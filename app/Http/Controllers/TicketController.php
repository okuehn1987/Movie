<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Organization;
use App\Models\Ticket;
use App\Models\TicketRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class TicketController extends Controller
{
    public function index()
    {
        Gate::authorize('viewIndex', Ticket::class);
        return Inertia::render('Ticket/TicketIndex', [
            'tickets' => Ticket::inOrganization()->with(['customer:id,name', 'user:id,first_name,last_name', 'assignee:id,first_name,last_name', 'records.user'])->get(),
            'customers' => Customer::inOrganization()->get(['id', 'name']),
            'users' => User::inOrganization()->get(['id', 'first_name', 'last_name', 'job_role']),
        ]);
    }

    public function store(Request $request)
    {
        Gate::authorize('create', Ticket::class);
        $validated = $request->validate([
            'tab' => 'required|in:expressTicket,ticket',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:lowest,low,medium,high,highest',
            'customer_id' => ['required', Rule::exists('customers', 'id')->whereIn('id', Organization::getCurrent()->customers()->pluck('customers.id'))],
            'assignee_id' => ['required_if:tab,ticket', Rule::exists('users', 'id')->whereIn('id', Organization::getCurrent()->users()->pluck('users.id'))],
            'start' => 'nullable|required_if:tab,expressTicket|date',
            'duration' => 'nullable|required_if:tab,expressTicket|date_format:H:i',
            'resources' => 'nullable|string',
        ]);

        $ticket = Ticket::create(
            [
                ...collect($validated)->only(['title', 'description', 'priority', 'customer_id', 'assignee_id']),
                'user_id' => Auth::id(),
                'assigned_at' => now(),
            ]
        );

        if ($validated["tab"] === "expressTicket")
            $ticket->records()->create([
                'resources' => $validated['resources'],
                'start' => Carbon::parse($validated['start']),
                'duration' => Carbon::parse($validated['duration'])->hour * 3600 + Carbon::parse($validated['duration'])->minute * 60,
                'user_id' => Auth::id(),
            ]);

        return back()->with('success', 'Ticket erfolgreich erstellt.');
    }

    public function update(Request $request, Ticket $ticket)
    {

        Gate::authorize('update', [Ticket::class, $ticket->user]);
        // Organization::getCurrent()->tickets()->whereHas(records())
        $validated = $request->validate([
            'priority' => 'required|in:lowest,low,medium,high,highest',
            'assignee_id' => ['nullable', Rule::exists('users', 'id')->whereIn('id', Organization::getCurrent()->users()->pluck('users.id'))],
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
        $ticket->update(collect($validated)->except('selected')->toArray());

        return back()->with('success', 'Änderungen erfolgreich gespeichert.');
    }

    public function delete(Ticket $ticket)
    {
        $ticket->delete();

        return back()->with('success', 'Ticket erfolgreich gelöscht.');
    }
}
