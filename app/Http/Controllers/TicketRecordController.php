<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\Ticket;
use App\Models\TicketRecord;
use App\Models\User;
use App\Notifications\TicketRecordCreationNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
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
            'description' => 'required|string|max:400',
            'resources' => 'nullable|string|max:400',
        ]);

        $ticket->records()->create([
            ...$validated,
            'start' => Carbon::parse($validated['start']),
            'duration' => Carbon::parse($validated['duration'])->secondsSinceMidnight(),
            'user_id' => $authUser->id,
        ]);

        Organization::getCurrent()->users
            ->filter(fn($u) => !$authUser->is($u) && $u->can('update', $ticket))
            ->each
            ->notify(new TicketRecordCreationNotification($authUser, $ticket));

        return back()->with('success', 'Eintrag erfolgreich erstellt.');
    }

    public function update(Request $request, TicketRecord $ticketRecord, #[CurrentUser] User $authUser)
    {
        Gate::authorize('update', $ticketRecord);

        $validated = $request->validate([
            'start' => 'required|date',
            'duration' => 'required|date_format:H:i',
            'description' => 'required|string|max:400',
            'resources' => 'nullable|string|max:400',
        ]);

        $ticketRecord->update([
            ...$validated,
            'start' => Carbon::parse($validated['start']),
            'duration' => Carbon::parse($validated['duration'])->secondsSinceMidnight(),
        ]);

        Organization::getCurrent()->users
            ->filter(fn($u) => !$authUser->is($u) && $u->can('update', $ticketRecord->ticket))
            ->each
            ->notify(new TicketRecordCreationNotification($authUser, $ticketRecord->ticket));

        return back()->with('success', 'Eintrag erfolgreich bearbeitet.');
    }
}
