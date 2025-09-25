<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketRecord;
use App\Models\User;
use App\Notifications\TicketRecordCreationNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Support\Facades\Gate;

class TicketRecordController extends Controller
{
    public function store(Request $request, Ticket $ticket, #[CurrentUser] User $authUser)
    {
        Gate::authorize('publicAuth', User::class);

        $validated = $request->validate([
            'start' => 'required|date',
            'duration' => 'required|date_format:H:i',
            'description' => 'required|string',
            'resources' => 'nullable|string',
        ]);

        $ticket->records()->create([
            ...$validated,
            'start' => Carbon::parse($validated['start']),
            'duration' => Carbon::parse($validated['duration'])->hour * 3600 + Carbon::parse($validated['duration'])->minute * 60,
            'user_id' => Auth::id(),
        ]);

        // TODO: die richtigen Leute notifyen  (Britta aka abrechnende Person)
        $authUser->supervisor?->notify(new TicketRecordCreationNotification($authUser, $ticket->records));

        return back()->with('success', 'Eintrag erfolgreich erstellt.');
    }

    public function update(Request $request, TicketRecord $ticketRecord, #[CurrentUser] User $authUser)
    {
        Gate::authorize('update', $ticketRecord);

        $validated = $request->validate([
            'start' => 'required|date',
            'duration' => 'required|date_format:H:i',
            'description' => 'required|string',
            'resources' => 'nullable|string',
        ]);

        $ticketRecord->update([
            ...$validated,
            'start' => Carbon::parse($validated['start']),
            'duration' => Carbon::parse($validated['duration'])->hour * 3600 + Carbon::parse($validated['duration'])->minute * 60,
        ]);

        // TODO: die richtigen Leute notifyen  (Britta aka abrechnende Person)
        $authUser->supervisor?->notify(new TicketRecordCreationNotification($authUser, $ticketRecord));

        return back()->with('success', 'Eintrag erfolgreich bearbeitet.');
    }
}
