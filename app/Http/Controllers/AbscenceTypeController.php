<?php

namespace App\Http\Controllers;

use App\Models\AbscenceType;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AbscenceTypeController extends Controller
{
    public function index()
    {
        return Inertia::render('AbscenceType/AbscenceTypeIndex', ['abscenceTypes' => AbscenceType::inOrganization()->get(), 'types' => AbscenceType::getTypes()]);
    }
}
