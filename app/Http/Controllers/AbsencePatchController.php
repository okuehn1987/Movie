<?php

namespace App\Http\Controllers;

use App\Models\Absence;
use App\Models\AbsencePatch;
use App\Models\AbsenceType;
use App\Enums\Status;
use App\Models\User;
use App\Notifications\AbsencePatchNotification;
use App\Notifications\DisputeStatusNotification;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class AbsencePatchController extends Controller
{
    public function store(Request $request, Absence $absence, #[CurrentUser] User $authUser)
    {
        Gate::authorize('create', [AbsencePatch::class, $absence->user]);

        $validated = $request->validate([
            'start' => ['required', 'date', function ($attr, $val, $fail) use ($absence, $request) {
                if (
                    AbsencePatch::getCurrentEntries($absence->user)
                    ->where('start', '<=', $request['end'])
                    ->where('end', '>=', $request['start'])
                    ->count() > 0
                ) $fail('In diesem Zeitraum besteht bereits eine Abwesenheit.');
            }],
            'end' => 'required|date|after_or_equal:start',
            'absence_type_id' => ['required', Rule::in(AbsenceType::inOrganization()->get()->pluck('id'))],
            'comment' => 'nullable|string'
        ]);

        $requires_approval = ($authUser->supervisor_id &&
            AbsenceType::find($validated['absence_type_id'])->requires_approval);

        $absencePatch = AbsencePatch::create([
            ...$validated,
            'user_id' => $absence->user_id,
            'absence_id' => $absence->id,
            'status' => Status::Created,
            'type' => 'patch'
        ]);

        if ($authUser->id !== $absencePatch->user_id) {
            $absence->user->notify(new DisputeStatusNotification($absencePatch, Status::Created));
        }
        if ($requires_approval) {
            collect($authUser->supervisor->isSubstitutedBy)
                ->merge([$authUser->supervisor])
                ->unique('id')
                ->eachr->notify(new AbsencePatchNotification($authUser, $absencePatch));
        } else $absencePatch->accept();

        return back()->with('success', 'Korrektur erfolgreich ' . ($requires_approval ? 'beantragt' : 'eingetragen'));
    }

    public function updateStatus(Request $request, AbsencePatch $absencePatch, #[CurrentUser] User $authUser)
    {
        Gate::authorize('update', $absencePatch);

        $is_accepted = $request->validate([
            'accepted' => 'required|boolean'
        ])['accepted'];

        if (
            $is_accepted &&
            AbsencePatch::getCurrentEntries($absencePatch->user)
            ->where('start', '<=', $absencePatch->end)
            ->where('end', '>=', $absencePatch->start)
            ->count() > 0
        ) return back()->with('error', 'In diesem Zeitraum besteht bereits eine Abwesenheit.');

        $absencePatchNotification = $authUser->notifications()
            ->where('type', AbsencePatchNotification::class)
            ->where('data->status', Status::Created)
            ->where('data->absence_patch_id', $absencePatch->id)
            ->first();

        if ($absencePatchNotification) {
            $absencePatchNotification->markAsRead();
            $absencePatchNotification->update(['data->status' => $is_accepted ? Status::Accepted : Status::Declined]);
            $absencePatch->user->notify(new DisputeStatusNotification($absencePatch, $is_accepted ? Status::Accepted : Status::Declined));
        }

        if ($is_accepted) $absencePatch->accept();
        else $absencePatch->decline();

        return back()->with('success',  "Abwesenheitkorrektur erfolgreich " . ($is_accepted ? 'akzeptiert' : 'abgelehnt') . ".");
    }

    public function destroy(AbsencePatch $absencePatch, #[CurrentUser] User $authUser)
    {
        Gate::authorize('delete', $absencePatch);

        if ($absencePatch->delete()) {
            $authUser->notifications()
                ->where('type', AbsencePatchNotification::class)
                ->where('data->status', Status::Created)
                ->where('data->absence_patch_id', $absencePatch->id)
                ->delete();
        }

        return back()->with('success', 'Korrekturantrag erfolgreich zurückgezogen');
    }
}
