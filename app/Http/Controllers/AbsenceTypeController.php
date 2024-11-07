<?php

namespace App\Http\Controllers;

use App\Models\AbsenceType;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class AbsenceTypeController extends Controller
{
    // public function index()
    // {
    //     return Inertia::render('AbsenceType/AbsenceTypeIndex', ['absenceTypes' => AbsenceType::inOrganization()->get(), 'types' => AbsenceType::getTypes()]);
    // }

    public function store(Request $request)
    {
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

    public function destroy(Request $request, AbsenceType $absenceType)
    {
        $absenceType->delete();

        return back()->with('success', "Abwesenheitstyp erfolgreich gelöscht.");
    }
}
