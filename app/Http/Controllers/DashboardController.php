<?php

namespace App\Http\Controllers;

use App\Models\Absence;
use App\Models\User;
use App\Models\WorkLog;
use App\Models\WorkLogPatch;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $user = User::find(Auth::id());
        $isSupervisor = User::where('supervisor_id', $user->id)->exists();

        $patches = null;
        if ($user->work_log_patching && $isSupervisor) {
            $patches = WorkLogPatch::select(['id', 'start', 'end', 'is_home_office', 'user_id', 'work_log_id'])->inOrganization()
                ->where('status', 'created')
                ->with(['workLog:id,start,end,is_home_office', 'user:id,first_name,last_name'])->get();
        }

        $absences = null;
        if ($isSupervisor) {
            $absences = Absence::whereIn('user_id', User::where('supervisor_id', $user->id)->select('id'))
                ->where('status', 'created')
                ->with(['user:id,first_name,last_name', 'absenceType:id,name'])
                ->get(['id', 'start', 'end', 'user_id', 'absence_type_id']);
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
            ]
        ]);
    }
}
