<?php

namespace App\Http\Controllers;

use App\Models\OperatingSite;
use App\Models\OperatingTime;
use App\Models\Organization;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OperatingSiteController extends Controller
{
    public function index()
    {
        return Inertia::render('OperatingSite/OperatingSiteIndex', ['operatingSites' => OperatingSite::inOrganization()->withCount('users')->paginate(12)]);
    }
    public function show(OperatingSite $operatingSite)
    {
        return Inertia::render('OperatingSite/OperatingSiteShow', [
            'operatingSite' => $operatingSite,
            'operatingTimes' => OperatingTime::inOrganization()->where('operating_site_id', $operatingSite->id)->get()
        ]);
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'address_suffix' => "nullable|string",
            'city' => "required|string",
            'country' => "required|string",
            'email' => "required|email",
            'fax' => "nullable|string",
            'federal_state' => "required|string",
            'house_number' => "required|string",
            'is_head_quarter' => "required|boolean",
            'phone_number' => "required|string",
            'street' => "required|string",
            'zip' => "required|string",
        ]);

        OperatingSite::create([...$validated, 'organization_id' => Organization::getCurrent()->id]);

        return back()->with('success', 'Betriebsstätte wurde erfolgreich erstellt.');
    }
    public function update(Request $request, OperatingSite $operatingSite)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'address_suffix' => "nullable|string",
            'city' => "required|string",
            'country' => "required|string",
            'email' => "required|email",
            'fax' => "nullable|string",
            'federal_state' => "required|string",
            'house_number' => "required|string",
            'is_head_quarter' => "required|boolean",
            'phone_number' => "required|string",
            'street' => "required|string",
            'zip' => "required|string",
        ]);

        $operatingSite->update($validated);

        return back();
    }

    public function destroy(OperatingSite $operatingSite)
    {
        if ($operatingSite->users->count() > 0) return abort(405);

        $operatingSite->delete();

        return back();
    }
}
