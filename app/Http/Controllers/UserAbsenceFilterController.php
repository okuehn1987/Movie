<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserAbsenceFilterController extends Controller
{
    public function store(Request $request)
    {
        dd($request->all());

        $validated = $request->validate([
            'set_name' => 'required|string',

            'filtered_users' => 'nullable|array',
            'filtered_users.*' => 'integer',

            'filtered_absence_types' => 'nullable|array',
            'filtered_absence_types.*' => 'integer',

            "filtered_statuses" => 'nullable|array',
            "filtered_statuses.*" => 'in:created,accepted,declined',
        ]);
    }

    public function update(Request $request)
    {
        //
    }

    public function destroy(Request $request)
    {
        //
    }
}
