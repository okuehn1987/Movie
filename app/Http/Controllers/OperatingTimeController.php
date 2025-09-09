<?php

namespace App\Http\Controllers;

use App\Models\OperatingSite;
use App\Models\OperatingTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class OperatingTimeController extends Controller
{
    public function store(Request $request, OperatingSite $operatingSite)
    {
        Gate::authorize('create', OperatingTime::class);

        $validated = $request->validate([
            "start" => 'required|string',
            "end" => 'required|string',
            "type" => "required|string",
        ]);

        $operatingSite->operatingTimes()->updateOrCreate(
            [
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
