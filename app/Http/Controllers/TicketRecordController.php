<?php

namespace App\Http\Controllers;

use App\Models\CustomerOperatingSite;
use App\Models\OperatingSite;
use App\Models\Organization;
use App\Models\Ticket;
use App\Models\TicketRecord;
use App\Models\TicketRecordFile;
use App\Models\User;
use App\Notifications\TicketRecordCreationNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class TicketRecordController extends Controller
{
    public function store(Request $request, Ticket $ticket, #[CurrentUser] User $authUser)
    {
        Gate::authorize('publicAuth', User::class);

        $validated = $request->validate([
            'start' => 'required|date',
            'operatingSite' => ['required', 'array', function ($attr, $value, $fail) {
                if (!array_key_exists('type', $value) || !array_key_exists('id', $value)) $fail('böse');
                $type = $value['type'];
                if (!in_array($type, [OperatingSite::class, CustomerOperatingSite::class, User::class])) {
                    $fail('Bitte gib einen gültigen Standort ein');
                };

                $operatingSite = $type::inOrganization()->exists($value['id']);
                if (!$operatingSite) $fail('Bitte gib einen gültigen Standort ein');
            }],
            'duration' => 'required|date_format:H:i',
            'description' => 'required|string|max:400',
            'resources' => 'nullable|string|max:400',
            'files' => 'present|array',
            'files.*' => 'required|file|mimes:jpg,png,jpeg,avif,tiff,svg+xml,pdf|max:5120',
        ], [
            'files.*' => 'Die Dateien müssen im Format JPG, PNG, JPEG, AVIF, TIFF, SVG oder PDF vorliegen.',
        ]);

        $address = $validated['operatingSite']['type']::inOrganization()->find($validated['operatingSite']['id'])->currentAddress;

        $record = $ticket->records()->create([
            ...collect($validated)->except(['files', 'operatingSite']),
            'start' => Carbon::parse($validated['start']),
            'duration' => Carbon::parse($validated['duration'])->secondsSinceMidnight(),
            'user_id' => $authUser->id,
            'address_id' => $address->id,
        ]);

        foreach ($validated['files'] as $file) {
            $path = $file ? Storage::disk('ticket_record_files')->putFile($file) : null;
            $record->files()->create([
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
            ]);
        }

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
            'files' => 'present|array',
            'files.*' => 'required|file|mimes:jpg,png,jpeg,avif,tiff,svg+xml,pdf|max:5120',
        ], [
            'files.*' => 'Die Dateien müssen im Format JPG, PNG, JPEG, AVIF, TIFF, SVG oder PDF vorliegen.',
        ]);

        $ticketRecord->update([
            ...collect($validated)->except('files'),
            'start' => Carbon::parse($validated['start']),
            'duration' => Carbon::parse($validated['duration'])->secondsSinceMidnight(),
        ]);

        foreach ($validated['files'] as $file) {
            $path = $file ? Storage::disk('ticket_record_files')->putFile($file) : null;
            $ticketRecord->files()->create([
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
            ]);
        }

        Organization::getCurrent()->users
            ->filter(fn($u) => !$authUser->is($u) && $u->can('update', $ticketRecord->ticket))
            ->each
            ->notify(new TicketRecordCreationNotification($authUser, $ticketRecord->ticket));

        return back()->with('success', 'Eintrag erfolgreich bearbeitet.');
    }

    public function download(TicketRecordFile $ticketRecordFile)
    {
        Gate::authorize('publicAuth', User::class);

        return response()->file(Storage::disk('ticket_record_files')->path($ticketRecordFile->path));
    }

    public function deleteFile(TicketRecordFile $ticketRecordFile)
    {
        Gate::authorize('publicAuth', User::class);

        if (Storage::disk('ticket_record_files')->exists($ticketRecordFile->path)) {
            Storage::disk('ticket_record_files')->delete($ticketRecordFile->path);
        }

        $ticketRecordFile->delete();

        return back()->with('success', 'Datei erfolgreich gelöscht.');
    }
}
