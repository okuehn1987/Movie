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
use App\Models\UserWorkingHour;
use App\Models\UserWorkingWeek;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class UserController extends Controller
{
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

        $user['currentWorkingHours'] = $user->userWorkingHours()->latest()->first();
        $user['userWorkingWeek'] = $user->userWorkingWeeks()->latest()->first();

        $timeAccounts =  $user->timeAccounts()->withTrashed()->with(['timeAccountSetting'])->get(["id", "user_id", "balance", "balance_limit", "time_account_setting_id", "name", "deleted_at"]);

        $userTransactions = TimeAccountTransaction::forUser($user)->with('user:id,first_name,last_name')->latest()->paginate(15);

        return Inertia::render('User/UserShow', [
            'user' => $user->load(['groupUser', 'operatingSiteUser', 'organizationUser']),
            'supervisors' => User::inOrganization()
                ->where('is_supervisor', true)
                ->whereNotIn('id', $user->allSuperviseesFlat()->pluck('id'))
                ->get(['id', 'first_name', 'last_name']),
            'time_accounts' => $timeAccounts,
            'time_account_settings' => TimeAccountSetting::inOrganization()->get(['id', 'type', 'truncation_cycle_length_in_months']),
            'defaultTimeAccountId' => $user->defaultTimeAccount()->id,
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
                ]
            ],
        ]);
    }

    public function store(Request $request)
    {
        Gate::authorize('create', User::class);
        $request['groupUser'] = $request['organizationUser'];

        $validated = $request->validate([
            "first_name" => "required|string",
            "last_name" => "required|string",
            "email" => "required|string|unique:users",
            "date_of_birth" => "nullable|date",
            "city" => "nullable|string",
            "zip" => "nullable|string",
            "street" => "nullable|string",
            "house_number" => "nullable|string",
            "address_suffix" => "nullable|string",
            "country" => "nullable|string",
            "federal_state" => "nullable|string",
            "phone_number" => "nullable|string",
            "staff_number" => "nullable|integer",
            "password" => "required|string",
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

            'userWorkingHours' => 'required|decimal:0,2',
            'userWorkingHoursSince' => 'required|date',

            'userWorkingWeek' => 'required|array',
            'userWorkingWeek.*' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'userWorkingWeekSince' => 'required|date',

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
            'groupUser' => 'required|array',
            'groupUser.*' => ['nullable', function ($attribute, $value, $fail) {
                if (!in_array(explode('.', $attribute)[1], collect(User::$PERMISSIONS)->only(['all', 'group'])->flatten(1)->map(fn($p) => $p['name'])->toArray())) {
                    $fail('The ' . $attribute . ' is invalid.');
                }
                if (!in_array($value, ['read', 'write', null])) {
                    $fail('The ' . $attribute . ' is invalid.');
                }
            }]
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
            'password' => $validated['password'],
            'group_id' => $validated['group_id'],
            'is_supervisor' => $validated['is_supervisor'],
            'supervisor_id' => $validated['supervisor_id'],
            'operating_site_id' => $validated['operating_site_id'],
            'date_of_birth' => Carbon::parse($validated['date_of_birth']),
            'email_verified_at' => now(),
        ]);

        foreach ($validated['permissions'] as $permission) {
            $user[$permission] = true;
        }
        $user->password = Hash::make($validated['password']);
        $user->save();

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
            'name' => 'Standardkonto',
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

        GroupUser::create([
            "user_id" => $user->id,
            "group_id" => $user->group_id,
            ...$validated['groupUser']
        ]);

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

        $validated = $request->validate([
            "first_name" => "required|string",
            "last_name" => "required|string",
            "email" => "required|string",
            "date_of_birth" => "nullable|date",
            "city" => "nullable|string",
            "zip" => "nullable|string",
            "street" => "nullable|string",
            "house_number" => "nullable|string",
            "address_suffix" => "nullable|string",
            "country" => "nullable|string",
            "federal_state" => "nullable|string",
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
                    ->whereNotIn('id', $user->allSuperviseesFlat()->pluck('id'))
            ],
            'is_supervisor' => 'required|boolean',
            'userWorkingHours' => 'required|decimal:0,2',
            'userWorkingHoursSince' => 'required|date',
            'userWorkingWeek' => 'required|array',
            'userWorkingWeek.*' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'userWorkingWeekSince' => 'required|date',

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
            'groupUser' => 'required|array',
            'groupUser.*' => ['nullable', function ($attribute, $value, $fail) {
                if (!in_array(explode('.', $attribute)[1], collect(User::$PERMISSIONS)->only(['all', 'group'])->flatten(1)->map(fn($p) => $p['name'])->toArray())) {
                    $fail('The ' . $attribute . ' is invalid.');
                }
                if (!in_array($value, ['read', 'write', null])) {
                    $fail('The ' . $attribute . ' is invalid.');
                }
            }]
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
        ]);
        $user->organizationUser->update($validated['organizationUser']);
        $user->operatingSiteUser->update($validated['operatingSiteUser']);
        $user->groupUser->update($validated['groupUser']);

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
            $workingWeek[$day] = in_array($day, $validated['userWorkingWeek']) ? 1 : 0;
        }

        if ($workingWeek->isDirty()) $workingWeek->replicate()->save();

        return back()->with("success", "Mitarbeitenden erfolgreich aktualisiert.");
    }
}
