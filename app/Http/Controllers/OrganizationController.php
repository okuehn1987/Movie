<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrganizationController extends Controller
{
    public function index()
    {
        return Inertia::render('Organization/Index', ['organizations' => Organization::all()]);
    }
}
