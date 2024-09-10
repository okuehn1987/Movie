<?php

namespace App\Http\Controllers;

use App\Models\OperatingSite;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OperatingSiteController extends Controller
{
    public function index()
    {
        return Inertia::render('OperatingSite/OperatingSiteIndex', ['operatingSites' => OperatingSite::inOrganization()->get()]);
    }
}
