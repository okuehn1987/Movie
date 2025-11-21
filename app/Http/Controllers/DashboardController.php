<?php

namespace App\Http\Controllers;

use App\Models\Absence;
use App\Models\User;
use App\Services\AppModuleService;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(#[CurrentUser] User $user)
    {
        Gate::authorize('publicAuth', User::class);

        $visibleUsers = User::inOrganization()
            ->with(['operatingSite.currentAddress:id,country,federal_state,addresses.addressable_id,addresses.addressable_type'])
            ->get(['id', 'supervisor_id', 'group_id', 'operating_site_id', 'first_name', 'last_name'])
            ->filter(fn($u) => $user->can('viewShow', [Absence::class, $u]));


        $currentAbsences = $visibleUsers->map(fn($u) => [...$u->currentAbsencePeriod, 'name' => $u->name])
            ->filter(fn($a) => $a['end'] !== null)
            ->values();


        $hertaProps = [];
        if (AppModuleService::hasAppModule('tide')) {
            $lastWeekEntries =
                $user->currentWeekShifts
                ->flatMap
                ->entries
                ->filter(fn($e) => $e->end)
                ->sortByDesc('start')
                ->map(fn($e) => collect($e->append('duration'))->only(['id', 'start', 'end', 'duration']))
                ->values();

            $hertaProps = [
                'overtime' => $user->overtime,
                'workingHours' => $user->currentWeekWorkingHours,
                'lastWeekEntries' => $lastWeekEntries,
            ];
        }

        return Inertia::render('Dashboard/Dashboard', [
            'user' => [...$user->load(['latestWorkLog'])->toArray(), 'current_shift' => $user->currentShift()->first(['id', 'user_id', 'start', 'end'])?->append('current_work_duration')],
            'supervisor' => User::select('id', 'first_name', 'last_name')->find($user->supervisor_id),
            'currentAbsences' => $currentAbsences,
            ...$hertaProps,
        ]);
    }
}
