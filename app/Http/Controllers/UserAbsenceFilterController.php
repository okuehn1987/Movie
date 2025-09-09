<?php

namespace App\Http\Controllers;

use App\Models\AbsenceType;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use App\Models\UserAbsenceFilter;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class UserAbsenceFilterController extends Controller
{
    public function store(Request $request, #[CurrentUser] User $authUser)
    {
        Gate::authorize('publicAuth', User::class);

        $validated = $request->validate([
            'set' => 'required|string',

            'selected_users' => 'present|array',
            'selected_users.*' => Rule::in(User::inOrganization()->pluck('id')),

            'selected_absence_types' => 'present|array',
            'selected_absence_types.*' => Rule::in(AbsenceType::inOrganization()->pluck('id')),

            "selected_statuses" => 'present|array',
            "selected_statuses.*" => 'in:created,accepted,declined',
        ]);

        UserAbsenceFilter::create([
            'user_id' => $authUser->id,
            'name' => $validated['set'],
            'data' => [
                'user_ids' => $validated['selected_users'],
                'absence_type_ids' => $validated['selected_absence_types'],
                'statuses' => $validated['selected_statuses'],
            ],
        ]);

        return back()->with('success', 'Filtergruppe erfolgreich erstellt.');
    }

    public function update(Request $request, UserAbsenceFilter $userAbsenceFilter)
    {
        Gate::authorize('publicAuth', User::class);

        $validated = $request->validate([
            'selected_users' => 'present|array',
            'selected_users.*' => Rule::in(User::inOrganization()->pluck('id')),

            'selected_absence_types' => 'present|array',
            'selected_absence_types.*' => Rule::in(AbsenceType::inOrganization()->pluck('id')),

            "selected_statuses" => 'present|array',
            "selected_statuses.*" => 'in:created,accepted,declined',
        ]);

        $userAbsenceFilter->update([
            'data' => [
                'user_ids' => $validated['selected_users'],
                'absence_type_ids' => $validated['selected_absence_types'],
                'statuses' => $validated['selected_statuses'],
            ]
        ]);

        return back()->with('success', 'Filtergruppe erfolgreich aktualisiert.');
    }

    public function destroy(Request $request, UserAbsenceFilter $userAbsenceFilter)
    {
        Gate::authorize('publicAuth', User::class);

        $userAbsenceFilter->delete();

        return back()->with('success', 'Filtergruppe erfolgreich gelöscht.');
    }
}
