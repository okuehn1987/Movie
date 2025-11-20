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
            'name' => 'required|string',
            'occupation' => 'required|string',
            'email' => 'nullable|email',
            'phone_number' => 'nullable|string',
            'mobile_number' => 'nullable|string',
        ]);

        $customer->contacts()->create($validated);

        return back()->with('success', 'Kontakt wurde erfolgreich angelegt.');
    }

    public function update(Request $request, CustomerContact $customerContact)
    {
        Gate::authorize('update', CustomerContact::class);

        $validated = $request->validate([
            'name' => 'required|string',
            'occupation' => 'required|string',
            'email' => 'nullable|email',
            'phone_number' => 'nullable|string',
            'mobile_number' => 'nullable|string',
        ]);

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
