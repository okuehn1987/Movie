<?php

namespace App\Http\Controllers;

use App\Models\AbsenceType;
use App\Models\Organization;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AbsenceTypeController extends Controller
{
    public function index()
    {
        return Inertia::render('AbsenceType/AbsenceTypeIndex', ['absenceTypes' => AbsenceType::inOrganization()->get(), 'types' => AbsenceType::getTypes()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            "name" =>  "required|string",
            "abbreviation" =>  "required|string",
            "type" =>  "required|string",
            "requires_approval" => "required|boolean"
        ]);

        $absenceType = new AbsenceType($validated);
        $absenceType->organization_id = Organization::getCurrent()->id;
        $absenceType->save();

        return back();
    }

    public function destroy(Request $request, AbsenceType $absenceType)
    {
        $absenceType->delete();

        return back();
    }
}
