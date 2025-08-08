<?php

namespace App\Http\Controllers;

use App\Models\Absence;
use App\Models\AbsenceType;
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
use App\Models\User;
use App\Models\UserLeaveDay;
use App\Models\UserWorkingHour;
use App\Models\UserWorkingWeek;
use App\Services\HolidayService;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonInterval;

class UserController extends Controller
{

    private function validateUser(Request $request, array $additionalRules = [])
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

            'home_office' => 'required|boolean',
            'home_office_hours_per_week' => 'nullable|min:0|numeric',

            'user_working_hours' => 'required|array',
            'user_working_hours.*.id' => 'nullable|exists:user_working_hours,id',
            'user_working_hours.*.weekly_working_hours' => 'required|min:0|decimal:0,2',
            'user_working_hours.*.active_since' => 'required|date',

            'user_working_weeks' => 'required|array',
            'user_working_weeks.*.id' => 'nullable|exists:user_working_weeks,id',
            'user_working_weeks.*.weekdays' => 'required|array',
            'user_working_weeks.*.weekdays.*' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'user_working_weeks.*.active_since' => 'required|date',

            'user_leave_days' => 'required|array',
            'user_leave_days.*.id' => 'nullable|exists:user_leave_days,id',
            'user_leave_days.*.leave_days' => 'required|integer|min:0',
            'user_leave_days.*.active_since' => 'required|date',

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
            'users' => User::inOrganization()->with('group:id,name')->get()->map(fn($u) => [
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

        $user->load(['groupUser', 'operatingSiteUser', 'organizationUser', 'supervisor:id']);

        $user['user_working_hours'] = $user->userWorkingHours()
            ->orderBy('active_since', 'asc')
            ->whereDate('active_since', '>', Carbon::now())
            ->get()
            ->merge([$user->currentWorkingHours]);

        $user['user_working_weeks'] = $user->userWorkingWeeks()
            ->orderBy('active_since', 'asc')
            ->whereDate('active_since', '>', Carbon::now())
            ->get()
            ->merge([$user->currentWorkingWeek]);

        $user['user_leave_days'] = collect(
            $user->userLeaveDays()
                ->where('type', 'annual')
                ->orderBy('active_since', 'asc')
                ->whereDate('active_since', '>', Carbon::now())
                ->get()
        )->merge([$user->currentLeaveDays]);

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
        ]);
    }

    public function absences(User $user)
    {
        Gate::authorize('viewIndex', [Absence::class, $user]);

        $user['leaveDaysForYear'] = $user->leaveDaysForYear(Carbon::now());
        $user['usedLeaveDaysForYear'] = $user->usedLeaveDaysForYear(Carbon::now());
        $user['absences'] = $user->absences()
            ->whereYear('start', '<=', Carbon::now()->year)
            ->whereYear('end', '>=', Carbon::now()->year)
            ->with(['absenceType:id,name', 'user:id,operating_site_id'])
            ->get(['id', 'start', 'end', 'absence_type_id', 'status', 'user_id'])->append('usedDays');

        return Inertia::render('User/UserShow/Absences', [
            'user' => $user,
            'can' => self::getUserShowCans($user),
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
                'email'
            ]),
            'supervisor' => $user->supervisor()->first(['id', 'first_name', 'last_name', 'email']),

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
            'city' => $validated['city'],
            'zip' => $validated['zip'],
            'street' => $validated['street'],
            'house_number' => $validated['house_number'],
            'address_suffix' => $validated['address_suffix'],
            'country' => $validated['country'],
            'federal_state' => $validated['federal_state'],
            'phone_number' => $validated['phone_number'],
            'staff_number' => $validated['staff_number'],
            'password' =>  Hash::make($validated['password']),
            'overtime_calculations_start' => $validated['overtime_calculations_start'],
            'group_id' => $validated['group_id'],
            'is_supervisor' => $validated['is_supervisor'],
            'supervisor_id' => $validated['supervisor_id'],
            'operating_site_id' => $validated['operating_site_id'],
            'date_of_birth' => Carbon::parse($validated['date_of_birth']),
            'home_office' => $validated['home_office'],
            'home_office_hours_per_week' => $validated['home_office'] ? $validated['home_office_hours_per_week'] ?? 0 : null,
            'email_verified_at' => now(),
        ]);
        $user->save();

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
            "email" => "required|email",
        ]);

        $user->update([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'date_of_birth' => $validated['date_of_birth'],
            'city' => $validated['city'],
            'zip' => $validated['zip'],
            'street' => $validated['street'],
            'house_number' => $validated['house_number'],
            'address_suffix' => $validated['address_suffix'],
            'country' => $validated['country'],
            'federal_state' => $validated['federal_state'],
            'phone_number' => $validated['phone_number'],
            'staff_number' => $validated['staff_number'],
            'group_id' => $validated['group_id'],
            'operating_site_id' => $validated['operating_site_id'],
            'supervisor_id' => $validated['supervisor_id'],
            'is_supervisor' => $validated['is_supervisor'],
            'date_of_birth' => Carbon::parse(Carbon::parse($validated['date_of_birth'])->format('d-m-Y')),
            'home_office' => $validated['home_office'],
            'home_office_hours_per_week' => $validated['home_office'] ? $validated['home_office_hours_per_week'] ?? 0 : null,
            "overtime_calculations_start" => $validated['overtime_calculations_start'],
        ]);
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

    public function timeStatements(Request $request, User $user)
    {
        Gate::authorize('viewIndex', [TimeAccountTransaction::class, $user]);

        return Inertia::render('User/UserShow/TimeStatements', [
            'user' => $user,
            'work_logs' => $user->workLogs()->whereBetween('start', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])->get(),
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

        $date = Carbon::now()->startOfMonth();

        $shifts = Shift::where('user_id', $user->id)
            ->with(['workLogs.currentAccountedPatch', 'user'])
            ->get()
            ->filter(fn($s) => Carbon::parse($s->start)->between($date->copy()->startOfMonth(), $date->copy()->endOfMonth()));

        $absences = $user->absences()
            ->whereBetween('start', [$date->copy()->startOfMonth(), $date->copy()->endOfMonth()])
            ->orWhereBetween('end', [$date->copy()->startOfMonth(), $date->copy()->endOfMonth()])
            ->with(['absenceType:id,name'])
            ->get();

        function formatDuration(string $seconds, string $fallback = ''): string
        {
            if ($seconds == 0) return $fallback;
            $hours = floor(abs($seconds) / 3600);
            $seconds %= 3600;
            $minutes =  floor(abs($seconds) / 60);
            $seconds %= 60;
            return ($seconds < 0 ? '-' : '') . str_pad($hours, 2, '0', STR_PAD_LEFT) . ':' . str_pad($minutes, 2, '0', STR_PAD_LEFT) . ':' . str_pad(abs($seconds), 2, '0', STR_PAD_LEFT);
        }

        $entryIndex = 0;
        $data =
            collect(range(1, $date->copy()->daysInMonth()))->reduce(
                function ($values, $i) use ($shifts, $absences, $user, $date, &$entryIndex) {
                    $day = $date->copy()->startOfMonth()->addDays($i - 1);
                    $shifts = $shifts->where(fn($s) => $day->between(Carbon::parse($s->start)->startOfDay(), Carbon::parse($s->end)->endOfDay()));
                    $absence = $absences->first(fn($a) => $day->between(Carbon::parse($a->start)->startOfDay(), Carbon::parse($a->end)->endOfDay()));

                    $values->{$day->format('d')} = [];

                    if ($shifts->isEmpty() && !$absence) {
                        $values->{$day->format('d')} = (object)[
                            'day' => $day->format('d'),
                            'type' => 'empty',
                            'should' => formatDuration($user->getSollsekundenForDate($day)),
                            'transaction_value' => '',
                            'entryIndex' => ++$entryIndex,
                            'data' => [],
                        ];
                        return $values;
                    }


                    if ($shifts->isEmpty() && $absence) {
                        $values->{$day->format('d')} = (object)[
                            'day' => $day->format('d'),
                            'type' => 'absence',
                            'absence_type' => $absence->absenceType?->name ?? 'Abwesend',
                            'entryIndex' => ++$entryIndex,
                            'data' => [],
                        ];
                        return $values;
                    }
                    $values->{$day->format('d')} = (object)[
                        'day' => $day->format('d'),
                        'type' => 'shift',
                        'absence_type' => $absence?->absenceType->name ?? null,
                        'data' => [],
                    ];
                    foreach ($shifts as $shift) {
                        $entries = $values->{$day->copy()->subDay()->format('d')}?->data;
                        $values->{$day->format('d')}->data[] = (object)[
                            'shift_id' => $shift->id,
                            'entryIndex' => (
                                (property_exists($values, $day->copy()->subDay()->format('d')) &&
                                    count($values->{$day->copy()->subDay()->format('d')}?->data) > 0 &&
                                    end($entries)?->shift_id == $shift->id
                                )
                                ? $entryIndex
                                : ++$entryIndex
                            ),
                            'logs' => $shift->workLogs->map(fn($wl) => $wl->currentAccountedPatch ?? $wl)->filter(fn($log) => $day->between(
                                Carbon::parse($log->start)->startOfDay(),
                                Carbon::parse($log->end)->endOfDay()
                            ))->map(
                                fn($wl) => [
                                    'home_office' => $wl->is_home_office ? 'Ja' : 'Nein',
                                    'start' => Carbon::parse(Carbon::parse($wl->start)->isSameDay($day) ? $wl->start : $day->copy()->startOfDay())->format('H:i:s'),
                                    'end' => Carbon::parse(Carbon::parse($wl->end)->isSameDay($day) ? $wl->end : $day->copy()->endOfDay())->format('H:i:s'),
                                    'duration' => formatDuration(
                                        Carbon::parse($wl->start)->isSameDay($day) && Carbon::parse($wl->end)->isSameDay($day)
                                            ? $wl->duration
                                            : ((Carbon::parse($wl->start)->lt($day->copy()->startOfDay())
                                                ? $day->copy()->startOfDay()
                                                : Carbon::parse($wl->start)
                                            )->diffInSeconds(
                                                Carbon::parse($wl->end)->gt($day->copy()->endOfDay())
                                                    ? $day->copy()->endOfDay()
                                                    : Carbon::parse($wl->end)
                                            ))
                                    ),
                                ]
                            ),
                            'should' => formatDuration($user->getSollsekundenForDate($day), '00:00:00'),
                            'is' => $day->isSameDay(Carbon::parse($shift->end))
                                ? ($absence ? formatDuration(max(
                                    $shift->workDuration - $shift->missingBreakDuration,
                                    $user->getSollsekundenForDate($day)
                                )) : formatDuration($shift->workDuration - $shift->missingBreakDuration, '00:00:00'))
                                : '',
                            'pause' => $day->isSameDay(Carbon::parse($shift->end)) ? formatDuration($shift->missingBreakDuration) . '/' . formatDuration($shift->breakDuration) : '',
                            'transaction_value' => '',
                        ];
                    }

                    return $values;
                },
                (object)[]
            );

        $props = [
            'data' => $data,
            'organization' => Organization::getCurrent(),
            'federal_state' => HolidayService::$COUNTRIES[$user->operatingSite->country]['regions'][$user->operatingSite->federal_state],
            'user' => $user->load('operatingSite'),
            'month' => $date
        ];

        return view('print.timeStatement', $props);
        // $pdf = PDF::loadView('print.timeStatement', $props)->setPaper('a4', 'landscape');
        // return $pdf->download('timeStatement.pdf');
    }

    private function getUserShowCans(User $user)
    {
        return [
            'absences' => [
                'viewIndex' =>  Gate::allows('viewIndex', [Absence::class, $user]),
            ],
            'timeAccount' => [
                'viewIndex' => Gate::allows('viewIndex', [TimeAccount::class, $user]),
                'create' => Gate::allows('create', [TimeAccount::class, $user]),
                'update' => Gate::allows('update', [TimeAccount::class, $user]),
                'delete' => Gate::allows('delete', [TimeAccount::class, $user]),
            ],
            'timeAccountTransaction' => [
                'viewIndex' => Gate::allows('viewIndex', [TimeAccountTransaction::class, $user]),
                'create' => Gate::allows('create', [TimeAccountTransaction::class, $user]),
            ],
            'user' => [
                'update' => Gate::allows('update', $user),
                'viewShow' => Gate::allows('viewShow', $user),
                'viewIndex' => Gate::allows('viewIndex', User::class),
            ]
        ];
    }
}
