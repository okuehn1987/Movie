<?php

namespace App\Http\Controllers;

use App\Models\AbsenceType;
use App\Models\OperatingSite;
use App\Models\OperatingTime;
use App\Models\Organization;
use App\Models\SpecialWorkingHoursFactor;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use App\Services\HolidayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class OrganizationController extends Controller
{
    public function index()
    {
        Gate::authorize('viewIndex', Organization::class);

        return Inertia::render('Organization/OrganizationIndex', ['organizations' => Organization::all(),  'countries' => HolidayService::getCountries()]);
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
            "organization_federal_state" => ["required", Rule::in(HolidayService::getRegionCodes($request["country"]))],
            'organization_city' => "required|string",
            'organization_zip' => "required|string",
            'head_quarter_name' => "required|string",
            'first_name' => "required|string",
            'last_name' => "required|string",
            'email' => "required|string",
            'password' => "required|string",
            'date_of_birth' => "required|date",
        ]);

        $org =  Organization::create([
            'name' => $validated['organization_name'],
        ]);

        $operating_site = OperatingSite::create([
            'name' => $validated['head_quarter_name'],
            'street' => $validated['organization_street'],
            'house_number' => $validated['organization_house_number'],
            'address_suffix' => $validated['organization_address_suffix'],
            'country' => $validated['organization_country'],
            'city' => $validated['organization_city'],
            'zip' => $validated['organization_zip'],
            'federal_state' => $validated['organization_federal_state'],
            'organization_id' => $org->id,
            'is_headquarter' => 1
        ]);
        $user = (new User)->forceFill([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'date_of_birth' => $validated['date_of_birth'],
            'organization_id' => $org->id,
            'operating_site_id' => $operating_site->id
        ]);
        $user->save();
        $org->owner_id = $user->id;
        $org->save();

        return back()->with('success', 'Organisation erfolgreich erstellt.');
    }

    public function show(Organization $organization)
    {
        Gate::authorize('viewShow', $organization);

        return Inertia::render('Organization/OrganizationShow', [
            'organization' => $organization,
            'operating_sites' => OperatingSite::inOrganization()->get(),
            'operating_times' => OperatingTime::inOrganization()->get(),
            'absence_types' => AbsenceType::inOrganization()->get(),
            'special_working_hours_factors' => SpecialWorkingHoursFactor::inOrganization()->get(),
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
            "logo" => "nullable|file",
            "website" => "nullable|string",
            "night_surcharges" => "required|boolean",
            "vacation_limitation_period" => "required|boolean",
        ]);

        $organization->update($validated);

        return back()->with('success', 'Organisation erfolgreich aktualisiert.');
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
            'users' => User::whereNull('supervisor_id')
                ->with('allSupervisees:id,first_name,last_name,supervisor_id,email')
                ->get(['id', 'first_name', 'last_name', 'supervisor_id', 'email']),
        ]);
    }
}
