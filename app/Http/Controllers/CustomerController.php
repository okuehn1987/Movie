<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Organization;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
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

    public function show(Customer $customer)
    {
        Gate::authorize('viewShow', Customer::class);

        $ticketQuery = Ticket::inOrganization()->where('customer_id', $customer->id)
            ->with(['customer:id,name', 'user:id,first_name,last_name', 'assignees:id,first_name,last_name', 'records.user', 'records.files']);

        return Inertia::render('Customer/CustomerShow', [
            'customer' => $customer->load('tickets.assignees', 'tickets.user', 'tickets.customer', 'tickets.records.user', 'tickets.records.files'),
            'tickets' => (clone $ticketQuery)
                ->whereNull('tickets.finished_at')
                ->orWhereHas('records', fn($q) => $q->whereNull('accounted_at'))
                ->get(),
            'archiveTickets' => (clone $ticketQuery)
                ->whereNotNull('tickets.finished_at')
                ->whereDoesntHave('records', fn($q) => $q->whereNull('accounted_at'))
                ->get(),
            'operatingSites' => $customer->customerOperatingSites()->with('currentAddress')->get(),
            'customerNotes' => $customer->customerNotes,
            'users' => User::inOrganization()->get(),
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
            'organization_id' => Organization::id(),
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
