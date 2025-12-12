<?php

namespace App\Http\Controllers;

use App\Enums\Status;
use App\Models\Absence;
use App\Models\TimeAccount;
use App\Models\TimeAccountTransaction;
use App\Models\User;
use App\Models\UserTrustWorkingHour;
use App\Models\WorkLog;
use App\Models\WorkLogPatch;
use App\Notifications\DisputeStatusNotification;
use App\Notifications\WorkLogNotification;
use App\Services\AppModuleService;
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
                    ->whereHas('workLogs')->with(['defaultTimeAccount:id,balance,user_id', 'latestWorkLog', 'currentWorkingHours:id,weekly_working_hours,user_working_hours.user_id', 'currentWorkingWeek'])
                    ->where(function ($query) {
                        $query->whereNull('resignation_date')
                            ->orWhere('resignation_date', '>=', Carbon::today());
                    })
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
                'accepted_at' => now(),
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
                'status' => Status::Accepted,
                'accepted_at' => now(),
            ]);
        }

        return back()->with('success', 'Arbeitsstatus erfolgreich eingetragen.');
    }

    public function createWorkLog(Request $request, User $user, #[CurrentUser] User $authUser)
    {
        Gate::authorize('create', [WorkLog::class, $authUser]);

        $validated = $request->validate([
            'start' => ['required', 'date', 'before:end', function ($attr, $value, $fail) use ($request, $user) {
                if (UserTrustWorkingHour::checkCollisions([
                    'start' => $value,
                    'end' => $request['end'],
                    'user_id' => $user->id
                ])) {
                    $fail('In dem Zeitraum besteht Vertrauensarbeit.');
                }
            }],
            'end' => ['required', 'date', 'before_or_equal:now', function ($attr, $value, $fail) use ($user) {
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
            'status' => Status::Created,
            'comment' => $validated['comment'],
            'user_id' => $user->id
        ]);

        $supervisor = $workLog->user->supervisor;
        $requiresApproval = $supervisor && $supervisor->id != $authUser->id;
        if (!$requiresApproval) $workLog->accept();
        else {
            collect($supervisor->loadMissing('isSubstitutedBy')->isSubstitutedBy)
                ->merge([$supervisor])
                ->unique('id')
                ->each
                ->notify(new WorkLogNotification($workLog->user, $workLog));
        }
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
            ->where('data->status', Status::Created)
            ->where('data->work_log_id', $workLog->id)
            ->first();

        if ($workLogNotification) {
            $workLogNotification->markAsRead();
            $workLogNotification->update(['data->status' => Status::Accepted]);
            $workLog->user->notify(new DisputeStatusNotification($workLog, $is_accepted ? Status::Accepted : Status::Declined));
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
                ->where('data->status', Status::Created)
                ->where('data->work_log_id', $workLog->id)
                ->delete();
        }

        return back()->with('success', 'Arbeitseintrag erfolgreich gelöscht.');
    }

    public function userWorkLogs(User $user)
    {
        Gate::authorize('viewShow', [WorkLog::class, $user]);

        $canTide = AppModuleService::hasAppModule('tide');

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
                    'viewShow' => Gate::allows('viewShow', [WorkLog::class, $user]),
                ],
                'absences' => [
                    'viewIndex' => Gate::allows('viewIndex', [Absence::class, $user]),
                ],
                'user' => [
                    'viewShow' => Gate::allows('viewShow', $user),
                    'viewIndex' => Gate::allows('viewIndex', User::class),
                ],
                'timeAccount' => [
                    'viewIndex' => $canTide && Gate::allows('viewIndex', [TimeAccount::class, $user]),
                ],
                'timeAccountTransaction' => [
                    'viewIndex' => $canTide &&  Gate::allows('viewIndex', [TimeAccountTransaction::class, $user]),
                ]
            ]
        ]);
    }
}
