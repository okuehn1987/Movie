<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\OperatingSite;
use App\Models\OperatingTime;
use App\Models\Organization;
use App\Services\HolidayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class OperatingSiteController extends Controller
{
    public function index()
    {
        Gate::authorize('viewIndex', OperatingSite::class);

        return Inertia::render('OperatingSite/OperatingSiteIndex', [
            'operatingSites' => OperatingSite::inOrganization()->with('currentAddress')->withCount('users')->paginate(12)->through(
                fn($operatingSite) => [
                    ...$operatingSite->toArray(),
                    'can' => [
                        'operatingSite' => [
                            'viewShow' => Gate::allows('viewShow', $operatingSite),
                            'delete' => Gate::allows('delete', $operatingSite),
                        ]
                    ]
                ]
            ),
            'countries' => HolidayService::getCountries(),
            'can' => [
                'operatingSite' => [
                    'create' => Gate::allows('create', OperatingSite::class),
                ]
            ]
        ]);
    }
    public function show(OperatingSite $operatingSite)
    {
        Gate::authorize('viewShow', $operatingSite);

        return Inertia::render('OperatingSite/OperatingSiteShow', [
            'operatingSite' => $operatingSite->load(['operatingTimes', 'currentAddress']),
            'countries' => HolidayService::getCountries(),
            'can' => [
                'operatingSite' => [
                    'update' => Gate::allows('update', $operatingSite),
                ],
                'operatingTime' => [
                    'viewIndex' => Gate::allows('viewIndex', OperatingTime::class),
                    'create' => Gate::allows('create', OperatingTime::class),
                    'delete' => Gate::allows('delete', OperatingTime::class),
                ]
            ]
        ]);
    }
    public function store(Request $request)
    {
        Gate::authorize('create', OperatingSite::class);

        $validated = $request->validate([
            'name' => 'required|string',
            'address_suffix' => "nullable|string",
            'city' => "required|string",
            "country" => ["required", Rule::in(HolidayService::getCountryCodes())],
            "federal_state" => ["required", Rule::in(HolidayService::getRegionCodes($request["country"]))],
            'email' => "required|email",
            'fax' => "nullable|string",
            'house_number' => "required|string",
            'is_headquarter' => "required|boolean",
            'phone_number' => "required|string",
            'street' => "required|string",
            'zip' => "required|string",
        ]);

        $operatingSite = OperatingSite::create([
            ...collect($validated)->except(Address::$ADDRESS_KEYS),
            'organization_id' => Organization::getCurrent()->id
        ]);
        $operatingSite->addresses()->create(collect($validated)->only(Address::$ADDRESS_KEYS)->toArray());

        return back()->with('success', 'Betriebsstätte erfolgreich erstellt.');
    }
    public function update(Request $request, OperatingSite $operatingSite)
    {
        Gate::authorize('update', $operatingSite);

        $validated = $request->validate([
            'name' => 'required|string',
            'address_suffix' => "nullable|string",
            'city' => "required|string",
            "country" => ["required", Rule::in(HolidayService::getCountryCodes())],
            "federal_state" => ["required", Rule::in(HolidayService::getRegionCodes($request["country"]))],
            'email' => "required|email",
            'fax' => "nullable|string",
            'house_number' => "required|string",
            'is_headquarter' => "required|boolean",
            'phone_number' => "required|string",
            'street' => "required|string",
            'zip' => "required|string",
        ]);

        if ($validated['is_headquarter'] && $operatingSite->is_headquarter === false) {
            OperatingSite::inOrganization()->where('is_headquarter', true)->update(['is_headquarter' => false]);
        }

        // FIXME: wir brauchen active since und sync wie userworkinghours
        $operatingSite->update(collect($validated)->except(Address::$ADDRESS_KEYS)->toArray());
        $operatingSite->addresses()->create(collect($validated)->only(Address::$ADDRESS_KEYS)->toArray());

        return back()->with('success', 'Betriebsstätte erfolgreich aktualisiert.');
    }

    public function destroy(OperatingSite $operatingSite)
    {
        Gate::authorize('delete', $operatingSite);

        if ($operatingSite->users->count() > 0) return abort(405);

        $operatingSite->delete();

        return back()->with('success', 'Betriebsstätte erfolgreich gelöscht.');
    }
}
