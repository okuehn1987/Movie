<?php

namespace App\Http\Controllers;

use App\Models\SpecialWorkingHoursFactor;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SpecialWorkingHoursFactorController extends Controller
{
    public function index()
    {
        return Inertia::render('SWHF/SWHFIndex', ['factors' => SpecialWorkingHoursFactor::inOrganization()->get()]);
    }
}
