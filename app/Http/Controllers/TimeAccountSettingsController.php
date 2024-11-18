<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\TimeAccountSetting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TimeAccountSettingsController extends Controller
{
    public function index()
    {
        return Inertia::render('TimeAccount/TimeAccountSettingsIndex', [
            'timeAccountSettings' => TimeAccountSetting::inOrganization()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string',
            'truncation_cycle_length_in_months' => 'nullable|string|in:1,3,6,12',
        ]);

        TimeAccountSetting::create([...$validated, 'organization_id' => Organization::getCurrent()->id]);

        return back()->with('success', 'Neue Variante erfolgreich erstellt.');
    }
}
