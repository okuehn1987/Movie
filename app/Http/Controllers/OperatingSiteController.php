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

        return Inertia::render('OperatingSite/OperatingSiteIndex', [
            'operatingSites' => OperatingSite::inOrganization()->withCount('users')->paginate(12)->through(
                fn($operatingSite) => [
                    ...$operatingSite->toArray(),
                    'can' => [
                        'operatingSite' => [
                            'viewShow' => Gate::allows('viewShow', $operatingSite),
                            'delete' => Gate::allows('delete', $operatingSite),
                        ]
                    ]
                ]
            ),
            'can' => [
                'operatingSite' => [
                    'create' => Gate::allows('create', OperatingSite::class),
                ]
            ]
        ]);
    }
    public function show(OperatingSite $operatingSite)
    {
        Gate::authorize('viewShow', $operatingSite);

        return Inertia::render('OperatingSite/OperatingSiteShow', [
            'operatingSite' => $operatingSite->load('operatingTimes'),
            'can' => [
                'operatingSite' => [
                    'update' => Gate::allows('update', $operatingSite),
                ],
                'operatingTime' => [
                    'viewIndex' => Gate::allows('viewIndex', OperatingTime::class),
                    'create' => Gate::allows('create', OperatingTime::class),
                    'delete' => Gate::allows('delete', OperatingTime::class),
                ]
            ]
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
            'is_headquarter' => "required|boolean",
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
            'is_headquarter' => "required|boolean",
            'phone_number' => "required|string",
            'street' => "required|string",
            'zip' => "required|string",
        ]);

        if ($validated['is_headquarter'] && $operatingSite->is_headquarter === false) {
            OperatingSite::inOrganization()->where('is_headquarter', true)->update(['is_headquarter' => false]);
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
