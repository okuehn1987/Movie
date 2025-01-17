<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\WorkLog;
use App\Models\WorkLogPatch;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
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

    public function store(Request $request)
    {
        Gate::authorize('create', WorkLog::class);

        $last = (WorkLog::inOrganization()->where('user_id', Auth::id())->latest('start')->first());

        $validated = $request->validate([
            'is_home_office' => 'required|boolean',
            'id' => [
                'nullable',
                Rule::in([$last?->id])
            ]
        ]);


        WorkLog::updateOrCreate(['id' => array_key_exists('id', $validated) ? $validated['id'] : 0], [
            ...$validated,
            'start' => array_key_exists('id', $validated) && $validated['id'] ? $last->start :  Carbon::now(),
            'end' => array_key_exists('id', $validated) && $validated['id'] ? Carbon::now() : null,
            'user_id' => Auth::id()
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
                ->with('workLogPatches:id,work_log_id,updated_at,status,start,end,is_home_office,comment')
                ->orderBy('start', 'DESC')
                ->paginate(12),
            'can' => [
                'workLogPatch' => [
                    'create' => Gate::allows('create', [WorkLogPatch::class, $user]),
                    'update' => Gate::allows('update', [WorkLogPatch::class, $user]),
                ]
            ]
        ]);
    }
}
