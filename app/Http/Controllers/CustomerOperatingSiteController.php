<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Customer;
use App\Models\CustomerOperatingSite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CustomerOperatingSiteController extends Controller
{

    public function store(Request $request, Customer $customer)
    {
        Gate::authorize('create', Customer::class);

        $validated = $request->validate([
            'name' => 'required|string',
            'street' => 'required|string',
            'house_number' => 'required|string',
            'address_suffix' => 'nullable|string',
            'country' => 'required|string',
            'city' => 'required|string',
            'zip' => 'required|string',
            'federal_state' => 'required|string',
        ]);

        $customerOperatingSite = CustomerOperatingSite::create([
            'customer_id' => $customer->id,
            'name' => $validated['name'],
        ]);

        $customerOperatingSite->addresses()->create(collect($validated)->only(Address::$ADDRESS_KEYS)->toArray());

        return back()->with('success', 'Der Standort wurde erfolgreich angelegt.');
    }

    public function update(Request $request, CustomerOperatingSite $customerOperatingSite)
    {
        Gate::authorize('update', Customer::class);

        $validated = $request->validate([
            'name' => 'required|string',
            'street' => 'required|string',
            'house_number' => 'required|string',
            'address_suffix' => 'nullable|string',
            'country' => 'required|string',
            'city' => 'required|string',
            'zip' => 'required|string',
            'federal_state' => 'required|string',
        ]);

        $customerOperatingSite->update([
            'name' => $validated['name'],
        ]);

        foreach (Address::$ADDRESS_KEYS as $key) {
            $customerOperatingSite->currentAddress->$key = $validated[$key];
        }
        if ($customerOperatingSite->currentAddress->isDirty()) {
            $customerOperatingSite->currentAddress->active_since = now();
            $customerOperatingSite->currentAddress->replicate()->save();
        }

        return back()->with('success', 'Der Standort wurde erfolgreich aktualisiert.');
    }

    public function destroy(CustomerOperatingSite $customerOperatingSite)
    {
        Gate::authorize('delete', Customer::class);

        $customerOperatingSite->delete();

        return back()->with('success', 'Der Standort wurde erfolgreich gelöscht.');
    }
}
