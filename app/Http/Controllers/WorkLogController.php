<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\User;
use App\Models\WorkLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class WorkLogController extends Controller
{
    public function index()
    {
        Gate::authorize('publicAuth', User::class);

        return Inertia::render('WorkLog/WorkLogIndex', [
            'users' => User::find(Auth::id())->supervisees()->get(['id', 'first_name', 'last_name'])->map(fn($u) => [
                ...$u->toArray(),
                'isPresent' => $u->workLogs()->latest()->first()->end ? true : false,
                'latestWorkLog' => $u->workLogs()->latest()->first()
            ])
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
                Rule::in([$last->id])
            ]
        ]);


        WorkLog::updateOrCreate(['id' => $validated['id']], [
            ...$validated,
            'start' => $validated['id'] ? $last->start :  Carbon::now(),
            'end' => $validated['id'] ? Carbon::now() : null,
            'user_id' => Auth::id()
        ]);

        return back()->with('success', 'Arbeitsstatus erfolgreich eingetragen.');
    }
    public function userWorkLogs(Request $request, User $user)
    {
        Gate::authorize('viewShow', $user);
        Gate::authorize('viewShow', [WorkLog::class, $user]);

        return Inertia::render('WorkLog/UserWorkLogIndex', [
            'user' => $user,
            'workLogs' => WorkLog::where('user_id', $user->id)
                ->with('workLogPatches:id,work_log_id,updated_at,status,start,end,is_home_office')
                ->orderBy('start', 'DESC')
                ->paginate(12),
        ]);
    }
}
