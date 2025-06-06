<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\WorkLog;
use App\Models\WorkLogPatch;
use App\Notifications\PatchNotification;
use App\Notifications\WorkLogPatchNotification;
use Carbon\Carbon;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class WorkLogPatchController extends Controller
{
    public function store(Request $request, #[CurrentUser] User $authUser)
    {
        Gate::authorize('create', [WorkLogPatch::class, WorkLog::with('user')->find($request['workLog'])->user]);

        $validated = $request->validate([
            'start' => 'required|date',
            'end' => 'required|date',
            'comment' => 'nullable|string',
            'is_home_office' => 'required|boolean',
            'workLog' => 'required|exists:work_logs,id'
        ]);

        $workLog = WorkLog::find($validated['workLog']);
        $user = $workLog->user;

        $patch = WorkLogPatch::create([
            'is_home_office' => $validated['is_home_office'],
            'start' => Carbon::parse($validated['start']),
            'end' => Carbon::parse($validated['end']),
            'status' => 'created',
            'comment' => $validated['comment'],
            'work_log_id' => $workLog->id,
            'user_id' => $user->id
        ]);

        $supervisor = $workLog->user->supervisor;
        if (!$supervisor || $supervisor->is($authUser)) $patch->accept();
        else $supervisor->notify(new WorkLogPatchNotification($workLog->user, $patch));

        return back()->with('success',  'Korrektur der Arbeitszeit erfolgreich beantragt.');
    }

    public function update(Request $request, WorkLogPatch $workLogPatch, #[CurrentUser] User $authUser)
    {
        Gate::authorize('update', [WorkLogPatch::class, $workLogPatch->user]);

        $is_accepted = $request->validate([
            'accepted' => 'required|boolean'
        ])['accepted'];

        $patchNotification = $authUser->unreadNotifications()
            ->where('data->work_log_patch_id', $workLogPatch->id)->first();

        if ($patchNotification) $patchNotification->markAsRead();

        if ($is_accepted) $workLogPatch->accept();
        else $workLogPatch->decline();

        return back()->with('success',  "Zeitkorrektur erfolgreich " . ($is_accepted ? 'akzeptiert' : 'abgelehnt') . ".");
    }

    public function destroy(WorkLogPatch $workLogPatch)
    {
        Gate::authorize('delete', [WorkLogPatch::class, $workLogPatch->user]);

        $workLogPatch->delete();

        return back()->with('success',  "Antrag auf Zeitkorrektur erfolgreich zurückgezogen.");
    }
}
