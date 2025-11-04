<?php

namespace App\Http\Controllers;

use App\Enums\Status;
use App\Models\Customer;
use App\Models\CustomerOperatingSite;
use App\Models\OperatingSite;
use App\Models\Organization;
use App\Models\Ticket;
use App\Models\TicketRecord;
use App\Models\TicketRecordFile;
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
use Illuminate\Support\Facades\Storage;

class TicketController extends Controller
{
    public function index(Request $request, #[CurrentUser] User $authUser)
    {
        Gate::authorize('viewIndex', Ticket::class);

        $validated = $request->validate([
            'tab' => 'nullable|string|in:archive,finishedTickets,newTickets,workingTickets',
            'customer_id' => 'nullable|exists:customers,id',
            'assignees' => 'nullable|array',
            'assignees.*' => ['nullable', Rule::exists('users', 'id')->whereIn('id', Organization::getCurrent()->users()->select('users.id'))],
            'start' => 'nullable|date|required_with:end',
            'end' => 'nullable|date|required_with:start|after_or_equal:start',
            'openTicket' => ['nullable', Rule::exists('tickets', 'id')->whereIn('id', Ticket::inOrganization()->select('tickets.id'))],
        ]);

        $openTicket = array_key_exists('openTicket', $validated) && $validated['openTicket'] != null ? Ticket::find($validated['openTicket']) : null;

        $tab = match (true) {
            $openTicket?->finished_at != null => $validated['tab'],
            $openTicket != null => 'workingTickets',
            default => array_key_exists('tab', $validated) && $validated['tab'] ? $validated['tab'] : 'newTickets',
        };


        $ticketQuery = Ticket::inOrganization()->with([
            'customer:id,name',
            'user:id,first_name,last_name',
            'assignees:id,first_name,last_name',
            'records.user',
            'records.files'
        ]);
        return Inertia::render('Ticket/TicketIndex', [
            'tickets' => fn() => (clone $ticketQuery)
                ->whereNull('tickets.finished_at')
                ->orWhereHas('records', fn($q) => $q->whereNull('accounted_at'))
                ->get()
                ->map(fn($t) => [
                    ...$t->toArray(),
                    'can' => [
                        'ticket' => [
                            'update' => Gate::allows('update', $t),
                            'account' => Gate::allows('account', $t),
                            'delete' => Gate::allows('update', $t),
                        ],
                    ],
                    'records' => $t->records->map(
                        fn($ticketRecord) => [
                            ...$ticketRecord->toArray(),
                            'can' => [
                                'ticketRecord' => [
                                    'update' => Gate::allows('update', $ticketRecord),
                                ],
                            ],
                        ]
                    )
                ]),
            'archiveTickets' => fn() => (clone $ticketQuery)
                ->whereNotNull('tickets.finished_at')
                ->whereDoesntHave('records', fn($q) => $q->whereNull('accounted_at'))
                ->when(
                    array_key_exists('customer_id', $validated) && $validated['customer_id'] != null,
                    fn($q) => $q->where('customer_id', $validated['customer_id'])
                )
                ->when(
                    array_key_exists('assignees', $validated) && $validated['assignees'] != null,
                    fn($q) => $q->whereHas('assignees', fn($q2) => $q2->wherePivot('status', Status::Accepted)->whereIn('users.id', $validated['assignees']))
                )
                ->when(
                    array_key_exists('start', $validated) && array_key_exists('end', $validated) && $validated['start'] != null && $validated['end'] != null,
                    fn($q) => $q->whereBetween('tickets.finished_at', [Carbon::parse($validated['start'])->startOfDay(), Carbon::parse($validated['end'])->endOfDay()])
                )
                ->paginate(14),
            'customers' => fn() => Customer::inOrganization()->get(['id', 'name']),
            'users' => fn() => User::inOrganization()->get(['id', 'first_name', 'last_name', 'job_role']),
            'operatingSites' => fn() => collect([['title' => 'Homeoffice', 'value' => ['id' => $authUser->id, 'type' => User::class]]])
                ->merge(
                    CustomerOperatingSite::inOrganization()->with('currentAddress')
                        ->get()
                        ->map(fn($co) => [
                            'title' => $co->name,
                            'value' => ['id' => $co->id, 'type' => CustomerOperatingSite::class],
                            'customer_id' => $co->customer_id,
                            'address' => $co->currentAddress
                        ])
                )->merge(
                    OperatingSite::inOrganization()->with('currentAddress')
                        ->get()
                        ->map(fn($os) => [
                            'title' => $os->name,
                            'value' => ['id' => $os->id, 'type' => OperatingSite::class],
                            'address' => $os->currentAddress
                        ])
                ),
            'tab' => Inertia::always(fn() => $tab)
        ]);
    }

    public function store(Request $request, #[CurrentUser] User $authUser)
    {
        Gate::authorize('create', Ticket::class);

        $validated = $request->validate([
            'tab' => 'required|in:expressTicket,ticket',
            'title' => 'required|string|max:255',
            'operatingSite' => ['required', 'array', function ($attr, $value, $fail) {
                if (!array_key_exists('type', $value) || !array_key_exists('id', $value)) $fail('böse');
                $type = $value['type'];
                if (!in_array($type, [OperatingSite::class, CustomerOperatingSite::class, User::class])) {
                    $fail('Bitte gib einen gültigen Standort ein');
                };

                $operatingSite = $type::inOrganization()->exists($value['id']);
                if (!$operatingSite) $fail('Bitte gib einen gültigen Standort ein');
            }],
            'description' => 'nullable|required_if:tab,expressTicket|string|max:1000',
            'priority' => 'required|in:lowest,low,medium,high,highest',
            'customer_id' => ['required', Rule::exists('customers', 'id')->whereIn('id', Organization::getCurrent()->customers()->select('customers.id'))],
            'assignees' => 'present|array',
            'assignees.*' => ['required_if:tab,ticket', Rule::exists('users', 'id')->whereIn('id', Organization::getCurrent()->users()->select('users.id'))],
            'start' => 'nullable|required_if:tab,expressTicket|date',
            'duration' => 'nullable|required_if:tab,expressTicket|date_format:H:i',
            'resources' => 'nullable|string|max:1000',
            'appointment_at' => 'nullable|date',
            'files' => 'present|array',
            'files.*' => 'required|file|mimes:jpg,png,jpeg,avif,tiff,svg+xml,pdf|max:5120',
        ], [
            'files.*' => 'Die Dateien müssen im Format JPG, PNG, JPEG, AVIF, TIFF, SVG oder PDF vorliegen.',
        ]);

        $customer = Customer::inOrganization()->find($validated['customer_id']);

        $ticket = Ticket::create(
            [
                ...collect($validated)->only(['title', 'description', 'priority', 'customer_id', 'assignee_id']),
                'user_id' => $authUser->id,
                'reference_prefix' => strtoupper(substr($customer->name, 0, 3)),
                'appointment_at' => $validated['appointment_at'] ? Carbon::parse($validated['appointment_at']) : null,
            ]
        );

        $ticket->assignees()->attach($validated['assignees']);

        if ($validated["tab"] === "expressTicket") {
            $address = $validated['operatingSite']['type']::inOrganization()->find($validated['operatingSite']['id'])->currentAddress;

            $record = $ticket->records()->create([
                'resources' => $validated['resources'],
                'start' => Carbon::parse($validated['start']),
                'duration' => Carbon::parse($validated['duration'])->secondsSinceMidnight(),
                'description' => $validated['description'],
                'user_id' => $authUser->id,
                'address_id' => $address->id,
            ]);
            $ticket->update(['finished_at' => now()]);

            foreach ($validated['files'] as $file) {
                $path = Storage::disk('ticket_record_files')->putFile($file);
                $record->files()->create([
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                ]);
            }
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
            'description' => 'nullable|string|max:1000',
            'assignees' => 'present|array',
            'assignees.*' => ['required_if:tab,ticket', Rule::exists('users', 'id')->whereIn('id', Organization::getCurrent()->users()->select('users.id'))],
            'selected' => 'present|array',
            'selected.*' => [
                'required',
                Rule::exists('ticket_records', 'id')
                    ->whereIn(
                        'id',
                        TicketRecord::whereHas('ticket', fn($q) => $q->whereIn(
                            'id',
                            Organization::getCurrent()->tickets()->select('tickets.id')
                        ))->select('ticket_records.id')
                    )
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

        $userToNotify = collect($ticket->assignees);
        if ($ticket->records()->count() > 0) {
            $userToNotify = $userToNotify->merge(User::inOrganization()->get()->filter(fn($u) => $u->can('account', $ticket)));
        }

        $userToNotify->filter(fn($u) => !$u->is($authUser))->unique()->each->notify(new TicketFinishNotification($authUser, $ticket));

        return back()->with('success', 'Ticket erfolgreich abgeschlossen.');
    }

    public function unfinish(Ticket $ticket, #[CurrentUser] User $authUser)
    {
        Gate::authorize('update', $ticket);

        $ticket->update(['finished_at' => null]);

        $ticket->assignees->filter(fn($u) => !$u->is($authUser))->each->notify(new TicketUpdateNotification($authUser, $ticket));

        return back()->with('success', 'Ticket erfolgreich als unbearbeitet markiert.');
    }

    public function destroy(Ticket $ticket, #[CurrentUser] User $authUser)
    {
        Gate::authorize('delete',  $ticket);

        $ticket->assignees->each->notify(new TicketDeletionNotification($authUser, $ticket));

        $ticket->delete();

        return back()->with('success', 'Ticket erfolgreich gelöscht.');
    }

    public function accept(Ticket $ticket, #[CurrentUser] User $authUser)
    {
        Gate::authorize('update', Ticket::class);

        $authUser->tickets()->syncWithoutDetaching([$ticket->id]);
        $authUser->tickets()->updateExistingPivot($ticket->id, ['status' => 'accepted']);

        return back()->with('success', 'Ticket erfolgreich angenommen.');
    }
}
