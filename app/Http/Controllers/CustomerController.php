<?php

namespace App\Http\Controllers;

use App\Enums\Status;
use App\Models\Customer;
use App\Models\CustomerContact;
use App\Models\CustomerNoteFolder;
use App\Models\CustomerOperatingSite;
use App\Models\OperatingSite;
use App\Models\Organization;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class CustomerController extends Controller
{
    public function index()
    {
        Gate::authorize('publicAuth', User::class);

        return Inertia::render('Customer/CustomerIndex', [
            'customers' => Organization::getCurrent()->customers()->with(['customerOperatingSites.currentAddress'])->get(),
            'can' => [
                'customer' => [
                    'viewShow' => Gate::allows('viewShow', Customer::class),
                    'update' => Gate::allows('update', Customer::class),
                    'delete' => Gate::allows('delete', Customer::class),
                    'create' => Gate::allows('create', Customer::class),
                ]
            ],
        ]);
    }

    public function show(Request $request, Customer $customer, #[CurrentUser] User $authUser)
    {
        Gate::authorize('viewShow', Customer::class);

        $validated = $request->validate([
            'selectedFolder' => 'nullable|exists:customer_note_folders,id',
            'tab' => 'nullable|string|in:archive,finishedTickets,newTickets,workingTickets',
            'customer_id' => 'nullable|exists:customers,id',
            'assignees' => 'nullable|array',
            'assignees.*' => ['nullable', Rule::exists('users', 'id')->whereIn('id', Organization::getCurrent()->users()->select('users.id'))],
            'start' => 'nullable|date|required_with:end',
            'end' => 'nullable|date|required_with:start|after_or_equal:start',
            'openTicket' => ['nullable', Rule::exists('tickets', 'id')->whereIn('id', Ticket::inOrganization()->select('tickets.id'))],
        ]);

        $selectedFolder = array_key_exists('selectedFolder', $validated)
            ? CustomerNoteFolder::find($validated['selectedFolder'])
            : $customer->customerNoteFolders()->first();

        $openTicket = array_key_exists('openTicket', $validated) && $validated['openTicket'] != null ? Ticket::find($validated['openTicket']) : null;

        $tab = match (true) {
            $openTicket && $openTicket->finished_at != null && !$openTicket->records()->whereNull('accounted_at')->exists() => 'archive',
            $openTicket && $openTicket->finished_at != null && $openTicket->records()->whereNull('accounted_at')->exists() => 'finishedTickets',
            $openTicket && $openTicket->finished_at == null && $openTicket->assignees()->where('status', 'accepted')->exists() => 'workingTickets',
            array_key_exists('tab', $validated) && $validated['tab'] => $validated['tab'],
            default => 'newTickets',
        };

        $ticketQuery = $customer->tickets()->with([
            'customer:id,name',
            'user:id,first_name,last_name',
            'assignees:id,first_name,last_name',
            'records.user',
            'records.files'
        ]);
        return Inertia::render('Customer/CustomerShow', [
            'customer' => $customer->load('contact'),
            'operatingSites' => $customer->customerOperatingSites()->with('currentAddress')->get(),
            'customerNoteFolders' => $customer->customerNoteFolders()->whereNull('customer_note_folder_id')->with([
                'subFolders:id,name,customer_note_folder_id',
                'subFolders.subFolders:id,name,customer_note_folder_id'
            ])->get(['id', 'name']),
            'customerNoteEntries' =>  $selectedFolder?->entries()
                ->with(['user' => fn($q) => $q->select(['id', 'first_name', 'last_name'])])
                ->get(['id', 'type', 'title', 'value', 'updated_at', 'metadata', 'modified_by']),
            'users' => User::inOrganization()->get(),
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
                ->with('records')
                ->when(
                    array_key_exists('customer_id', $validated) && $validated['customer_id'] != null,
                    fn($q) => $q->where('customer_id', $validated['customer_id'])
                )
                ->when(
                    array_key_exists('assignees', $validated) && $validated['assignees'] != null,
                    fn($q) => $q->whereHas('assignees', fn($q2) => $q2->where('status', Status::Accepted)->whereIn('users.id', $validated['assignees']))
                )
                ->when(
                    array_key_exists('start', $validated) && array_key_exists('end', $validated) && $validated['start'] != null && $validated['end'] != null,
                    fn($q) => $q->whereBetween('tickets.finished_at', [Carbon::parse($validated['start'])->startOfDay(), Carbon::parse($validated['end'])->endOfDay()])
                )
                ->paginate(13),
            'ticketableOperatingSites' => fn() => collect([['title' => 'Homeoffice', 'value' => ['id' => $authUser->id, 'type' => User::class]]])
                ->merge(
                    $customer->customerOperatingSites()->with('currentAddress')
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
            'ticketTab' => Inertia::always(fn() => $tab),
            'can' => [
                'customer' => [
                    'viewShow' => Gate::allows('viewShow', Customer::class),
                    'update' => Gate::allows('update', Customer::class),
                    'delete' => Gate::allows('delete', Customer::class),
                    'create' => Gate::allows('create', Customer::class),
                ],
                'customerContact' => [
                    'create' => Gate::allows('create', CustomerContact::class),
                    'update' => Gate::allows('update', CustomerContact::class),
                    'delete' => Gate::allows('delete', CustomerContact::class),
                ],
                'customerOperatingSite' => [
                    'create' => Gate::allows('create', CustomerOperatingSite::class),
                    'update' => Gate::allows('update', CustomerOperatingSite::class),
                    'delete' => Gate::allows('delete', CustomerOperatingSite::class),
                ]
            ],
        ]);
    }

    public function store(Request $request)
    {
        Gate::authorize('create', Customer::class);

        $validated = $request->validate([
            'name' => 'required|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'reference_number' => 'nullable|string',
        ]);

        Customer::create([
            ...$validated,
            'organization_id' => Organization::getCurrent()->id,
        ]);

        return back()->with('success', 'Der Kunde wurde erfolgreich angelegt.');
    }

    public function update(Request $request, Customer $customer)
    {
        Gate::authorize('update', Customer::class);

        $validated = $request->validate([
            'name' => 'required|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'reference_number' => 'nullable|string',
        ]);

        $customer->update($validated);

        return back()->with('success', 'Der Kunde wurde erfolgreich aktualisiert.');
    }
}
