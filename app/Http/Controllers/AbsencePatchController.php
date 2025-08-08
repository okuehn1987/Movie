<?php

namespace App\Http\Controllers;

use App\Models\Absence;
use App\Models\AbsencePatch;
use App\Models\AbsenceType;
use App\Models\User;
use App\Notifications\AbsencePatchNotification;
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
            'start' => 'required|date',
            'end' => 'required|date|after_or_equal:start',
            'absence_type_id' => ['required', Rule::in(AbsenceType::inOrganization()->get()->pluck('id'))],
            'comment' => 'nullable|string'
        ]);

        $requires_approval = $authUser->supervisor_id && AbsenceType::find($validated['absence_type_id'])->requires_approval;

        $absencePatch = AbsencePatch::create([
            ...$validated,
            'user_id' => $absence->user_id,
            'absence_id' => $absence->id,
            'status' => 'created',
        ]);

        if ($requires_approval) $authUser->supervisor->notify(new AbsencePatchNotification($authUser, $absencePatch));
        else $absencePatch->accept();

        return back()->with('success', 'Korrektur erfolgreich ' . ($requires_approval ? 'beantragt' : 'eingetragen'));
    }

    public function updateStatus(Request $request, AbsencePatch $absencePatch, #[CurrentUser] User $authUser)
    {
        Gate::authorize('update', $absencePatch);

        $is_accepted = $request->validate([
            'accepted' => 'required|boolean'
        ])['accepted'];

        $absencePatchNotification = $authUser->unreadNotifications()
            ->where('data->absence_patch_id', $absencePatch->id)
            ->first();

        if ($absencePatchNotification) $absencePatchNotification->markAsRead();

        if ($is_accepted) $absencePatch->accept();
        else $absencePatch->decline();

        return back()->with('success',  "Abwesenheitkorrektur erfolgreich " . ($is_accepted ? 'akzeptiert' : 'abgelehnt') . ".");
    }

    public function destroy(AbsencePatch $absencePatch, #[CurrentUser] User $authUser)
    {
        Gate::authorize('delete', $absencePatch);



        if ($absencePatch->delete()) {
            $patchNotification = $authUser->unreadNotifications()
                ->where('data->absence_patch_id', $absencePatch->id)->first();

            if ($patchNotification) $patchNotification->markAsRead();
        }

        return back()->with('success', 'Korrekturantrag erfolgreich zurückgezogen');
    }
}
