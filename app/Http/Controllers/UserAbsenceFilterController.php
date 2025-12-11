<?php

namespace App\Http\Controllers;

use App\Enums\Status;
use App\Models\AbsenceType;
use App\Models\OperatingSite;
use App\Models\Organization;
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

            'is_default' => 'required|boolean',

            'selected_users' => 'present|array',
            'selected_users.*' => Rule::exists('users', 'id')->whereIn('operating_site_id', OperatingSite::inOrganization()->select('id')),

            'selected_absence_types' => 'present|array',
            'selected_absence_types.*' => Rule::exists('absence_types', 'id')->where('organization_id', Organization::getCurrent()->id),

            'selected_operating_sites' => 'present|array',
            'selected_operating_sites.*' => Rule::exists('operating_sites', 'id')->where('organization_id', Organization::getCurrent()->id),

            'selected_groups' => 'present|array',
            'selected_groups.*' => Rule::exists('groups', 'id')->where('organization_id', Organization::getCurrent()->id),

            "selected_statuses" => 'present|array',
            "selected_statuses.*" => Rule::enum(Status::class),

            "selected_holidays" => 'present|array',
            "selected_holidays.*" => Rule::in(array_keys(HolidayService::$COUNTRIES['DE']['regions']))
        ]);

        if ($validated['is_default']) $authUser->userAbsenceFilters()->where('is_default', true)->update(['is_default' => false]);

        UserAbsenceFilter::create([
            'user_id' => $authUser->id,
            'name' => $validated['set'],
            'is_default' => $validated['is_default'],
            'data' => [
                'version' => 'v2',
                'user_ids' => $validated['selected_users'],
                'absence_type_ids' => $validated['selected_absence_types'],
                'operating_site_ids' => $validated['selected_operating_sites'],
                'group_ids' => $validated['selected_groups'],
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
            'is_default' => 'required|boolean',

            'selected_users' => 'present|array',
            'selected_users.*' => Rule::exists('users', 'id')->whereIn('operating_site_id', OperatingSite::inOrganization()->select('id')),

            'selected_absence_types' => 'present|array',
            'selected_absence_types.*' => Rule::exists('absence_types', 'id')->where('organization_id', Organization::getCurrent()->id),

            'selected_operating_sites' => 'present|array',
            'selected_operating_sites.*' => Rule::exists('operating_sites', 'id')->where('organization_id', Organization::getCurrent()->id),

            'selected_groups' => 'present|array',
            'selected_groups.*' => Rule::exists('groups', 'id')->where('organization_id', Organization::getCurrent()->id),

            "selected_statuses" => 'present|array',
            "selected_statuses.*" => Rule::enum(Status::class),

            "selected_holidays" => 'present|array',
            "selected_holidays.*" => Rule::in(array_keys(HolidayService::$COUNTRIES['DE']['regions']))
        ]);

        if ($validated['is_default']) UserAbsenceFilter::where('user_id', $userAbsenceFilter->user_id)->where('is_default', true)->update(['is_default' => false]);

        $userAbsenceFilter->update([
            'is_default' => $validated['is_default'],
            'data' => [
                'user_ids' => $validated['selected_users'],
                'absence_type_ids' => $validated['selected_absence_types'],
                'statuses' => $validated['selected_statuses'],
                'operating_site_ids' => $validated['selected_operating_sites'],
                'group_ids' => $validated['selected_groups'],
                'holidays_from_federal_states' => $validated['selected_holidays'],
            ]
        ]);

        return back()->with('success', 'Filtergruppe erfolgreich aktualisiert.');
    }

    public function destroy(UserAbsenceFilter $userAbsenceFilter)
    {
        Gate::authorize('publicAuth', User::class);

        $userAbsenceFilter->delete();

        return back()->with('success', 'Filtergruppe erfolgreich gelöscht.');
    }
}
