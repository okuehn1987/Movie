<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\GroupUser;
use App\Models\OperatingSite;
use App\Models\OperatingSiteUser;
use App\Models\Organization;
use App\Models\OrganizationUser;
use App\Models\TimeAccount;
use App\Models\TimeAccountSetting;
use App\Models\TimeAccountTransaction;
use App\Models\User;
use App\Models\UserLeaveDay;
use App\Models\UserWorkingHour;
use App\Models\UserWorkingWeek;
use App\Services\HolidayService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

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
            'home_office_hours_per_week' => 'nullable|required_if:home_office,true|numeric',

            'userWorkingHours' => 'required|decimal:0,2',
            'userWorkingHoursSince' => 'required|date',

            'userWorkingWeek' => 'required|array',
            'userWorkingWeek.*' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'userWorkingWeekSince' => 'required|date',

            'userLeaveDays' => 'required|integer',
            'userLeaveDaysSince' => 'required|date',
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
            'users' => User::inOrganization()->with('group:id,name')->paginate(12)->through(fn($u) => [
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

    public function show(User $user)
    {
        Gate::authorize('viewShow', $user);

        $user->load(['groupUser', 'operatingSiteUser', 'organizationUser']);
        $user['currentWorkingHours'] = $user->userWorkingHours()->latest('active_since')->first();
        $user['futureWorkingHours'] = $user->userWorkingHours()->latest('active_since')->whereDate('active_since', '>', Carbon::now())->get();
        $user['currentUserWorkingWeek'] = $user->userWorkingWeeks()->latest('active_since')->first();
        $user['futureUserWorkingWeek'] = $user->userWorkingWeeks()->latest('active_since')->whereDate('active_since', '>', Carbon::now())->get();
        $user['leaveDaysForYear'] = $user->leaveDaysForYear(Carbon::now());
        $user['usedLeaveDaysForYear'] = $user->usedLeaveDaysForYear(Carbon::now());
        $user['userLeaveDays'] = $user->userLeaveDays()->latest('active_since')->get();
        $user['absences'] = $user->absences()
            ->whereYear('start', '<=', Carbon::now()->year)
            ->whereYear('end', '>=', Carbon::now()->year)
            ->with(['absenceType:id,name', 'user:id,operating_site_id'])
            ->get(['id', 'start', 'end', 'absence_type_id', 'status', 'user_id'])->append('usedDays');

        $timeAccounts =  $user->timeAccounts()
            ->withTrashed()
            ->with(['timeAccountSetting'])
            ->get(["id", "user_id", "balance", "balance_limit", "time_account_setting_id", "name", "deleted_at"]);

        $userTransactions = TimeAccountTransaction::forUser($user)->with('user:id,first_name,last_name')->latest()->paginate(15);

        return Inertia::render('User/UserShow', [
            'user' => $user,
            'supervisors' => User::inOrganization()
                ->where('is_supervisor', true)
                ->whereNotIn('id', $user->allSuperviseesFlat()->pluck('id'))
                ->get(['id', 'first_name', 'last_name']),
            'time_accounts' => $timeAccounts,
            'time_account_settings' => TimeAccountSetting::inOrganization()->get(['id', 'type', 'truncation_cycle_length_in_months']),
            'defaultTimeAccountId' => $user->defaultTimeAccount->id,
            'groups' => Group::inOrganization()->get(),
            'operating_sites' => OperatingSite::inOrganization()->get(),
            'time_account_transactions' => $userTransactions,
            'organigramUsers' => ($user->supervisor ?: $user)->allSupervisees()->get([
                'id',
                'first_name',
                'last_name',
                'supervisor_id',
                'email'
            ]),
            'supervisor' => $user->supervisor()->first(['id', 'first_name', 'last_name', 'email']),
            'countries' => HolidayService::getCountries(),
            'permissions' => collect(User::$PERMISSIONS)->flatten(1),
            'can' => [
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
                    'viewIndex' => Gate::allows('viewIndex', User::class),
                    'update' => Gate::allows('update', $user),
                ]
            ],
        ]);
    }

    public function store(Request $request)
    {
        Gate::authorize('create', User::class);

        $validated = self::validateUser($request, [
            "password" => "required|string",
            "email" => "required|email|unique:users",
            "remaining_leave_days" => "required|integer",
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
            'home_office_hours_per_week' => $validated['home_office_hours_per_week'],
            'email_verified_at' => now(),
        ]);
        $user->save();

        UserLeaveDay::create([
            'user_id' => $user->id,
            'leave_days' => $validated['userLeaveDays'],
            'active_since' => Carbon::parse($validated['userLeaveDaysSince']),
            'type' => 'annual'
        ]);

        if ($validated['remaining_leave_days'] > 0) {
            UserLeaveDay::create([
                'user_id' => $user->id,
                'leave_days' => $validated['remaining_leave_days'],
                'active_since' => Carbon::now()->startOfYear(),
                'type' => 'remaining'
            ]);
        }

        UserWorkingHour::create([
            'user_id' => $user->id,
            'weekly_working_hours' => $validated['userWorkingHours'],
            'active_since' => Carbon::parse($validated['userWorkingHoursSince'])
        ]);

        UserWorkingWeek::create([
            'user_id' => $user->id,
            ...collect($validated['userWorkingWeek'])->flatMap(fn($e) => [$e => true]),
            'active_since' => Carbon::parse($validated['userWorkingWeekSince'])
        ]);

        TimeAccount::create([
            'name' => 'Gleitzeitkonto',
            'balance' => 0,
            'balance_limit' => $validated['userWorkingHours'] * 2,
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
            'home_office_hours_per_week' => $validated['home_office_hours_per_week'],
        ]);
        $user->organizationUser->update($validated['organizationUser']);
        $user->operatingSiteUser->update($validated['operatingSiteUser']);
        $user->groupUser?->update($validated['groupUser']);

        $user->forceFill([
            "overtime_calculations_start" => $validated['overtime_calculations_start'],
        ]);
        $user->save();

        UserLeaveDay::updateOrCreate([
            'active_since' => Carbon::parse($validated['userLeaveDaysSince']),
            'user_id' => $user->id,
            'type' => 'annual'
        ], [
            'leave_days' => $validated['userLeaveDays'],
        ]);

        $lastWorkingHour = $user->userWorkingHours()
            ->where('active_since', Carbon::parse($validated['userWorkingHoursSince']))
            ->latest()
            ->firstOrNew();

        $lastWorkingHour->fill([
            'weekly_working_hours' => floatval($validated['userWorkingHours']),
            'active_since' => Carbon::parse($validated['userWorkingHoursSince'])->format("Y-m-d")
        ]);

        if ($lastWorkingHour->isDirty()) $lastWorkingHour->replicate()->save();

        $workingWeek = $user->userWorkingWeeks()
            ->where('active_since', Carbon::parse($validated['userWorkingWeekSince']))
            ->latest()
            ->firstOrNew();

        foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day) {
            $workingWeek[$day] = (int)in_array($day, $validated['userWorkingWeek']);
        }

        if ($workingWeek->isDirty()) $workingWeek->replicate()->save();

        return back()->with("success", "Mitarbeitenden erfolgreich aktualisiert.");
    }
}
