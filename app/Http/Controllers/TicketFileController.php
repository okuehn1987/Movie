<?php

namespace App\Http\Controllers;

use App\Models\TicketFile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class TicketFileController extends Controller
{
    public function show(TicketFile $ticketFile)
    {
        Gate::authorize('publicAuth', User::class);

        return Inertia::render('FileShow', [
            'file' => [
                'id' => $ticketFile->id,
                'name' => $ticketFile->original_name,
                'type' => 'ticketFile',
            ],
            'backurl' => route('customer.show', ['customer' => $ticketFile->ticket->customer_id, 'openTicket' => $ticketFile->ticket_id]),
        ]);
    }
    public function getContent(TicketFile $ticketFile)
    {
        Gate::authorize('publicAuth', User::class);

        return response()->file(Storage::disk('ticket_files')->path($ticketFile->path));
    }

    public function destroy(TicketFile $ticketFile)
    {
        Gate::authorize('publicAuth', User::class);

        if (Storage::disk('ticket_files')->exists($ticketFile->path)) {
            Storage::disk('ticket_files')->delete($ticketFile->path);
        }

        $ticketFile->delete();

        return back()->with('success', 'Datei erfolgreich gelöscht.');
    }
}
