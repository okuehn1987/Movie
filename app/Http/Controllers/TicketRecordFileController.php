<?php

namespace App\Http\Controllers;

use App\Models\TicketRecordFile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class TicketRecordFileController extends Controller
{
    public function show(TicketRecordFile $ticketRecordFile)
    {
        Gate::authorize('publicAuth', User::class);

        return Inertia::render('FileShow', [
            'file' => [
                'id' => $ticketRecordFile->id,
                'name' => $ticketRecordFile->original_name,
                'type' => 'ticketRecordFile',
            ],
            'backurl' => route('customer.show', ['customer' => $ticketRecordFile->ticketRecord->ticket->customer_id, 'openTicket' => $ticketRecordFile->ticketRecord->ticket->id]),
        ]);
    }
    public function getContent(TicketRecordFile $ticketRecordFile)
    {
        Gate::authorize('publicAuth', User::class);

        return response()->file(Storage::disk('ticket_record_files')->path($ticketRecordFile->path));
    }

    public function destroy(TicketRecordFile $ticketRecordFile)
    {
        Gate::authorize('publicAuth', User::class);

        if (Storage::disk('ticket_record_files')->exists($ticketRecordFile->path)) {
            Storage::disk('ticket_record_files')->delete($ticketRecordFile->path);
        }

        $ticketRecordFile->delete();

        return back()->with('success', 'Datei erfolgreich gelöscht.');
    }
}
