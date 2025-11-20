<?php

namespace App\Http\Controllers;

use App\Enums\Status;
use App\Models\AbsenceType;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use App\Models\UserAbsenceFilter;
use App\Services\HolidayService;
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
            "selected_statuses.*" => Rule::enum(Status::class),

            "selected_holidays" => 'present|array',
            "selected_holidays.*" => Rule::in(array_keys(HolidayService::$COUNTRIES['DE']['regions']))
        ]);

        UserAbsenceFilter::create([
            'user_id' => $authUser->id,
            'name' => $validated['set'],
            'data' => [
                'version' => 'v1',
                'user_ids' => $validated['selected_users'],
                'absence_type_ids' => $validated['selected_absence_types'],
                'statuses' => $validated['selected_statuses'],
                'holidays_from_federal_states' => $validated['selected_holidays'],
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
            "selected_statuses.*" => Rule::enum(Status::class),

            "selected_holidays" => 'present|array',
            "selected_holidays.*" => Rule::in(array_keys(HolidayService::$COUNTRIES['DE']['regions']))
        ]);

        $userAbsenceFilter->update([
            'data' => [
                'user_ids' => $validated['selected_users'],
                'absence_type_ids' => $validated['selected_absence_types'],
                'statuses' => $validated['selected_statuses'],
                'holidays_from_federal_states' => $validated['selected_holidays'],
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
