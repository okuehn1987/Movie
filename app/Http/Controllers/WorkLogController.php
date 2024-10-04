<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\WorkLog;
use App\Models\WorkLogPatch;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class WorkLogController extends Controller
{
    public function index()
    {
        return Inertia::render('WorkLog/WorkLogIndex', ['workLogs' => WorkLog::inOrganization()->get()]);
    }

    public function store(Request $request)
    {
        $last = (WorkLog::inOrganization()->where('user_id', Auth::id())->latest('start')->first());

        $validated = $request->validate([
            'is_home_office' => 'required|boolean',
            'id' => [
                'nullable',
                Rule::in([$last->id])
            ]
        ]);


        WorkLog::updateOrCreate(['id' => $validated['id']], [
            ...$validated,
            'start' => $validated['id'] ? $last->start :  Carbon::now(),
            'end' => $validated['id'] ? Carbon::now() : null,
            'user_id' => Auth::id()
        ]);

        return back();
    }
    public function userWorkLogs(Request $request, User $user)
    {
        return Inertia::render('WorkLog/UserWorkLogIndex', [
            'user' => $user,
            'workLogs' => WorkLog::where('user_id', $user->id)
                ->whereNotNull('end')
                ->with('workLogPatches:id,work_log_id,updated_at,status,start,end,is_home_office')
                ->orderBy('start', 'DESC')
                ->paginate(13),
        ]);
    }
}
