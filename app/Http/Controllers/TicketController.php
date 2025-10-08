<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Organization;
use App\Models\Ticket;
use App\Models\TicketRecord;
use App\Models\User;
use App\Notifications\RemovedFromTicketNotification;
use App\Notifications\TicketCreationNotification;
use App\Notifications\TicketDeletionNotification;
use App\Notifications\TicketFinishNotification;
use App\Notifications\TicketUpdateNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Illuminate\Container\Attributes\CurrentUser;

class TicketController extends Controller
{
    public function index()
    {
        Gate::authorize('publicAuth', User::class);

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
        Gate::authorize('publicAuth', User::class);

        $validated = $request->validate([
            'tab' => 'required|in:expressTicket,ticket',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:400',
            'priority' => 'required|in:lowest,low,medium,high,highest',
            'customer_id' => ['required', Rule::exists('customers', 'id')->whereIn('id', Organization::getCurrent()->customers()->select('customers.id'))],
            'assignees' => 'present|array',
            'assignees.*' => ['required_if:tab,ticket', Rule::exists('users', 'id')->whereIn('id', Organization::getCurrent()->users()->select('users.id'))],
            'start' => 'nullable|required_if:tab,expressTicket|date',
            'duration' => 'nullable|required_if:tab,expressTicket|date_format:H:i',
            'resources' => 'nullable|string|max:400',
        ]);

        $customer = Customer::inOrganization()->find($validated['customer_id']);

        $ticket = Ticket::create([
            ...collect($validated)->only(['title', 'description', 'priority', 'customer_id']),
            'user_id' => $authUser->id,
            'reference_prefix' => strtoupper(substr($customer->name, 0, 3)),
        ]);

        $ticket->assignees()->attach($validated['assignees']);

        if ($validated["tab"] === "expressTicket") {
            $ticket->records()->create([
                'resources' => $validated['resources'],
                'start' => Carbon::parse($validated['start']),
                'duration' => Carbon::parse($validated['duration'])->hour * 3600 + Carbon::parse($validated['duration'])->minute * 60,
                'user_id' => $authUser->id,
            ]);
            $ticket->update(['finished_at' => now()]);
        }
        $ticket->assignees->filter(fn($u) => !$authUser->is($u))->each->notify(new TicketCreationNotification($authUser, $ticket));

        return back()->with('success', 'Ticket erfolgreich erstellt.');
    }

    public function update(Request $request, Ticket $ticket, #[CurrentUser] User $authUser)
    {
        Gate::authorize('update', $ticket);

        $validated = $request->validate([
            'priority' => 'required|in:lowest,low,medium,high,highest',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:400',
            'assignees' => 'present|array',
            'assignees.*' => ['required_if:tab,ticket', Rule::exists('users', 'id')->whereIn('id', Organization::getCurrent()->users()->select('users.id'))],
            'selected' => 'present|array',
            'selected.*' => [
                'required',
                Rule::exists('ticket_records', 'id')
                    ->whereIn('id', TicketRecord::whereHas('ticket', fn($q) => $q->whereIn('id', Organization::getCurrent()->tickets()->select('tickets.id')))->pluck('id'))
            ]
        ]);

        $notifyAssignees = (
            $ticket->title != $validated['title'] ||
            $ticket->description != $validated['description'] ||
            $ticket->priority != $validated['priority'] ||
            collect($validated["assignees"])->diff($ticket->assignees->pluck('id'))->isNotEmpty() ||
            $ticket->assignees->pluck('id')->diff($validated["assignees"])->isNotEmpty()
        );

        $ticket->assignees
            ->filter(fn($a) => collect($validated["assignees"])->doesntContain($a->id))
            ->each
            ->notify(new RemovedFromTicketNotification($ticket));

        $ticket->records()->whereNotNull('accounted_at')->whereNotIn('id', $validated['selected'])->update(['accounted_at' => null]);
        $ticket->records()->whereNull('accounted_at')->whereIn('id', $validated['selected'])->update(['accounted_at' => now()]);
        $ticket->update(collect($validated)->except(['selected', 'assignees'])->toArray());
        $ticket->assignees()->sync($validated['assignees']);

        if ($notifyAssignees)
            $ticket->fresh('assignees')->assignees->filter(fn($u) => !$authUser->is($u))->each->notify(new TicketUpdateNotification($authUser, $ticket));

        return back()->with('success', 'Änderungen erfolgreich gespeichert.');
    }

    public function finish(Ticket $ticket, #[CurrentUser] User $authUser)
    {
        Gate::authorize('update', $ticket);

        $ticket->update(['finished_at' => now()]);

        Organization::getCurrent()->users
            ->filter(fn($u) => !$authUser->is($u) && $u->can('update', $ticket))
            ->each
            ->notify(new TicketFinishNotification($authUser, $ticket));

        return back()->with('success', 'Ticket erfolgreich abgeschlossen.');
    }

    public function destroy(Ticket $ticket, #[CurrentUser] User $authUser)
    {
        Gate::authorize('delete',  $ticket);

        $ticket->assignees->each->notify(new TicketDeletionNotification($authUser, $ticket));

        $ticket->delete();

        return back()->with('success', 'Ticket erfolgreich gelöscht.');
    }
}
