<?php

namespace App\Http\Controllers;

use App\Models\AbsenceType;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class AbsenceTypeController extends Controller
{
    public function store(Request $request)
    {
        Gate::authorize('create', AbsenceType::class);

        $validated = $request->validate([
            "name" =>  "required|string",
            "abbreviation" =>  "required|string",
            "type" =>  ['required', Rule::in(AbsenceType::getTypes())],
            "requires_approval" => "required|boolean"
        ], [
            'type.in' => 'Typ muss in ' . join(', ', AbsenceType::getTypes()) . ' vorhanden sein'
        ]);

        $absenceType = new AbsenceType($validated);
        $absenceType->organization_id = Organization::getCurrent()->id;
        $absenceType->save();

        return back()->with('success', "Abwesenheitstyp erfolgreich gespeichert.");
    }

    public function update(Request $request, AbsenceType $absenceType)
    {
        Gate::authorize('update', AbsenceType::class);

        $validated = $request->validate([
            "name" => 'required|string',
            "abbreviation" => 'required|string',
            "type" =>  ['required', Rule::in(AbsenceType::getTypes())],
            "requires_approval" => "required|boolean"
        ]);

        $absenceType->update([$validated]);

        return back()->with('success', "Abwesenheitsgrund erfolgreich aktualisiert.");
    }

    public function destroy(Request $request, AbsenceType $absenceType)
    {
        Gate::authorize('delete', AbsenceType::class);

        $absenceType->delete();

        return back()->with('success', "Abwesenheitstyp erfolgreich gelöscht.");
    }
}
