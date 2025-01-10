<?php

namespace App\Http\Controllers;

use App\Models\Absence;
use App\Models\User;
use App\Models\WorkLog;
use App\Models\WorkLogPatch;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('publicAuth', User::class);

        $user = $request->user();

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

        $lastWeekWorkLogs = $user->workLogs()
            ->where(
                fn($q) => $q
                    ->where('start', '>=', Carbon::now()->subDays(6)->startOfDay()->format('Y-m-d'))
                    ->orWhere('end', '>=', Carbon::now()->subDays(6)->startOfDay()->format('Y-m-d'))
            )
            ->whereNotNull('end')
            ->get(['id', 'start', 'end'])
            ->append('duration');

        return Inertia::render('Dashboard/Dashboard', [
            'lastWorkLog' => WorkLog::select('id', 'start', 'end', 'is_home_office')
                ->inOrganization()
                ->where('user_id', Auth::id())
                ->latest('start')->first(),
            'supervisor' => User::select('id', 'first_name', 'last_name')->find($user->supervisor_id),
            'operating_times' => $user->operatingSite->operatingTimes,
            'currentAbsences' => $currentAbsences,
            'overtime' => $user->overtime,
            'workingHours' => [
                'should' => $user->userWorkingHours()->where('active_since', '<=', Carbon::now()->format('Y-m-d'))->orderBy('active_since', 'Desc')->first()['weekly_working_hours'],
                'current' => User::getCurrentWeekWorkingHours($user)['totalHours'],
                'currentHomeOffice' => User::getCurrentWeekWorkingHours($user)['homeOfficeHours'],
            ],
            'lastWeekWorkLogs' => $lastWeekWorkLogs,
        ]);
    }
}
