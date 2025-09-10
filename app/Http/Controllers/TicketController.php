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
            'tickets' => Ticket::inOrganization()->with(['customer:id,name', 'user:id,first_name,last_name', 'assignee:id,first_name,last_name', 'records'])->get(),
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
            'duration' => 'required_if:tab,expressTicket|integer|min:0',
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
                'duration' => $validated['duration'] * 3600,
            ]);

        return back()->with('success', 'Ticket erfolgreich erstellt.');
    }

    public function update() {}

    public function delete(Ticket $ticket)
    {
        $ticket->delete();

        return back()->with('success', 'Ticket erfolgreich gelöscht.');
    }
}
