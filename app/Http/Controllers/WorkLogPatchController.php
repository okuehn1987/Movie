<?php

namespace App\Http\Controllers;

use App\Enums\Status;
use App\Models\User;
use App\Models\UserTrustWorkingHour;
use App\Models\WorkLog;
use App\Models\WorkLogPatch;
use App\Notifications\DisputeStatusNotification;
use App\Notifications\WorkLogPatchNotification;
use Carbon\Carbon;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class WorkLogPatchController extends Controller
{
    public function store(Request $request, WorkLog $workLog, #[CurrentUser] User $authUser)
    {
        Gate::authorize('create', [WorkLogPatch::class, $workLog->user]);

        $validated = $request->validate([
            'start' => ['required', 'date', 'before:end', function ($attr, $value, $fail) use ($request) {
                if (UserTrustWorkingHour::checkCollisions([
                    'start' => $value,
                    'end' => $request['end'],
                    'user_id' => WorkLog::find($request['workLog'])->user_id
                ])) {
                    $fail('Der angegebene Zeitraum kollidiert mit Vertrauensarbeitszeiteinträgen.');
                }
            }],
            'end' => 'required|date|before_or_equal:now',
            'comment' => 'nullable|string',
            'is_home_office' => 'required|boolean',
        ]);

        $user = $workLog->user;

        $patch = WorkLogPatch::create([
            'is_home_office' => $validated['is_home_office'],
            'start' => Carbon::parse($validated['start']),
            'end' => Carbon::parse($validated['end']),
            'status' => Status::Created,
            'comment' => $validated['comment'],
            'work_log_id' => $workLog->id,
            'user_id' => $user->id
        ]);

        $supervisor = $workLog->user->supervisor;
        $requiresApproval = $supervisor && !$authUser->can('update', [WorkLogPatch::class, $user]);
        if (!$requiresApproval) $patch->accept();
        else {
            collect($supervisor->loadMissing('isSubstitutedBy')->isSubstitutedBy)
                ->merge([$supervisor])
                ->unique('id')
                ->each
                ->notify(new WorkLogPatchNotification($workLog->user, $patch));
        }

        return back()->with('success',  "Korrektur der Arbeitszeit erfolgreich " . ($requiresApproval ? 'beantragt' : 'gespeichert') . ".");
    }

    public function update(Request $request, WorkLogPatch $workLogPatch, #[CurrentUser] User $authUser)
    {
        Gate::authorize('update', [WorkLogPatch::class, $workLogPatch->user]);

        $is_accepted = $request->validate([
            'accepted' => 'required|boolean'
        ])['accepted'];

        $patchNotification = $authUser->notifications()
            ->where('type', WorkLogPatchNotification::class)
            ->where('data->status', Status::Created)
            ->where('data->work_log_patch_id', $workLogPatch->id)
            ->first();

        if ($patchNotification) {
            $patchNotification->markAsRead();
            $patchNotification->update(['data->status' => Status::Accepted]);
            $workLogPatch->user->notify(new DisputeStatusNotification($workLogPatch, $is_accepted ? Status::Accepted : Status::Declined));
        }

        if ($is_accepted) $workLogPatch->accept();
        else $workLogPatch->decline();

        return back()->with('success',  "Zeitkorrektur erfolgreich " . ($is_accepted ? 'akzeptiert' : 'abgelehnt') . ".");
    }

    public function destroy(WorkLogPatch $workLogPatch, #[CurrentUser] User $authUser)
    {
        Gate::authorize('delete', [WorkLogPatch::class, $workLogPatch->user]);

        if ($workLogPatch->delete()) {
            $authUser->notifications()
                ->where('type', WorkLogPatchNotification::class)
                ->where('data->status', Status::Created)
                ->where('data->work_log_patch_id', $workLogPatch->id)
                ->delete();
        }

        return back()->with('success',  "Antrag auf Zeitkorrektur erfolgreich zurückgezogen.");
    }
}
