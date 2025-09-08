<?php

namespace App\Http\Controllers;

use App\Models\AbsenceType;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use App\Models\UserAbsenceFilter;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;

class UserAbsenceFilterController extends Controller
{
    public function store(Request $request, #[CurrentUser] User $authUser)
    {
        $validated = $request->validate([
            'set_name' => 'required|string',

            'filtered_users' => 'present|array',
            'filtered_users.*' => Rule::in(User::inOrganization()->pluck('id')),

            'filtered_absence_types' => 'present|array',
            'filtered_absence_types.*' => Rule::in(AbsenceType::inOrganization()->pluck('id')),

            "filtered_statuses" => 'present|array',
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
