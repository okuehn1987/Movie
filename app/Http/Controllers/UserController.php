<?php

namespace App\Http\Controllers;

use App\Enums\Status;
use App\Models\Absence;
use App\Models\AbsenceType;
use App\Models\Address;
use App\Models\Group;
use App\Models\GroupUser;
use App\Models\OperatingSite;
use App\Models\OperatingSiteUser;
use App\Models\Organization;
use App\Models\OrganizationUser;
use App\Models\Shift;
use App\Models\TimeAccount;
use App\Models\TimeAccountSetting;
use App\Models\TimeAccountTransaction;
use App\Models\TimeAccountTransactionChange;
use App\Models\TravelLog;
use App\Models\TravelLogPatch;
use App\Models\User;
use App\Models\UserLeaveDay;
use App\Models\UserWorkingHour;
use App\Models\UserWorkingWeek;
use App\Services\AppModuleService;
use App\Services\HolidayService;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{

    private function validateUser(Request $request, array $additionalRules = [], string $mode = 'create', User | null $user = null)
    {
        return $request->validate([
            "first_name" => "required|string",
            "last_name" => "required|string",
            "date_of_birth" => "nullable|date",
            "city" => "nullable|string",
            "zip" => "nullable|string",
            "street" => "nullable|string",
            "house_number" => "nullable|string",
            "address_suffix" => "nullable|string",
            "country" => ["required", Rule::in(HolidayService::getCountryCodes())],
            "federal_state" => ["required", Rule::in(HolidayService::getRegionCodes($request["country"]))],
            "phone_number" => "nullable|string",
            "staff_number" => "nullable|integer",
            "job_role" => "nullable|string|max:50",
            "group_id" => [
                "nullable",
                Rule::exists('groups', 'id')->where('organization_id', Organization::getCurrent()->id)
            ],
            'operating_site_id' => [
                "required",
                Rule::exists('operating_sites', 'id')->where('organization_id', Organization::getCurrent()->id)
            ],

            'supervisor_id' => [
                'nullable',
                Rule::exists('users', 'id')
                    ->where('is_supervisor', true)
                    ->whereIn('operating_site_id', OperatingSite::inOrganization()->pluck('id'))
            ],

            'is_supervisor' => 'required|boolean',

            'use_time_balance_traffic_light' => 'required|boolean',
            'time_balance_yellow_threshold' => 'nullable|required_if:use_time_balance_traffic_light,true|integer',
            'time_balance_red_threshold' => 'nullable|required_if:use_time_balance_traffic_light,true|integer',

            'resignation_date' => ['nullable', 'date', function ($attribute, $value, $fail) use ($request, $mode, $user) {
                if ($mode == 'create') return;
                if ($user->resignation_date == $value) return;
                if ($user->resignation_date && $user->resignation_date <= now()->format('Y-m-d')) {
                    $fail('validation.immutable')->translate([
                        'attribute' => __('validation.attributes.resignation_date'),
                    ]);
                }
                if ($value <= now()->format('Y-m-d')) {
                    $fail('validation.after')->translate([
                        'attribute' => __('validation.attributes.resignation_date'),
                        'date' => Carbon::now()->format('d.m.Y')
                    ]);
                }
            }],

            'home_office' => 'required|boolean',
            'home_office_hours_per_week' => 'nullable|min:0|numeric',

            'user_working_hours' => 'present|array',
            'user_working_hours.*.id' => 'nullable|exists:user_working_hours,id',
            'user_working_hours.*.weekly_working_hours' => 'required|min:0|decimal:0,2',
            'user_working_hours.*.active_since' => ['required', 'date', function ($attribute, $value, $fail) use ($request, $mode) {
                $index  = explode('.', $attribute)[1];
                $currentWorkingHour = $request['user_working_hours'][$index];
                if ($mode == 'update' && !isset($currentWorkingHour['id']) && Carbon::parse($currentWorkingHour['active_since'])->lt(Carbon::now()->endOfDay())) {
                    $fail('validation.after')->translate([
                        'attribute' => __('validation.attributes.active_since'),
                        'date' => Carbon::now()->format('d.m.Y')
                    ]);
                }
            }],

            'user_working_weeks' => 'present|array',
            'user_working_weeks.*.id' => 'nullable|exists:user_working_weeks,id',
            'user_working_weeks.*.weekdays' => 'required|array',
            'user_working_weeks.*.weekdays.*' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'user_working_weeks.*.active_since' =>  ['required', 'date', function ($attribute, $value, $fail) use ($request, $mode) {
                $index  = explode('.', $attribute)[1];
                $currentWorkingWeek = $request['user_working_weeks'][$index];
                if ($mode == 'update' && !isset($currentWorkingWeek['id']) && Carbon::parse($currentWorkingWeek['active_since'])->lt(Carbon::now()->endOfDay())) {
                    $fail('validation.after')->translate([
                        'attribute' => __('validation.attributes.active_since'),
                        'date' => Carbon::now()->format('d.m.Y')
                    ]);
                }
            }],

            'user_leave_days' => 'present|array',
            'user_leave_days.*.id' => 'nullable|exists:user_leave_days,id',
            'user_leave_days.*.leave_days' => 'required|integer|min:0',
            'user_leave_days.*.active_since' => ['required', 'date', function ($attribute, $value, $fail) use ($request, $mode) {
                $index  = explode('.', $attribute)[1];
                $currentLeaveDays = $request['user_leave_days'][$index];
                if ($mode == 'update' && !isset($currentLeaveDays['id']) && Carbon::parse($currentLeaveDays['active_since'])->lt(Carbon::now()->startOfYear())) {
                    $fail('validation.after_or_equal')->translate([
                        'attribute' => __('validation.attributes.active_since'),
                        'date' => Carbon::now()->startOfYear()->format('m.Y')
                    ]);
                }
            }],

            "overtime_calculations_start" => "required|date",

            'organizationUser' => 'required|array',
            'organizationUser.*' => ['nullable', function ($attribute, $value, $fail) {
                if (!in_array(explode('.', $attribute)[1], collect(User::$PERMISSIONS)->flatten(1)->map(fn($p) => $p['name'])->toArray())) {
                    $fail('The ' . $attribute . ' is invalid.');
                }
                if (!in_array($value, ['read', 'write', null])) {
                    $fail('The ' . $attribute . ' is invalid.');
                }
            }],
            'operatingSiteUser' => 'required|array',
            'operatingSiteUser.*' => ['nullable', function ($attribute, $value, $fail) {
                if (!in_array(explode('.', $attribute)[1], collect(User::$PERMISSIONS)->only(['all', 'operatingSite'])->flatten(1)->map(fn($p) => $p['name'])->toArray())) {
                    $fail('The ' . $attribute . ' is invalid.');
                }
                if (!in_array($value, ['read', 'write', null])) {
                    $fail('The ' . $attribute . ' is invalid.');
                }
            }],
            'groupUser' => 'nullable|array',
            'groupUser.*' => ['nullable', function ($attribute, $value, $fail) {
                if (!in_array(explode('.', $attribute)[1], collect(User::$PERMISSIONS)->only(['all', 'group'])->flatten(1)->map(fn($p) => $p['name'])->toArray())) {
                    $fail('The ' . $attribute . ' is invalid.');
                }
                if (!in_array($value, ['read', 'write', null])) {
                    $fail('The ' . $attribute . ' is invalid.');
                }
            }],
            ...$additionalRules
        ]);
    }

    public function index()
    {
        Gate::authorize('viewIndex', User::class);

        return Inertia::render('User/UserIndex', [
            'users' => User::inOrganization()->with(['group:id,name', 'currentAddress'])->get([
                'id',
                'first_name',
                'last_name',
                'date_of_birth',
                'email',
                'staff_number',
                'job_role',
                'group_id'
            ])->map(fn($u) => [
                ...$u->toArray(),
                'can' => [
                    'user' => [
                        'viewShow' => Gate::allows('viewShow', $u),
                        'delete' => Gate::allows('delete', $u),
                    ],

                ]
            ]),
            'supervisors' => User::inOrganization()->where('is_supervisor', true)->get(['id', 'first_name', 'last_name']),
            'permissions' => collect(User::$PERMISSIONS)->flatten(1),
            'groups' => Group::inOrganization()->get(['id', 'name']),
            'operating_sites' => OperatingSite::inOrganization()->get(['id', 'name']),
            'countries' => HolidayService::getCountries(),
            'can' => [
                'user' => [
                    'create' => Gate::allows('create', User::class),
                ]
            ]
        ]);
    }

    public function generalInformation(User $user)
    {
        Gate::authorize('viewShow', $user);

        $user->load([
            'groupUser',
            'operatingSiteUser',
            'organizationUser',
            'supervisor:id',
            'currentAddress',
            'userWorkingWeeks' => function ($q) {
                $q->orderBy('active_since', 'desc');
            },
            'userLeaveDays' => function ($q) {
                $q->where('type', 'annual')->orderBy('active_since', 'desc');
            },
            ...(AppModuleService::hasAppModule('herta') ?
                [
                    'userWorkingHours' => function ($q) {
                        $q->orderBy('active_since', 'desc');
                    }
                ] :
                []
            ),
        ]);

        return Inertia::render('User/UserShow/GeneralInformation', [
            'user' => $user,

            'operating_sites' => OperatingSite::inOrganization()->get(),
            'groups' => Group::inOrganization()->get(),

            'possibleSupervisors' => User::inOrganization()
                ->where('is_supervisor', true)
                ->whereNotIn('id', $user->allSuperviseesFlat()->pluck('id'))
                ->get(['id', 'first_name', 'last_name']),
            'countries' => HolidayService::getCountries(),
            'permissions' => collect(User::$PERMISSIONS)->flatten(1),

            'can' => self::getUserShowCans($user),
        ]);
    }

    public function profile(Request $request)
    {
        Gate::authorize('publicAuth', User::class);

        $user = $request->user();
        return Inertia::render('User/UserShow/Profile', [
            'user' => $user,
            'mustVerifyEmail' => $user instanceof MustVerifyEmail,
            'status' => session('status'),
            'can' => self::getUserShowCans($user),
            'users' => User::inOrganization()->get(['id', 'first_name', 'last_name', 'job_role']),
        ]);
    }

    public function absences(User $user)
    {
        Gate::authorize('viewIndex', [Absence::class, $user]);

        $user['leaveDaysForYear'] = $user->leaveDaysForYear(Carbon::now());
        $user['usedLeaveDaysForYear'] = $user->usedLeaveDaysForYear(Carbon::now());
        $user['absences'] = $user->absences()
            ->whereYear('start', '<=', Carbon::now()->year)
            ->whereYear('end', '>=', Carbon::now()->year - 3)
            ->with(['absenceType:id,name', 'user:id,operating_site_id'])
            ->where('status', Status::Accepted)
            ->get(['id', 'start', 'end', 'absence_type_id', 'status', 'user_id'])->append('usedDays');

        return Inertia::render('User/UserShow/Absences', [
            'user' => $user,
            'can' => self::getUserShowCans($user),
            'absenceTypes' => AbsenceType::inOrganization()->get(['id', 'name'])
        ]);
    }

    public function timeAccounts(User $user)
    {
        Gate::authorize('viewIndex', [TimeAccount::class, $user]);

        $timeAccounts =  $user->timeAccounts()
            ->withTrashed()
            ->with(['timeAccountSetting'])
            ->get(["id", "user_id", "balance", "balance_limit", "time_account_setting_id", "name", "deleted_at"]);

        return Inertia::render('User/UserShow/TimeAccounts/TimeAccounts', [
            'user' => $user->load(['currentWorkingHours', 'currentWorkingWeek']),
            'time_accounts' => $timeAccounts,
            'time_account_settings' => TimeAccountSetting::inOrganization()->get(['id', 'type', 'truncation_cycle_length_in_months']),
            'defaultTimeAccountId' => $user->defaultTimeAccount->id,

            'can' => self::getUserShowCans($user),
        ]);
    }

    public function timeAccountTransactions(User $user)
    {
        Gate::authorize('viewIndex', [TimeAccountTransaction::class, $user]);

        $timeAccounts =  $user->timeAccounts()
            ->withTrashed()
            ->with(['timeAccountSetting'])
            ->get(["id", "user_id", "balance", "balance_limit", "time_account_setting_id", "name", "deleted_at"]);

        $userTransactions = TimeAccountTransaction::forUser($user)->with('user:id,first_name,last_name')->orderByDesc('id')->paginate(13);

        return Inertia::render('User/UserShow/TimeAccountTransactions', [
            'user' => $user,
            'time_accounts' => $timeAccounts,
            'time_account_transactions' => $userTransactions,

            'can' => self::getUserShowCans($user),
        ]);
    }

    public function userOrganigram(User $user)
    {
        Gate::authorize('viewIndex', User::class);

        return Inertia::render('User/UserShow/Organigramm', [
            'user' => $user,
            'organigramUsers' => ($user->supervisor ?: $user)->allSupervisees()->get([
                'id',
                'first_name',
                'last_name',
                'supervisor_id',
                'email',
                'job_role',
            ]),
            'supervisor' => $user->supervisor()->first(['id', 'first_name', 'last_name', 'email', 'job_role']),

            'can' => self::getUserShowCans($user),
        ]);
    }

    public function store(Request $request)
    {
        Gate::authorize('create', User::class);

        $validated = self::validateUser($request, [
            "password" => "required|string",
            "email" => "required|email|unique:users",
            "initialRemainingLeaveDays" => "required|integer",
        ]);

        $user = (new User)->forceFill([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'date_of_birth' => $validated['date_of_birth'],
            'phone_number' => $validated['phone_number'],
            'staff_number' => $validated['staff_number'],
            'password' =>  Hash::make($validated['password']),
            'overtime_calculations_start' => $validated['overtime_calculations_start'],
            'group_id' => $validated['group_id'],
            'is_supervisor' => $validated['is_supervisor'],
            'supervisor_id' => $validated['supervisor_id'],
            'operating_site_id' => $validated['operating_site_id'],
            'resignation_date' => $validated['resignation_date'],
            'date_of_birth' => Carbon::parse($validated['date_of_birth']),
            'home_office' => $validated['home_office'],
            'home_office_hours_per_week' => $validated['home_office'] ? $validated['home_office_hours_per_week'] ?? 0 : null,
            'job_role' => $validated['job_role'],
            'time_balance_yellow_threshold' => $validated['use_time_balance_traffic_light'] ? $validated['time_balance_yellow_threshold'] : null,
            'time_balance_red_threshold' => $validated['use_time_balance_traffic_light'] ? $validated['time_balance_red_threshold'] : null,
            'email_verified_at' => now(),
            'notification_channels' => ['database', 'mail'],
        ]);
        $user->save();

        $user->addresses()->create(collect($validated)->only(Address::$ADDRESS_KEYS)->toArray());

        UserLeaveDay::create([
            'user_id' => $user->id,
            'leave_days' => $validated['initialRemainingLeaveDays'],
            'active_since' => Carbon::now()->startOfYear(),
            'type' => 'remaining'
        ]);

        foreach ($validated['user_leave_days'] as $leaveDay) {
            UserLeaveDay::create([
                'user_id' => $user->id,
                'leave_days' => $leaveDay['leave_days'],
                'active_since' => Carbon::parse($leaveDay['active_since']),
                'type' => 'annual'
            ]);
        }
        foreach ($validated['user_working_hours'] as $workingHour) {
            UserWorkingHour::create([
                'user_id' => $user->id,
                'weekly_working_hours' => $workingHour['weekly_working_hours'],
                'active_since' => Carbon::parse($workingHour['active_since'])
            ]);
        }
        foreach ($validated['user_working_weeks'] as $workingWeek) {
            UserWorkingWeek::create([
                'user_id' => $user->id,
                ...collect($workingWeek['weekdays'])->flatMap(fn($e) => [$e => true]),
                'active_since' => Carbon::parse($workingWeek['active_since'])
            ]);
        }

        $currentWorkingHours = UserWorkingHour::where('user_id', $user->id)
            ->latest('active_since')
            ->where('active_since', '<', Carbon::now()->startOfDay())
            ->first();

        TimeAccount::create([
            'name' => 'Gleitzeitkonto',
            'balance' => 0,
            'balance_limit' => ($currentWorkingHours?->weekly_working_hours ?? 40) * 2 * 3600,
            'time_account_setting_id' => TimeAccountSetting::inOrganization()->whereNull('type')->first()->id,
            'user_id' => $user->id
        ]);

        OrganizationUser::create([
            "user_id" => $user->id,
            "organization_id" => Organization::getCurrent()->id,
            ...$validated['organizationUser']
        ]);

        if (!is_null($user->group_id)) {
            GroupUser::create([
                "user_id" => $user->id,
                "group_id" => $user->group_id,
                ...$validated['groupUser']
            ]);
        }

        OperatingSiteUser::create([
            "user_id" => $user->id,
            "operating_site_id" => $user->operating_site_id,
            ...$validated['operatingSiteUser']
        ]);

        return back()->with('success', 'Mitarbeitenden erfolgreich angelegt.');
    }

    public function destroy(User $user)
    {
        Gate::authorize('delete', $user);

        if ($user->is_supervisor) {
            return back()->with('error', 'Mitarbeitende mit unterstellten Mitarbeitenden können nicht gelöscht werden.');
        }
        if ($user->organization->owner_id === $user->id) {
            return back()->with('error', 'Organisationsinhaber kann nicht gelöscht werden.');
        }
        $user->delete();

        return back()->with('success', 'Mitarbeitenden erfolgreich gelöscht.');
    }

    public function update(Request $request, User $user)
    {
        Gate::authorize('update', $user);

        $validated = self::validateUser($request, [
            "email" => ['required', 'email', Rule::unique('users')->ignore($user->id)],
        ], 'update', $user);

        $user->update([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'date_of_birth' => $validated['date_of_birth'],
            'phone_number' => $validated['phone_number'],
            'staff_number' => $validated['staff_number'],
            'group_id' => $validated['group_id'],
            'operating_site_id' => $validated['operating_site_id'],
            'supervisor_id' => $validated['supervisor_id'],
            'is_supervisor' => $validated['is_supervisor'],
            'resignation_date' => $validated['resignation_date'],
            'date_of_birth' => Carbon::parse(Carbon::parse($validated['date_of_birth'])->format('d-m-Y')),
            'home_office' => $validated['home_office'],
            'home_office_hours_per_week' => $validated['home_office'] ? $validated['home_office_hours_per_week'] ?? 0 : null,
            "overtime_calculations_start" => $validated['overtime_calculations_start'],
            'job_role' => $validated['job_role'],
            'time_balance_yellow_threshold' => $validated['use_time_balance_traffic_light'] ? $validated['time_balance_yellow_threshold']  : null,
            'time_balance_red_threshold' => $validated['use_time_balance_traffic_light'] ? $validated['time_balance_red_threshold'] : null,
        ]);

        // FIXME: wir brauchen active since und sync wie CustomerOperatingSiteController@update
        $user->addresses()->create(collect($validated)->only(Address::$ADDRESS_KEYS)->toArray());

        $user->organizationUser->update($validated['organizationUser']);
        $user->operatingSiteUser->update($validated['operatingSiteUser']);
        $user->groupUser?->update($validated['groupUser']);

        $user->userLeaveDays()
            ->where('type', 'annual')
            ->whereDate('active_since', '>', Carbon::now())
            ->whereNotIn('id', collect($validated['user_leave_days'])->map(fn($e) => $e['id'])->toArray())
            ->delete();

        foreach ($validated['user_leave_days'] as $leaveDay) {
            if (Carbon::now()->startOfYear()->gt(Carbon::parse($leaveDay['active_since'])))
                continue;

            UserLeaveDay::updateOrCreate([
                'id' => $leaveDay['id'],
            ], [
                'user_id' => $user->id,
                'leave_days' => $leaveDay['leave_days'],
                'active_since' => Carbon::parse($leaveDay['active_since']),
                'type' => 'annual'
            ]);
        }

        $user->userWorkingWeeks()
            ->whereDate('active_since', '>', Carbon::now())
            ->whereNotIn('id', collect($validated['user_working_weeks'])->map(fn($e) => $e['id'])->toArray())
            ->delete();

        foreach ($validated['user_working_weeks'] as $workingWeek) {
            if (Carbon::now()->startOfDay()->gt(Carbon::parse($workingWeek['active_since'])))
                continue;

            UserWorkingWeek::updateOrCreate(
                [
                    'id' => $workingWeek['id'],
                ],
                [
                    'user_id' => $user->id,
                    'active_since' => Carbon::parse($workingWeek['active_since']),
                    ...collect(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'])
                        ->flatMap(
                            fn($e) =>
                            [$e => (int)in_array($e, $workingWeek['weekdays'])]
                        )->toArray()
                ]
            );
        }

        $user->userWorkingHours()
            ->whereDate('active_since', '>', Carbon::now())
            ->whereNotIn('id', collect($validated['user_working_hours'])->map(fn($e) => $e['id'])->toArray())
            ->delete();

        foreach ($validated['user_working_hours'] as $workingHour) {
            if (Carbon::now()->startOfDay()->gt(Carbon::parse($workingHour['active_since'])))
                continue;

            UserWorkingHour::updateOrCreate([
                'id' => $workingHour['id'],
            ], [
                'active_since' => Carbon::parse($workingHour['active_since']),
                'user_id' => $user->id,
                'weekly_working_hours' => $workingHour['weekly_working_hours'],
            ]);
        }

        return back()->with("success", "Mitarbeitenden erfolgreich aktualisiert.");
    }

    public function documents(Request $request, User $user)
    {
        Gate::authorize('viewIndex', [TimeAccountTransaction::class, $user]);

        return Inertia::render('User/UserShow/Documents', [
            'user' => $user,
            'can' => self::getUserShowCans($user),
        ]);
    }

    public function timeStatementDoc(Request $request, User $user)
    {
        Gate::authorize('viewIndex', [TimeAccountTransaction::class, $user]);
        Gate::authorize('viewIndex', [TimeAccount::class, $user]);
        Gate::authorize('viewIndex', [Absence::class, $user]);
        Gate::authorize('viewShow', [AbsenceType::class, $user]);
        Gate::authorize('viewShow', [User::class, $user]);

        $validated = $request->validate([
            'start' => 'required|date_format:Y-m|after_or_equal:' . Carbon::parse($user->created_at)->format('Y-m') . '|before_or_equal:end',
            'end' => 'required|date_format:Y-m|after_or_equal:start|before_or_equal:' . Carbon::now()->format('Y-m'),
        ]);

        $start = Carbon::parse($validated['start'])->startOfMonth();
        $end = Carbon::parse($validated['end'])->endOfMonth();

        $lastTransactionBeforeStatement = $user->defaultTimeAccount->allTimeAccountTransactions()
            ->orderByDesc(TimeAccountTransactionChange::select('date')
                ->whereDate('date', '<', $start->format('Y-m-d'))
                ->whereColumn('time_account_transaction_id', 'time_account_transactions.id')
                ->orderByDesc('date')
                ->limit(1))
            ->first();

        if ($lastTransactionBeforeStatement->changes()->whereDate('date', '>=', $start->format('Y-m-d'))->count() != 0) {
            $lastTransactionBeforeStatement = null;
        }

        if ($lastTransactionBeforeStatement)
            $previousBalance = $lastTransactionBeforeStatement->from_id == $user->defaultTimeAccount->id ?
                $lastTransactionBeforeStatement->from_previous_balance :
                $lastTransactionBeforeStatement->to_previous_balance;
        else $previousBalance = 0;


        $allTransactionChanges = TimeAccountTransactionChange::whereIn(
            'time_account_transaction_id',
            TimeAccountTransaction::forUser($user)->select('id')
        )->select('date', DB::raw('sum(amount) as amount'))->groupBy('date')->whereBetween('date', [$start, $end])->get();

        $shifts = Shift::where('user_id', $user->id)
            ->with('user')
            ->get()
            ->filter(fn($s) => Carbon::parse($s->start)->between($start, $end))
            ->each
            ->append('entries');

        $absences = $user->absences()
            ->with(['patches', 'currentAcceptedPatch.absenceType:id,name', 'absenceType:id,name'])
            ->where('status', Status::Accepted)
            ->get();

        $absences = $absences->map(fn($a) => $a->currentAcceptedPatch ?? $a)
            ->filter(fn($a) => $start->lte(Carbon::parse($a->end)) && $end->gte(Carbon::parse($a->start)));

        function countEntriesOfChunk($chunk)
        {
            $count = 0;
            foreach ((array)$chunk as $item) {
                if (count($item->data) == 0) $count++;
                foreach ($item->data as $d) {
                    $count += count($d->entries);
                }
            }
            return $count;
        }

        $monthData = [];
        $CHUNK_SIZE = 30;

        for ($date = $start->copy(); $date->lte($end); $date->addMonth()) {
            $entryIndex = 0;
            $previousMonth = end($monthData);

            if ($previousMonth) {
                $previousBalance += $previousMonth->data->reduce(fn($carry, $item) => $carry + $item->transaction_value, 0);
            }

            $monthData[] = (object)[
                'month' => $date->copy(),
                'previous_balance' => $previousBalance,
                'leave_days_used' => $user->usedLeaveDaysForMonth($date),
                'leave_days' => $user->leaveDaysForYear($date),
                'leave_days_used_before' =>  collect(
                    range(0, $start->copy()->startOfYear()->diffInMonths($date))
                )->map(
                    fn($i) => $start->copy()->startOfYear()->addMonths($i)
                )->filter(fn($m) => !$m->isSameMonth($date))->unique()->sort()->reduce(fn($carry, $month) => $carry + $user->usedLeaveDaysForMonth($month), 0),
                'is' => null,
                'missing_break' => null,
                'transaction_value' => null,
                'should' => null,
                'data' => collect(collect(range(1, $date->copy()->daysInMonth()))->reduce(
                    function ($values, $i) use ($shifts, $absences, $user, $date, &$entryIndex, $allTransactionChanges, $CHUNK_SIZE) {
                        $otherChunks =  collect($values)->slice(0, -1)->toArray();

                        $currentChunk = $values[count($values) - 1] ?? (object)[];
                        $currentValue = (object)[];

                        $day = $date->copy()->addDays($i - 1);
                        $holiday = HolidayService::getHolidayName(
                            $user->operatingSite->currentAddress->country,
                            $user->operatingSite->currentAddress->federal_state,
                            $day
                        );

                        $currentValueEntryCount = countEntriesOfChunk($currentChunk);

                        if ($currentValueEntryCount >= $CHUNK_SIZE) {
                            $entryIndex = 0;
                            $otherChunks[] = $currentChunk;
                            $currentChunk = (object)[];
                        }

                        $transactionValue = $allTransactionChanges->firstWhere('date', $day->format('Y-m-d'))?->amount ?? 0;

                        $shifts = $shifts->where(fn($s) => $day->between(
                            Carbon::parse($s->start)->startOfDay(),
                            Carbon::parse($s->end)->endOfDay()
                        ))->filter(fn($s) => $s->entries->isNotEmpty());

                        $absence = $absences->first(fn($a) => $day->between(Carbon::parse($a->start)->startOfDay(), Carbon::parse($a->end)->endOfDay()));

                        $currentValue->{$day->format('d')} = [];
                        $sollZeit = $user->getSollsekundenForDate($day);

                        if ($shifts->isEmpty()) {
                            if ($absence && $user->shouldWork($day)) {
                                $currentValue->{$day->format('d')} = (object)[
                                    'day' => $day->format('d'),
                                    'type' => 'absence',
                                    'holiday' => $holiday,
                                    'should_text' => '',
                                    'should' => 0,
                                    'absence_type' => $absence->absenceType?->name ?? 'Abwesend',
                                    'transaction_value_text' => self::formatDuration($transactionValue),
                                    'transaction_value' => $transactionValue,
                                    'entryIndex' => ++$entryIndex,
                                    'data' => [],
                                ];


                                return  [...$otherChunks, (object)[...(array)$currentChunk, ...(array)$currentValue]];
                            } else {
                                $currentValue->{$day->format('d')} = (object)[
                                    'day' => $day->format('d'),
                                    'type' => 'empty',
                                    'holiday' => $holiday,
                                    'should_text' => self::formatDuration($sollZeit),
                                    'should' => $sollZeit,
                                    'transaction_value_text' => self::formatDuration($transactionValue),
                                    'transaction_value' => $transactionValue,
                                    'entryIndex' => ++$entryIndex,
                                    'data' => [],
                                ];
                                return [...$otherChunks, (object)[...(array)$currentChunk, ...(array)$currentValue]];
                            }
                        }

                        $currentValue->{$day->format('d')} = (object)[
                            'day' => $day->format('d'),
                            'type' => 'shift',
                            'holiday' => $holiday,
                            'should_text' => self::formatDuration($sollZeit),
                            'should' => $sollZeit,
                            'absence_type' => $absence?->absenceType->name ?? null,
                            'data' => [],

                        ];

                        foreach ($shifts as $shift) {
                            $entries = $currentValue->{$day->copy()->format('d')}?->data;
                            $missingBreak = $shift->missingBreakDuration();
                            $is = $day->isSameDay(Carbon::parse($shift->end))
                                ? ($absence ? max(
                                    Shift::workDuration($shift->entries) - $missingBreak,
                                    $sollZeit
                                ) : $shift->workDuration($shift->entries) - $missingBreak)
                                : 0;

                            $relevantShiftEntries = $shift->entries->filter(fn($entry) => $day->between(
                                Carbon::parse($entry->start)->startOfDay(),
                                Carbon::parse($entry->end)->endOfDay()
                            ));

                            $currentValue->{$day->format('d')}->data[] = (object)[
                                'shift_id' => $shift->id,
                                'workDuration' => $shift->currentWorkDuration,
                                'homeofficeDuration' => $shift->homeofficeDuration,
                                'travellogDuration' => $shift->travellogDuration,
                                'entryIndex' => (
                                    (property_exists($currentValue, $day->copy()->format('d')) &&
                                        count($currentValue->{$day->copy()->format('d')}?->data) > 0 &&
                                        end($entries)?->shift_id == $shift->id
                                    )
                                    ? $entryIndex
                                    : ++$entryIndex
                                ),
                                'entries' => $relevantShiftEntries->map(
                                    fn($entry) => [
                                        'type' => match (true) {
                                            $entry instanceof TravelLog, $entry instanceof TravelLogPatch => 'Dienstreise',
                                            $entry->is_home_office => 'Homeoffice',
                                            default => 'Betrieb',
                                        },
                                        'start' => Carbon::parse(Carbon::parse($entry->start)->isSameDay($day) ? $entry->start : $day->copy()->startOfDay())->format('H:i:s'),
                                        'end' => Carbon::parse(Carbon::parse($entry->end)->isSameDay($day) ? $entry->end : $day->copy()->endOfDay())->format('H:i:s'),
                                        'duration' => self::formatDuration(
                                            Carbon::parse($entry->start)->isSameDay($day) && Carbon::parse($entry->end)->isSameDay($day)
                                                ? $entry->duration
                                                : ((Carbon::parse($entry->start)->lt($day->copy()->startOfDay())
                                                    ? $day->copy()->startOfDay()
                                                    : Carbon::parse($entry->start)
                                                )->diffInSeconds(
                                                    Carbon::parse($entry->end)->gt($day->copy()->endOfDay())
                                                        ? $day->copy()->endOfDay()
                                                        : Carbon::parse($entry->end)
                                                ))
                                        ),
                                    ]
                                ),
                                'missing_break_text' => $day->isSameDay(Carbon::parse($shift->end)) ? self::formatDuration($missingBreak) : '',
                                'missing_break' => $day->isSameDay(Carbon::parse($shift->end)) ? $missingBreak : 0,
                                'is_text' => $day->isSameDay(Carbon::parse($shift->end))
                                    ? ($absence ? self::formatDuration($is) : self::formatDuration($is, '00:00:00'))
                                    : '',
                                'is' =>  $is,
                                'transaction_value_text' => self::formatDuration($transactionValue),
                                'transaction_value' => $transactionValue,
                            ];
                        }

                        if (countEntriesOfChunk((object)[...(array)$currentChunk, ...(array)$currentValue]) >= $CHUNK_SIZE) {
                            return [...$otherChunks, $currentChunk, $currentValue];
                        }

                        return  [...$otherChunks, (object)[...(array)$currentChunk, ...(array)$currentValue]];
                    },
                    []
                ))->map(
                    function ($data) {
                        $dataKeys = (object)[];
                        $dataKeys->entries = $data;
                        $dataKeys->is = collect(array_values((array)$data))->flatMap(fn($d) => $d->type == 'empty' ? [] : $d->data)->reduce(fn($carry, $item) => $carry + $item->is, 0);
                        $dataKeys->missing_break = collect(array_values((array)$data))->flatMap(fn($d) => $d->type == 'empty' ? [] : $d->data)->reduce(fn($carry, $item) => $carry + $item->missing_break, 0);
                        $dataKeys->transaction_value = collect(array_values((array)$data))->flatMap(fn($d) => $d->type != 'shift' ? [$d] : $d->data)->reduce(fn($carry, $item) => $carry + $item->transaction_value, 0);
                        $dataKeys->should = collect(array_values((array)$data))->map(fn($e) => $e->should)->reduce(fn($carry, $item) => $carry + $item, 0);

                        return $dataKeys;
                    }
                )->values(),
            ];
        }

        foreach ($monthData as $month) {
            $allShiftEntriesOfMonth = $month->data->flatMap(fn($d) => collect(array_values((array)$d->entries))->flatMap(fn($e) => collect($e->data)));
            $month->is = $month->data->sum(fn($item) => $item->is);
            $month->missing_break = $month->data->sum(fn($item) => $item->missing_break);
            $month->transaction_value = $month->data->sum(fn($item) => $item->transaction_value);
            $month->should = $month->data->sum(fn($item) => $item->should);
            $month->workDuration = $allShiftEntriesOfMonth->sum(fn($item) => $item->workDuration);
            $month->homeofficeDuration = $allShiftEntriesOfMonth->sum(fn($item) => $item->homeofficeDuration);
            $month->travellogDuration = $allShiftEntriesOfMonth->sum(fn($item) => $item->travellogDuration);
        }

        $operatingSiteAddress = $user->operatingSite->currentAddress;
        $props = [
            'organization' => Organization::getCurrent(),
            'federal_state' => HolidayService::$COUNTRIES[$operatingSiteAddress->country]['regions'][$operatingSiteAddress->federal_state],
            'user' => $user->load('operatingSite'),
            'monthData' => $monthData,
        ];

        $fileNameDate = $validated['start'] == $validated['end'] ? $validated['start'] : $validated['start'] . '-' . $validated['end'];
        $pdf = PDF::loadView('print.timeStatement', $props)->setPaper('a4', 'landscape');
        return $pdf->stream($user->last_name . '_' . $user->first_name . '_' . $fileNameDate . '_Zeitnachweis.pdf');
    }

    public function updateSubstitutes(Request $request, #[CurrentUser] User $authUser)
    {
        $validated = $request->validate([
            'substitute_ids' => 'present|array',
            'substitute_ids.*' => ['required', Rule::exists('users', 'id')->whereIn('id', User::inOrganization()->select('id'))],
        ]);

        $authUser->isSubstitutedBy()->sync($validated['substitute_ids']);

        return back()->with('success', 'Vertretungen erfolgreich aktualisiert.');
    }

    private function getUserShowCans(User $user)
    {
        $canHerta = AppModuleService::hasAppModule('herta');

        return [
            'absences' => [
                'viewIndex' => Gate::allows('viewIndex', [Absence::class, $user]),
            ],
            'user' => [
                'update' => Gate::allows('update', $user),
                'viewShow' => Gate::allows('viewShow', $user),
                'viewIndex' => Gate::allows('viewIndex', User::class),
            ],
            'timeAccount' => [
                'viewIndex' => $canHerta && Gate::allows('viewIndex', [TimeAccount::class, $user]),
                'create' => $canHerta && Gate::allows('create', [TimeAccount::class, $user]),
                'update' => $canHerta && Gate::allows('update', [TimeAccount::class, $user]),
                'delete' => $canHerta && Gate::allows('delete', [TimeAccount::class, $user]),
            ],
            'timeAccountTransaction' => [
                'viewIndex' => $canHerta &&  Gate::allows('viewIndex', [TimeAccountTransaction::class, $user]),
                'create' => $canHerta && Gate::allows('create', [TimeAccountTransaction::class, $user]),
            ]
        ];
    }

    public static function formatDuration(string $seconds, string $fallback = ''): string
    {
        if ($seconds == 0) return $fallback;
        $hours = floor(abs($seconds) / 3600);
        $seconds %= 3600;
        $minutes =  floor(abs($seconds) / 60);
        $seconds %= 60;
        return ($seconds < 0 ? '-' : '') . str_pad($hours, 2, '0', STR_PAD_LEFT) . ':' . str_pad($minutes, 2, '0', STR_PAD_LEFT) . ':' . str_pad(abs($seconds), 2, '0', STR_PAD_LEFT);
    }
}
