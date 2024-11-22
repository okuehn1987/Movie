<?php

namespace App\Http\Controllers;

use App\Models\OperatingSite;
use App\Models\OperatingTime;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class OperatingSiteController extends Controller
{
    public function index()
    {
        Gate::authorize('viewIndex', OperatingSite::class);

        return Inertia::render('OperatingSite/OperatingSiteIndex', ['operatingSites' => OperatingSite::inOrganization()->withCount('users')->paginate(12)]);
    }
    public function show(OperatingSite $operatingSite)
    {
        Gate::authorize('viewShow', $operatingSite);

        return Inertia::render('OperatingSite/OperatingSiteShow', [
            'operatingSite' => $operatingSite,
            'operatingTimes' => OperatingTime::inOrganization()->where('operating_site_id', $operatingSite->id)->get()
        ]);
    }
    public function store(Request $request)
    {
        Gate::authorize('create', OperatingSite::class);

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

        return back()->with('success', 'Betriebsstätte erfolgreich erstellt.');
    }
    public function update(Request $request, OperatingSite $operatingSite)
    {
        Gate::authorize('update', $operatingSite);

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

        if ($validated['is_head_quarter'] && $operatingSite->is_head_quarter === false) {
            OperatingSite::inOrganization()->where('is_head_quarter', true)->update(['is_head_quarter' => false]);
        }

        $operatingSite->update($validated);

        return back()->with('success', 'Betriebsstätte erfolgreich aktualisiert.');
    }

    public function destroy(OperatingSite $operatingSite)
    {
        Gate::authorize('delete', $operatingSite);

        if ($operatingSite->users->count() > 0) return abort(405);

        $operatingSite->delete();

        return back()->with('success', 'Betriebsstätte erfolgreich gelöscht.');
    }
}
