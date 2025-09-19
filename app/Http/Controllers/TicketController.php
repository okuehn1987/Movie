<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Organization;
use App\Models\Ticket;
use App\Models\TicketRecord;
use App\Models\User;
use App\Notifications\TicketCreationNotification;
use App\Notifications\TicketDeletionNotification;
use App\Notifications\TicketFinishNotification;
use App\Notifications\TicketUpdateNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Illuminate\Container\Attributes\CurrentUser;

class TicketController extends Controller
{
    public function index()
    {
        Gate::authorize('viewIndex', Ticket::class);

        $ticketQuery = Ticket::inOrganization()->with(['customer:id,name', 'user:id,first_name,last_name', 'assignees:id,first_name,last_name', 'records.user']);

        return Inertia::render('Ticket/TicketIndex', [
            'tickets' => (clone $ticketQuery)
                ->whereNull('tickets.finished_at')
                ->orWhereHas('records', fn($q) => $q->whereNull('accounted_at'))
                ->get(),
            'archiveTickets' => (clone $ticketQuery)
                ->whereNotNull('tickets.finished_at')
                ->whereDoesntHave('records', fn($q) => $q->whereNull('accounted_at'))
                ->get(),
            'customers' => Customer::inOrganization()->get(['id', 'name']),
            'users' => User::inOrganization()->get(['id', 'first_name', 'last_name', 'job_role']),
        ]);
    }

    public function store(Request $request, #[CurrentUser] User $authUser)
    {
        Gate::authorize('create', Ticket::class);

        $validated = $request->validate([
            'tab' => 'required|in:expressTicket,ticket',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:lowest,low,medium,high,highest',
            'customer_id' => ['required', Rule::exists('customers', 'id')->whereIn('id', Organization::getCurrent()->customers()->select('customers.id'))],
            'assignees' => 'present|array',
            'assignees.*' => ['required_if:tab,ticket', Rule::exists('users', 'id')->whereIn('id', Organization::getCurrent()->users()->select('users.id'))],
            'start' => 'nullable|required_if:tab,expressTicket|date',
            'duration' => 'nullable|required_if:tab,expressTicket|date_format:H:i',
            'resources' => 'nullable|string',
        ],);

        $ticket = Ticket::create(
            [
                ...collect($validated)->only(['title', 'description', 'priority', 'customer_id', 'assignee_id']),
                'user_id' => Auth::id(),
            ]
        );

        $ticket->assignees()->attach($validated['assignees']);

        if ($validated["tab"] === "expressTicket") {
            $ticket->records()->create([
                'resources' => $validated['resources'],
                'start' => Carbon::parse($validated['start']),
                'duration' => Carbon::parse($validated['duration'])->hour * 3600 + Carbon::parse($validated['duration'])->minute * 60,
                'user_id' => Auth::id(),
            ]);
            $ticket->update(['finished_at' => now()]);
        }
        $ticket->assignees->each->notify(new TicketCreationNotification($authUser, $ticket));

        return back()->with('success', 'Ticket erfolgreich erstellt.');
    }

    public function update(Request $request, Ticket $ticket, #[CurrentUser] User $authUser)
    {
        Gate::authorize('update', [Ticket::class, $ticket->user]);

        $validated = $request->validate([
            'priority' => 'required|in:lowest,low,medium,high,highest',
            'assignees' => 'present|array',
            'assignees.*' => ['required_if:tab,ticket', Rule::exists('users', 'id')->whereIn('id', Organization::getCurrent()->users()->select('users.id'))],
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'selected' => 'present|array',
            'selected.*' => [
                'required',
                Rule::exists('ticket_records', 'id')
                    ->whereIn('id', TicketRecord::whereHas('ticket', fn($q) => $q->whereIn('id', Organization::getCurrent()->tickets()->pluck('tickets.id')))->pluck('id'))
            ]
        ]);
        $ticket->records()->whereNotIn('id', $validated['selected'])->update(['accounted_at' => null]);
        $ticket->records()->whereIn('id', $validated['selected'])->update(['accounted_at' => now()]);
        $ticket->update(collect($validated)->except(['selected', 'assignees'])->toArray());
        $ticket->assignees()->sync($validated['assignees']);

        $ticket->assignees->each->notify(new TicketUpdateNotification($authUser, $ticket));

        return back()->with('success', 'Änderungen erfolgreich gespeichert.');
    }

    public function finish(Ticket $ticket, #[CurrentUser] User $authUser)
    {
        Gate::authorize('update', [Ticket::class, $ticket->user]);

        $ticket->update(['finished_at' => now()]);

        // TODO: die richtigen Leute notifyen (Britta aka abrechnende Person)
        $authUser->supervisor?->notify(new TicketFinishNotification($authUser, $ticket));

        return back()->with('success', 'Ticket erfolgreich abgeschlossen.');
    }

    public function destroy(Ticket $ticket, #[CurrentUser] User $authUser)
    {
        Gate::authorize('delete', [Ticket::class, $ticket->user]);

        $ticket->assignees->each->notify(new TicketDeletionNotification($authUser, $ticket));

        $ticket->delete();

        return back()->with('success', 'Ticket erfolgreich gelöscht.');
    }
}
