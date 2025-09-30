<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerOperatingSite;
use Illuminate\Http\Request;

class CustomerOperatingSiteController extends Controller
{

    public function store(Request $request, Customer $customer)
    {
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

        $customerOperatingSite->addresses()->create([
            'street' => $validated['street'],
            'house_number' => $validated['house_number'],
            'address_suffix' => $validated['address_suffix'],
            'country' => $validated['country'],
            'city' => $validated['city'],
            'zip' => $validated['zip'],
            'federal_state' => $validated['federal_state'],
        ]);

        return back()->with('success', 'Der Standort wurde erfolgreich angelegt.');
    }

    public function update(Request $request, CustomerOperatingSite $customerOperatingSite)
    {
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

        if ($customerOperatingSite->name !== $validated['name']) {
            $customerOperatingSite->update([
                'name' => $validated['name'],
            ]);
        }

        $newAddress = collect($validated)->except([
            'name'
        ])->toArray();

        $oldAddress = $customerOperatingSite->currentAddress->only(array_keys($newAddress));

        if ($newAddress !== $oldAddress) {
            $newAddress['active_since'] = now();
            $customerOperatingSite->addresses()->create($newAddress);
        }

        return back()->with('success', 'Der Standort wurde erfolgreich aktualisiert.');
    }

    public function destroy()
    {
        dd('destroy');
    }
}
