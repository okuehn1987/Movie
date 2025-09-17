<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketRecordController extends Controller
{
    public function store(Request $request, Ticket $ticket)
    {
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

        return back()->with('success', 'Eintrag erfolgreich erstellt.');
    }
}
