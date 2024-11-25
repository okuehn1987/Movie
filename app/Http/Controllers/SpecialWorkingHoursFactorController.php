<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\SpecialWorkingHoursFactor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class SpecialWorkingHoursFactorController extends Controller
{
    public function show(SpecialWorkingHoursFactor $specialWorkingHoursFactor)
    {
        Gate::authorize('view', SpecialWorkingHoursFactor::class);

        return Inertia::render('SWHF/SWHFShow', ['specialWorkingHoursFactor' => $specialWorkingHoursFactor]);
    }

    public function store(Request $request)
    {
        Gate::authorize('create', SpecialWorkingHoursFactor::class);

        $validated = $request->validate([
            'type' => ['required', Rule::in(array_keys(SpecialWorkingHoursFactor::$TYPES))],
            'extra_charge' => 'required|numeric',
            'id' => 'nullable|exists:special_working_hours_factors,id'
        ], [
            'type.in' => 'Muss in ' . join(', ', SpecialWorkingHoursFactor::$TYPES) . ' vorhanden sein'
        ]);


        SpecialWorkingHoursFactor::updateOrCreate([
            'id' => $validated['id']
        ], [
            ...$validated,
            'organization_id' => Organization::getCurrent()->id
        ]);


        return back()->with('success', "Besonderer Arbeitszeitzuschlag erfolgreich gespeichert.");
    }


    public function destroy(Request $request, SpecialWorkingHoursFactor $specialWorkingHoursFactor)
    {
        Gate::authorize('delete', $specialWorkingHoursFactor);

        $specialWorkingHoursFactor->delete();

        return back()->with('success', 'Arbeitszuschlag erfolgreich gelöscht.');
    }
}
