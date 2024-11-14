<?php

namespace App\Http\Controllers;

use App\Models\WorkLog;
use App\Models\WorkLogPatch;
use App\Notifications\PatchNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;

class WorkLogPatchController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'start' => 'required|date',
            'end' => 'required|date',
            'is_home_office' => 'required|boolean',
            'workLog' => 'required|integer'
        ]);

        $workLog = WorkLog::find($validated['workLog']);

        $patch = WorkLogPatch::create([
            'is_home_office' => $validated['is_home_office'],
            'start' => Carbon::parse($validated['start']),
            'end' => Carbon::parse($validated['end']),
            'status' => 'created',
            'work_log_id' => $workLog->id,
            'user_id' => $workLog->user_id
        ]);

        $supervisor = $workLog->user->supervisor;
        if ($supervisor) $supervisor->notify(new PatchNotification($workLog->user, $patch));
        else $patch->update(['status' => 'accepted']);

        return back()->with('success',  'Korrektur der Arbeitszeit erfolgreich beantragt.');
    }

    public function update(Request $request, WorkLogPatch $workLogPatch)
    {
        $validated = $request->validate([
            'accepted' => 'required|boolean'
        ]);

        $workLogPatch->update(['status' => $validated['accepted'] ? 'accepted' : 'declined']);

        return back()->with('success',  "Zeitkorrektur erfolgreich " . ($validated['accepted'] ? 'akzeptiert' : 'abgelehnt') . ".");
    }

    public function destroy(WorkLogPatch $workLogPatch)
    {
        $workLogPatch->delete();

        return back()->with('success',  "Antrag auf Zeitkorrektur erfolgreich zurückgezogen.");
    }
}
