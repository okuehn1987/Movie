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
        $isSupervisor = User::where('supervisor_id', $user->id)->exists(); //TODO: also look if the user has any permissions or delegations 

        $patches = null;
        if ($isSupervisor) {
            $patches = WorkLogPatch::inOrganization()->where('status', 'created')
                ->with(['workLog:id,start,end,is_home_office', 'user:id,first_name,last_name'])
                ->get(['id', 'start', 'end', 'is_home_office', 'user_id', 'work_log_id'])
                ->filter(fn($patch) => $user->can('update', $patch->user));
        }

        $visibleUsers = User::inOrganization()
            ->get(['id'])
            ->filter(fn($u) => $user->can('viewShow', [Absence::class, $u]))
            ->pluck('id');

        $absenceRequests = null;
        if ($isSupervisor) {
            $absenceRequests = Absence::inOrganization()
                ->where('status', 'created')
                ->whereIn('user_id', $visibleUsers)
                ->with(['user:id,first_name,last_name', 'absenceType:id,name'])
                ->get(['id', 'start', 'end', 'user_id', 'absence_type_id'])
                ->filter(fn($a) => $user->can('update', $a));
        }

        $currentAbsences = Absence::inOrganization()
            ->where('status', 'accepted')
            ->where('start', '<=', Carbon::now()->format('Y-m-d'))
            ->where('end', '>=', Carbon::now()->format('Y-m-d'))
            ->whereIn('user_id', $visibleUsers)
            ->with(['user:id,first_name,last_name', 'absenceType:id,abbreviation'])
            ->get(['id', 'start', 'end', 'user_id', 'absence_type_id'])
            ->toArray();

        return Inertia::render('Dashboard/Dashboard', [
            'lastWorkLog' => WorkLog::select('id', 'start', 'end', 'is_home_office')
                ->inOrganization()
                ->where('user_id', Auth::id())
                ->latest('start')->first(),
            'supervisor' => User::select('id', 'first_name', 'last_name')->find($user->supervisor_id),
            'patches' => $patches,
            'operating_times' => $user->operatingSite->operatingTimes,
            'absenceRequests' => $absenceRequests,
            'currentAbsences' => $currentAbsences,
            'overtime' => $user->overtime,
            'workingHours' => [
                'should' => $user->userWorkingHours()->where('active_since', '<=', Carbon::now()->format('Y-m-d'))->orderBy('active_since', 'Desc')->first()['weekly_working_hours'],
                'current' => User::getCurrentWeekWorkingHours($user)
            ],
        ]);
    }
}
