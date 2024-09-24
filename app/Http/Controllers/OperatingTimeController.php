<?php

namespace App\Http\Controllers;

use App\Models\OperatingTime;
use Illuminate\Http\Request;

class OperatingTimeController extends Controller
{
    public function store(Request $request)
    {
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

        return back();
    }

    public function destroy(Request $request, OperatingTime $operatingTime)
    {
        $operatingTime->delete();

        return back();
    }
}
