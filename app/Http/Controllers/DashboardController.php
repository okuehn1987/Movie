<?php

namespace App\Http\Controllers;

use App\Models\Absence;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(#[CurrentUser] User $user)
    {
        Gate::authorize('publicAuth', User::class);

        $visibleUsers = User::inOrganization()
            ->get(['id', 'supervisor_id', 'group_id', 'operating_site_id'])
            ->filter(fn($u) => $user->can('viewShow', [Absence::class, $u]))
            ->pluck('id');

        $currentAbsences = Absence::inOrganization()
            ->where('status', 'accepted')
            ->where('start', '<=', Carbon::now()->format('Y-m-d'))
            ->where('end', '>=', Carbon::now()->format('Y-m-d'))
            ->whereIn('user_id', $visibleUsers)
            ->with(['user:id,first_name,last_name,supervisor_id', 'absenceType:id,abbreviation'])
            ->get(['id', 'start', 'end', 'user_id', 'absence_type_id'])
            ->toArray();

        $lastWeekEntries =  collect([
            ...$user->currentWeekShifts
                ->flatMap
                ->entries
                ->filter(fn($e) => $e->end)
                ->sortByDesc('start')
                ->map(fn($e) => collect($e->append('duration'))->only(['id', 'start', 'end', 'duration']))
                ->toArray()
        ]);

        return Inertia::render('Dashboard/Dashboard', [
            'user' => [...$user->load(['latestWorkLog'])->toArray(), 'current_shift' => $user->currentShift()->first(['id', 'user_id', 'start', 'end'])?->append('current_work_duration')],
            'supervisor' => User::select('id', 'first_name', 'last_name')->find($user->supervisor_id),
            'currentAbsences' => $currentAbsences,
            'overtime' => $user->overtime,
            'workingHours' => $user->currentWeekWorkingHours,
            'lastWeekEntries' => $lastWeekEntries,
        ]);
    }
}
