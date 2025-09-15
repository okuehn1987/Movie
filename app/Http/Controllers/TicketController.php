<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
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
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:lowest,low,medium,high,highest',
            'customer_id' => 'required|exists:customers,id',
            'assignee_id' => 'required_if:tab,ticket|exists:users,id',
            'start' => 'required_if:tab,expressTicket|date',
            'duration' => 'required_if:tab,expressTicket|date_format:H:i',
            'resources' => 'nullable|string',
        ]);

        $RECORD_KEYS = ['start', 'duration', 'resources'];

        $ticket = Ticket::create(
            [
                ...collect($validated)->except($RECORD_KEYS),
                'user_id' => Auth::id(),
                'assigned_at' => now(),
            ]
        );

        if ($request["tab"] === "expressTicket")
            $ticket->records()->create([
                ...collect($validated)->only($RECORD_KEYS),
                'start' => Carbon::parse($validated['start']),
                'duration' => Carbon::parse($validated['duration'])->hour * 3600 + Carbon::parse($validated['duration'])->minute * 60,
                'user_id' => Auth::id(),
            ]);

        return back()->with('success', 'Ticket erfolgreich erstellt.');
    }

    public function update(Request $request, Ticket $ticket)
    {

        Gate::authorize('update', [Ticket::class, $ticket->user]);

        $validated = $request->validate([
            'priority' => 'required|in:lowest,low,medium,high,highest',
            'assignee_id' => 'nullable|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $ticket->update($validated);

        return back()->with('success', 'Änderungen erfolgreich gespeichert.');
    }

    public function delete(Ticket $ticket)
    {
        $ticket->delete();

        return back()->with('success', 'Ticket erfolgreich gelöscht.');
    }
}
