<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\TimeAccountSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class TimeAccountSettingsController extends Controller
{
    public function index()
    {
        Gate::authorize('viewIndex', TimeAccountSetting::class);

        return Inertia::render('TimeAccount/TimeAccountSettingsIndex', [
            'timeAccountSettings' => TimeAccountSetting::inOrganization()->get(),
            'can' => [
                'timeAccountSetting' => [
                    'create' => Gate::allows('create', TimeAccountSetting::class),
                ]
            ]
        ]);
    }

    public function store(Request $request)
    {
        Gate::authorize('create', TimeAccountSetting::class);

        $validated = $request->validate([
            'type' => 'required|string',
            'truncation_cycle_length_in_months' => 'nullable|string|in:1,3,6,12',
        ]);

        TimeAccountSetting::create([...$validated, 'organization_id' => Organization::getCurrent()->id]);

        return back()->with('success', 'Neue Variante erfolgreich erstellt.');
    }

    public function update(Request $request, TimeAccountSetting $timeAccountSetting)
    {
        // TODO: 
    }

    public function destroy(TimeAccountSetting $timeAccountSetting)
    {
        // TODO: 
    }
}
