<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\WorkLog;
use App\Models\WorkLogPatch;
use App\Notifications\DisputeStatusNotification;
use App\Notifications\WorkLogNotification;
use Carbon\Carbon;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class WorkLogController extends Controller
{
    public function index(#[CurrentUser] User $authUser)
    {
        Gate::authorize('viewIndex', WorkLog::class);

        return Inertia::render('WorkLog/WorkLogIndex', [
            'users' => [
                ...User::inOrganization()
                    ->whereHas('workLogs')->with(['defaultTimeAccount:id,balance,user_id', 'latestWorkLog', 'currentWorkingHours', 'currentWorkingWeek'])
                    ->get(['id', 'first_name', 'last_name', 'supervisor_id', 'time_balance_yellow_threshold', 'time_balance_red_threshold'])
                    ->filter(fn($u) => $authUser->can('viewShow', [WorkLog::class, $u]))
            ],
        ]);
    }

    public function store(Request $request, #[CurrentUser] User $authUser)
    {
        Gate::authorize('create', [WorkLog::class, $authUser]);

        $last = $authUser->latestWorkLog;

        if ($last && $last->end == null) {
            WorkLog::find($last->id)->update([
                'end' => now(),
            ]);
        } else {
            $validated = $request->validate([
                'is_home_office' => 'required|boolean',
            ]);
            WorkLog::create([
                ...$validated,
                'start' => now(),
                'end' => null,
                'user_id' => $authUser->id,
                'status' => 'accepted',
                'accepted_at' => now(),
            ]);
        }

        return back()->with('success', 'Arbeitsstatus erfolgreich eingetragen.');
    }

    public function createWorkLog(Request $request, User $user, #[CurrentUser] User $authUser)
    {
        Gate::authorize('create', [WorkLog::class, $authUser]);

        $validated = $request->validate([
            'start' => 'required|date',
            'end' => ['required', 'date', 'after:start', function ($attr, $value, $fail) use ($user) {
                $last = $user->latestWorkLog;
                $lastEnded = $last?->end != null;

                if ($last && !$lastEnded && $value > $last->shift->start) {
                    $fail('Der Eintrag muss vor der aktiven Schicht enden.');
                };
            }],
            'comment' => 'nullable|string',
            'is_home_office' => 'required|boolean',
        ]);

        $workLog = WorkLog::create([
            'is_home_office' => $validated['is_home_office'],
            'start' => Carbon::parse($validated['start']),
            'end' => Carbon::parse($validated['end']),
            'status' => 'created',
            'comment' => $validated['comment'],
            'user_id' => $user->id
        ]);

        $supervisor = $workLog->user->supervisor;
        $requiresApproval = $supervisor && $supervisor->id != $authUser->id;
        if (!$requiresApproval) $workLog->accept();
        else $supervisor->notify(new WorkLogNotification($workLog->user, $workLog));

        return back()->with('success', 'Buchung erfolgreich ' . ($requiresApproval ? 'beantragt.' : 'gespeichert.'));
    }

    public function update(Request $request, WorkLog $workLog, #[CurrentUser] User $authUser)
    {
        Gate::authorize('update', [WorkLog::class, $workLog->user]);

        $is_accepted = $request->validate([
            'accepted' => 'required|boolean'
        ])['accepted'];

        $workLogNotification = $authUser->notifications()
            ->where('type', WorkLogNotification::class)
            ->where('data->status', 'created')
            ->where('data->work_log_id', $workLog->id)
            ->first();

        if ($workLogNotification) {
            $workLogNotification->markAsRead();
            $workLogNotification->update(['data->status' => 'accepted']);
            $workLog->user->notify(new DisputeStatusNotification($workLog->user, $workLog, $is_accepted ? 'accepted' : 'declined'));
        }

        if ($is_accepted) $workLog->accept();
        else $workLog->decline();

        return back()->with('success',  "Buchung erfolgreich " . ($is_accepted ? 'akzeptiert' : 'abgelehnt') . ".");
    }

    public function destroy(WorkLog $workLog)
    {
        Gate::authorize('delete', [WorkLog::class, $workLog->user]);

        if ($workLog->delete()) {
            $workLog->user->supervisor->notifications()
                ->where('type', WorkLogNotification::class)
                ->where('data->status', 'created')
                ->where('data->work_log_id', $workLog->id)
                ->delete();
        }

        return back()->with('success', 'Arbeitseintrag erfolgreich gelöscht.');
    }

    public function userWorkLogs(User $user)
    {
        Gate::authorize('viewShow', [WorkLog::class, $user]);

        return Inertia::render('WorkLog/UserWorkLogIndex', [
            'user' => $user->only('id', 'first_name', 'last_name'),
            'workLogs' => WorkLog::where('user_id', $user->id)
                ->whereNotNull('end')
                ->with([
                    'latestPatch:id,work_log_patches.work_log_id,status,start,end,is_home_office,comment',
                    'currentAcceptedPatch:id,work_log_patches.work_log_id,status,start,end,is_home_office,comment'
                ])
                ->orderBy('start', 'DESC')
                ->get(),
            'can' => [
                'workLogPatch' => [
                    'create' => Gate::allows('create', [WorkLogPatch::class, $user]),
                    'delete' => Gate::allows('delete', [WorkLogPatch::class, $user]),
                    'update' => Gate::allows('update', [WorkLogPatch::class, $user]),
                ],
                'workLog' => [
                    'delete' => Gate::allows('delete', [WorkLog::class, $user]),
                ]
            ]
        ]);
    }
}
