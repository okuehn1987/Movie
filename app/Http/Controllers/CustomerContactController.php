<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerContact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CustomerContactController extends Controller
{
    public function store(Request $request, Customer $customer)
    {
        Gate::authorize('create', CustomerContact::class);

        $validated = $request->validate([
            'name'          => 'nullable|string',
            'occupation'    => 'nullable|string',
            'email'         => 'nullable|email|required_without_all:phone_number,mobile_number',
            'phone_number'  => 'nullable|string|required_without_all:email,mobile_number',
            'mobile_number' => 'nullable|string|required_without_all:email,phone_number',
        ], [
            'email.required_without_all'         => 'Bitte gib mindestens eine Kontaktmöglichkeit an.',
            'phone_number.required_without_all'  => 'Bitte gib mindestens eine Kontaktmöglichkeit an.',
            'mobile_number.required_without_all' => 'Bitte gib mindestens eine Kontaktmöglichkeit an.',
        ]);

        if (empty($validated['name'])) {
            $validated['name'] = '';
        }

        if (empty($validated['occupation'])) {
            $validated['occupation'] = '';
        }

        $customer->contacts()->create($validated);

        return back()->with('success', 'Kontakt wurde erfolgreich angelegt.');
    }

    public function update(Request $request, CustomerContact $customerContact)
    {
        Gate::authorize('update', CustomerContact::class);

        $validated = $request->validate([
            'name'          => 'nullable|string',
            'occupation'    => 'nullable|string',
            'email'         => 'nullable|email|required_without_all:phone_number,mobile_number',
            'phone_number'  => 'nullable|string|required_without_all:email,mobile_number',
            'mobile_number' => 'nullable|string|required_without_all:email,phone_number',
        ], [
            'email.required_without_all'         => 'Bitte gib mindestens eine Kontaktmöglichkeit an.',
            'phone_number.required_without_all'  => 'Bitte gib mindestens eine Kontaktmöglichkeit an.',
            'mobile_number.required_without_all' => 'Bitte gib mindestens eine Kontaktmöglichkeit an.',
        ]);

        if (empty($validated['name'])) {
            $validated['name'] = '';
        }

        if (empty($validated['occupation'])) {
            $validated['occupation'] = '';
        }

        $customerContact->update($validated);

        return back()->with('success', 'Kontakt wurde erfolgreich aktualisiert.');
    }

    public function destroy(CustomerContact $customerContact)
    {
        Gate::authorize('delete', CustomerContact::class);

        $customerContact->delete();

        return back()->with('success', 'Kontakt wurde erfolgreich gelöscht.');
    }
}
