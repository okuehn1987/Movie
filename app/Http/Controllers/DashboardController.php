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
        $isSupervisor = User::where('supervisor_id', $user->id)->exists();

        $patches = null;
        if ($isSupervisor) {
            $patches = WorkLogPatch::inOrganization()->where('status', 'created')
                ->with(['workLog:id,start,end,is_home_office', 'user:id,first_name,last_name'])
                ->get(['id', 'start', 'end', 'is_home_office', 'user_id', 'work_log_id'])
                ->filter(fn($patch) => $user->can('update', $patch->user));
        }

        $absences = null;
        if ($isSupervisor) {
            $absences = Absence::inOrganization()->where('status', 'created')
                ->with(['user:id,first_name,last_name', 'absenceType:id,name'])
                ->get(['id', 'start', 'end', 'user_id', 'absence_type_id'])
                ->filter(fn($absence) => $user->can('viewShow', $absence) && $user->can('update', $absence));
        }

        return Inertia::render('Dashboard/Dashboard', [
            'lastWorkLog' => WorkLog::select('id', 'start', 'end', 'is_home_office')
                ->inOrganization()
                ->where('user_id', Auth::id())
                ->latest('start')->first(),
            'supervisor' => User::select('id', 'first_name', 'last_name')->find($user->supervisor_id),
            'patches' => $patches,
            'operating_times' => $user->operatingSite->operatingTimes,
            'absences' => $absences,
            'overtime' => $user->overtime,
            'workingHours' => [
                'should' => $user->userWorkingHours()->where('active_since', '<=', Carbon::now()->format('Y-m-d'))->orderBy('active_since', 'Desc')->first()['weekly_working_hours'],
                'current' => User::getCurrentWeekWorkingHours($user)
            ],
        ]);
    }
}
