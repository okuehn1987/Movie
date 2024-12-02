<?php

namespace App\Http\Controllers;

use App\Models\OperatingTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class OperatingTimeController extends Controller
{
    public function store(Request $request)
    {
        Gate::authorize('create', OperatingTime::class);

        $validated = $request->validate([
            "start" => 'required|string',
            "end" => 'required|string',
            "type" => "required|string",
            "operating_site_id" => "required|integer"
        ]);

        OperatingTime::inOrganization()->updateOrCreate(
            [
                'operating_site_id' => $validated['operating_site_id'],
                "type" => $validated['type']
            ],
            [
                "start" => $validated["start"],
                "end" => $validated["end"]
            ]
        );

        return back()->with('success', 'Betriebszeit erfolgreich hinzugefügt.');
    }

    public function destroy(OperatingTime $operatingTime)
    {
        Gate::authorize('delete', $operatingTime);

        $operatingTime->delete();

        return back()->with('success', 'Betriebszeit erfolgreich gelöscht.');
    }

    // TODO: function update
}
