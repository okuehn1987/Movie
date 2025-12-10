<?php

namespace App\Http\Controllers;

use App\Models\AbsenceType;
use App\Models\ChatAssistant;
use App\Models\GroupUser;
use App\Models\OperatingSite;
use App\Models\OperatingSiteUser;
use App\Models\OperatingTime;
use App\Models\Organization;
use App\Models\OrganizationUser;
use App\Models\SpecialWorkingHoursFactor;
use App\Models\TimeAccountSetting;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use App\Services\HolidayService;
use App\Services\OpenAIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class OrganizationController extends Controller
{
    public function index()
    {
        Gate::authorize('viewIndex', Organization::class);

        $organizations = Organization::with(['chatAssistant:id,organization_id,created_at,updated_at', 'owner'])
            ->get(['id', 'owner_id', 'name', 'created_at'])
            ->map(function ($organization) {
                $chatAssistant = $organization->chatAssistant;

                return [
                    ...$organization->toArray(),
                    'stats'            => $chatAssistant
                        ? IsaController::getStatsForAssistant($chatAssistant)
                        : null,
                ];
            });
        return Inertia::render('Organization/OrganizationIndex', [
            'organizations' => $organizations,
            'countries'     => HolidayService::getCountries(),
        ]);
    }

    public function store(Request $request)
    {
        Gate::authorize('create', Organization::class);

        $validated = $request->validate([
            'organization_name' => "required|string|unique:organizations,name",
            'organization_street' => "required|string",
            'organization_house_number' => "required|string",
            'organization_address_suffix' => "nullable|string",
            "organization_country" => ["required", Rule::in(HolidayService::getCountryCodes())],
            "organization_federal_state" => ["required", Rule::in(HolidayService::getRegionCodes($request["organization_country"]))],
            'organization_city' => "required|string",
            'organization_zip' => "required|string",
            'head_quarter_name' => "required|string",
            'first_name' => "required|string",
            'last_name' => "required|string",
            'email' => "required|string",
            'password' => "required|string",
            'date_of_birth' => "required|date",
            'isa_active' => 'required|boolean',
            'flow_active' => 'required|boolean',
            'tide_active' => 'required|boolean',
            'openai_api_key' => 'nullable|string',
        ]);

        $org = Organization::create([
            'name' => $validated['organization_name'],
            'isa_active' => $validated['isa_active'],
            'openai_api_key' => $validated['openai_api_key'],
        ]);

        if ($validated['isa_active']) {
            $isa = $org->chatAssistant()->create([
                'monthly_cost_limit' => 100,
            ]);
            OpenAIService::createAssistant($isa);
        }

        if ($validated['flow_active']) {
            $org->modules()->create([
                'module' => 'flow',
                'activated_at' => now(),
            ]);
        }

        if ($validated['tide_active']) {
            $org->modules()->create([
                'module' => 'tide',
                'activated_at' => now(),
            ]);
        }

        foreach (AbsenceType::$DEFAULTS as $type) {
            AbsenceType::create([
                "organization_id" => $org->id,
                'type' => $type['name'],
                ...$type,
            ]);
        }

        $operating_site = $org->operatingSites()->create([
            'name' => $validated['head_quarter_name'],
            'is_headquarter' => 1
        ]);

        $operating_site->addresses()->create([
            'street' => $validated['organization_street'],
            'house_number' => $validated['organization_house_number'],
            'address_suffix' => $validated['organization_address_suffix'],
            'country' => $validated['organization_country'],
            'city' => $validated['organization_city'],
            'zip' => $validated['organization_zip'],
            'federal_state' => $validated['organization_federal_state'],
        ]);

        $user = (new User)->forceFill([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'date_of_birth' => $validated['date_of_birth'],
            'organization_id' => $org->id,
            'operating_site_id' => $operating_site->id,
            'notification_channels' => ['database', 'mail'],
        ]);
        $user->save();
        $org->owner_id = $user->id;
        $org->save();


        OrganizationUser::create([
            "user_id" => $user->id,
            "organization_id" => $org->id,
            ...collect(User::$PERMISSIONS)->flatten(1)->map(fn($p) => $p['name'])->mapWithKeys(fn($p) => [$p => 'write'])->toArray()
        ]);

        OperatingSiteUser::create([
            "user_id" => $user->id,
            "operating_site_id" => $user->operating_site_id,
            ...collect([User::$PERMISSIONS['all'], User::$PERMISSIONS['operatingSite']])
                ->flatten(1)
                ->map(fn($p) => $p['name'])
                ->mapWithKeys(fn($p) => [$p => 'write'])
                ->toArray()
        ]);
        $org->timeAccountSettings()->createMany(TimeAccountSetting::getDefaultSettings());

        $defaultTimeAccountSetting = $org->timeAccountSettings()->where('truncation_cycle_length_in_months', null)->first();

        $user->timeAccounts()->create([
            'name' => 'Gleitzeitkonto',
            'balance' => 0,
            'balance_limit' => 0,
            'time_account_setting_id' => $defaultTimeAccountSetting->id,
        ]);

        $chatAssistant = ChatAssistant::create([
            'organization_id' => $org->id,
            'monthly_cost_limit' => 100,
        ]);

        $chatAssistant->vector_store_id = OpenAIService::createAssistant($chatAssistant);
        $chatAssistant->save();

        return back()->with('success', 'Organisation erfolgreich erstellt.');
    }

    public function show(Organization $organization)
    {
        Gate::authorize('viewShow', $organization);

        return Inertia::render('Organization/OrganizationShow', [
            'organization' => $organization,
            'flags' => Organization::$FLAGS,
            'operating_sites' => $organization->operatingSites()->get(),
            'absence_types' => $organization->absenceTypes()->get(),
            'absence_type_defaults' => AbsenceType::getTypes(),
            'special_working_hours_factors' => $organization->specialWorkingHoursFactors()->get(),
            'timeAccountSettings' => $organization->timeAccountSettings()->get(),
            'can' => [
                'organization' => [
                    'update' => Gate::allows('update', $organization),
                ],
                'specialWorkingHoursFactors' => [
                    'viewIndex' => Gate::allows('viewIndex', SpecialWorkingHoursFactor::class),
                    'create' => Gate::allows('create', SpecialWorkingHoursFactor::class),
                    'update' => Gate::allows('update', SpecialWorkingHoursFactor::class),
                    'delete' => Gate::allows('delete', SpecialWorkingHoursFactor::class),
                ],
                'absenceType' => [
                    'viewIndex' => Gate::allows('viewIndex', AbsenceType::class),
                    'create' => Gate::allows('create', AbsenceType::class),
                    'update' => Gate::allows('update', AbsenceType::class),
                    'delete' => Gate::allows('delete', AbsenceType::class),
                ],
                'timeAccountSetting' => [
                    'create' => Gate::allows('create', TimeAccountSetting::class),
                    'viewIndex' => Gate::allows('viewIndex', TimeAccountSetting::class),
                ]
            ]
        ]);
    }

    public function update(Request $request, Organization $organization)
    {
        Gate::authorize('update', $organization);

        $validated = $request->validate([
            'name' => "required|string",
            'tax_registration_id' => "nullable|string",
            "commercial_registration_id" => "nullable|string",
            "logo" => "nullable|image",
            "website" => "nullable|string",
            "night_surcharges" => "required|boolean",
            "vacation_limitation_period" => "required|boolean",
        ]);

        $path = $validated['logo'] ? Storage::disk('organization_logos')->putFile($validated['logo']) : null;

        if ($organization->logo && $path)
            Storage::disk('organization_logos')->delete($organization->logo);
        else
            $path = $organization->logo ?? $path;

        $organization->update([...$validated, 'logo' => $path]);

        return back()->with('success', 'Organisation erfolgreich aktualisiert.');
    }

    public function getLogo(Organization $organization)
    {
        Gate::authorize('publicAuth', User::class);

        return response(Storage::disk('organization_logos')->get($organization->logo));
    }

    public function destroy(Organization $organization)
    {
        Gate::authorize('delete', $organization);

        $organization->delete();

        return back()->with('success', 'Organisation erfolgreich gelöscht.');
    }

    public function organigram(Organization $organization)
    {
        Gate::authorize('viewShow', $organization) && Gate::authorize('viewIndex', User::class);

        return Inertia::render('Organization/OrganizationOrganigram', [
            'users' => User::inOrganization()->whereNull('supervisor_id')
                ->with('allSupervisees:id,first_name,last_name,supervisor_id,email,job_role')
                ->get(['id', 'first_name', 'last_name', 'supervisor_id', 'email', 'job_role']),
        ]);
    }

    public function saveSettings(Request $request)
    {
        Gate::authorize('update', Organization::getCurrent());
        $validated = $request->validate(collect(Organization::$FLAGS)->mapWithKeys(fn($_, $k) => [$k => 'required|boolean'])->toArray());

        Organization::getCurrent()->update([...$validated]);

        return back()->with('success', 'Einstellungen erfolgreich gespeichert.');
    }
}
