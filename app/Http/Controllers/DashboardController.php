<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\WorkLog;
use App\Models\WorkLogPatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $patches = null;
        if (Auth::user()->work_log_patching) {
            $patches = WorkLogPatch::select(['id', 'start', 'end', 'is_home_office', 'user_id', 'work_log_id'])->inOrganization()
                ->where('status', 'created')
                ->with(['workLog:id,start,end,is_home_office', 'user:id,first_name,last_name'])
                ->paginate(5);
        }

        return Inertia::render('Dashboard/Dashboard', [
            'lastWorkLog' => WorkLog::select('id', 'start', 'end', 'is_home_office')
                ->inOrganization()
                ->where('user_id', Auth::id())
                ->latest('start')->first(),
            'supervisor' => User::select('id', 'first_name', 'last_name')->find(Auth::user()->supervisor_id),
            'patches' => $patches
        ]);
    }
}
