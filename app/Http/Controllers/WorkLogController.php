<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\WorkLog;
use App\Models\WorkLogPatch;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class WorkLogController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewIndex', WorkLog::class);

        return Inertia::render('WorkLog/WorkLogIndex', [
            'users' => [
                ...User::inOrganization()
                    ->whereHas('workLogs')->with(['defaultTimeAccount:id,balance,user_id', 'latestWorkLog'])
                    ->select(['id', 'first_name', 'last_name', 'supervisor_id'])
                    ->get()
                    ->filter(fn($u) => $request->user()->can('viewShow', [WorkLog::class, $u]))
            ],
        ]);
    }

    public function store(Request $request, #[CurrentUser] User $user)
    {
        Gate::authorize('create', WorkLog::class);

        $last = $user->latestWorkLog;

        $validated = $request->validate([
            'is_home_office' => 'required|boolean',
        ]);

        WorkLog::updateOrCreate(['id' => $last->end ? null : $last->id], [
            ...$validated,
            'start' => $last->end ? now() : $last->start,
            'end' => $last->end ? null : now(),
            'user_id' => Auth::id(),
            'status' => 'accepted',
            'accepted_at' => now()
        ]);

        return back()->with('success', 'Arbeitsstatus erfolgreich eingetragen.');
    }
    public function userWorkLogs(User $user)
    {
        Gate::authorize('viewShow', [WorkLog::class, $user]);

        return Inertia::render('WorkLog/UserWorkLogIndex', [
            'user' => $user->only('id', 'first_name', 'last_name'),
            'workLogs' => WorkLog::where('user_id', $user->id)
                ->whereNotNull('end')
                ->with('patches:id,work_log_id,updated_at,status,start,end,is_home_office,comment')
                ->orderBy('start', 'DESC')
                ->get(),
            'can' => [
                'workLogPatch' => [
                    'create' => Gate::allows('create', [WorkLogPatch::class, $user]),
                    'delete' => Gate::allows('delete', [WorkLogPatch::class, $user]),
                ]
            ]
        ]);
    }
}
