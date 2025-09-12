<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerNote;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CustomerNoteController extends Controller
{
    public function store(Request $request, Customer $customer, #[CurrentUser] User $authUser)
    {
        Gate::authorize('publicAuth', User::class);

        $validated = $request->validate([
            'type' => 'required|in:primitive,complex,file',
            'key' => 'required|string',
            'value' => 'required|string',
            'parent_id' => 'nullable|required_if:type,complex|exists:customer_notes,id'
        ]);

        $customer->customerNotes()->create([
            'modified_by' => $authUser->id,
            'parent_id' => $validated['parent_id'],
            'type' => $validated['type'],
            'key' => $validated['key'],
            'value' => $validated['value'],
        ]);

        return back()->with('success', 'Notiz erfolgreich erstellt.');
    }

    public function update(Request $request, CustomerNote $customerNote)
    {
        Gate::authorize('publicAuth', User::class);

        $validated = $request->validate([
            'key' => 'required|string|max:65535',
            'value' => 'nullable|string|max:65535',
        ]);

        $customerNote->update([
            'key' => $validated['key'],
            'value' => $validated['value'],
        ]);

        return back()->with('success', 'Notiz erfolgreich aktualisiert');
    }

    public function destroy(CustomerNote $customerNote)
    {
        Gate::authorize('publicAuth', User::class);

        $customerNote->delete();

        return back()->with('success', 'Notiz erfolgreich gelöscht');
    }
}
