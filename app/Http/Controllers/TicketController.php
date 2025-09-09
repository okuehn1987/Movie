<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class TicketController extends Controller
{
    public function index()
    {
        Gate::authorize('viewIndex', Ticket::class);

        return Inertia::render('Ticket/TicketIndex', [
            'tickets' => Ticket::inOrganization()->with(['customer:id,name', 'user:id,first_name,last_name', 'assignee:id,first_name,last_name'])->get(),
        ]);
    }

    public function store() {}

    public function update() {}

    public function delete(Ticket $ticket)
    {
        $ticket->delete();

        return back()->with('success', 'Ticket erfolgreich gelöscht.');
    }
}
