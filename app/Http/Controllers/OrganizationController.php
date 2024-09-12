<?php

namespace App\Http\Controllers;

use App\Models\OperatingSite;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class OrganizationController extends Controller
{
    public function index()
    {
        return Inertia::render('Organization/OrganizationIndex', ['organizations' => Organization::all()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'organization_name' => "required|string|unique:organizations,name",
            'organization_street' => "required|string",
            'organization_house_number' => "required|string",
            'organization_address_suffix' => "nullable|string",
            'organization_country' => "required|string",
            'organization_city' => "required|string",
            'organization_zip' => "required|string",
            'organization_federal_state' => "required|string",
            'first_name' => "required|string",
            'last_name' => "required|string",
            'email' => "required|string",
            'password' => "required|string",
            'date_of_birth' => "required|date",
        ]);

        $org =  Organization::create([
            'name' => $validated['organization_name'],
        ]);

        $operating_site = OperatingSite::create([
            'street' => $validated['organization_street'],
            'house_number' => $validated['organization_house_number'],
            'address_suffix' => $validated['organization_address_suffix'],
            'country' => $validated['organization_country'],
            'city' => $validated['organization_city'],
            'zip' => $validated['organization_zip'],
            'federal_state' => $validated['organization_federal_state'],
            'organization_id' => $org->id,
            'is_head_quarter' => 1
        ]);
        $user = (new User)->forceFill([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'date_of_birth' => $validated['date_of_birth'],
            'organization_id' => $org->id,
            'operating_site_id' => $operating_site->id
        ]);
        $user->save();
        $org->update(['owner_id' => $user->id]);

        return back();
    }
}
