<?php

namespace App\Http\Controllers;

use App\Models\AbsenceType;
use Inertia\Inertia;

class AbsenceTypeController extends Controller
{
    public function index()
    {
        return Inertia::render('AbsenceType/AbsenceTypeIndex', ['absenceTypes' => AbsenceType::inOrganization()->get(), 'types' => AbsenceType::getTypes()]);
    }
}
