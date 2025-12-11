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

        $currentAbsences = $visibleUsers->map(fn($u) => [...$u->currentAbsencePeriod, 'last_name' => $u->last_name, 'first_name' => $u->first_name])
            ->filter(fn($a) => $a['end'] !== null && $a['start'] !== null)
            ->values();
        $tideProps = [];
        if (AppModuleService::hasAppModule('tide')) {
            $lastEntries =
                $user->shifts()
                ->where('start', '>=', now()->subDays(7))
                ->get()
                ->flatMap
                ->entries
                ->filter(fn($e) => $e->end)
                ->sortByDesc('start')
                ->map(fn($e) => collect($e->append('duration'))->only(['id', 'start', 'end', 'duration']))->take(5)
                ->values();

            $tideProps = [
                'overtime' => $user->overtime,
                'workingHours' => $user->currentWeekWorkingHours,
                'lastEntries' => $lastEntries,
            ];
        }

        return Inertia::render('Dashboard/Dashboard', [
            'user' => [
                ...$user->load(['latestWorkLog', 'currentTrustWorkingHours:id,user_trust_working_hours.user_id'])->toArray(),
                'current_shift' => $user->currentShift()->first(['id', 'user_id', 'start', 'end'])?->append('current_work_duration')
            ],
            'supervisor' => User::select('id', 'first_name', 'last_name')->find($user->supervisor_id),
            'currentAbsences' => $currentAbsences,
            ...$tideProps,
        ]);
    }
}
