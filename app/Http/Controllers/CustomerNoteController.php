<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerNote;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class CustomerNoteController extends Controller
{
    public function store(Request $request, Customer $customer, #[CurrentUser] User $authUser)
    {
        Gate::authorize('publicAuth', User::class);

        $validated = $request->validate([
            'type' => 'required|in:primitive,complex,file',
            'key' => 'required|string',
            'value' => 'nullable|required_if:type,primitive|string',
            'parent_id' => 'nullable|exists:customer_notes,id',
            'file' => 'nullable|required_if:type,file|file',
        ]);

        if ($validated['type'] == 'file') {
            $path = $validated['file'] ? Storage::disk('customer_note_files')->putFile($validated['file']) : null;

            $customer->customerNotes()->create([
                'modified_by' => $authUser->id,
                'parent_id' => $validated['parent_id'],
                'type' => $validated['type'],
                'key' => $validated['key'],
                'value' => $path,
            ]);
        } else {
            $customer->customerNotes()->create([
                'modified_by' => $authUser->id,
                'parent_id' => $validated['parent_id'],
                'type' => $validated['type'],
                'key' => $validated['key'],
                'value' => $validated['value'],
            ]);
        }

        return back()->with('success', 'Notiz erfolgreich erstellt.');
    }

    public function update(Request $request, CustomerNote $customerNote)
    {
        Gate::authorize('publicAuth', User::class);

        $validated = $request->validate([
            'key' => 'required|string|max:65535',
            'value' => 'nullable|string|max:65535',
            'file' => 'nullable|file',
        ]);

        if ($customerNote->type == 'file') {
            $path = array_key_exists('file', $validated) && $validated['file'] ? Storage::disk('customer_note_files')->putFile($validated['file']) : null;

            if ($customerNote->value && $path)
                Storage::disk('customer_note_files')->delete($customerNote->value);

            $customerNote->update([
                'key' => $validated['key'],
                ...(array_key_exists('file', $validated) && $validated['file'] ? ['value' =>  $path] : []),
            ]);
        } else {
            $customerNote->update([
                'key' => $validated['key'],
                'value' => $validated['value'],
            ]);
        }

        return back()->with('success', 'Notiz erfolgreich aktualisiert');
    }

    public function destroy(CustomerNote $customerNote)
    {
        Gate::authorize('publicAuth', User::class);

        Storage::disk('customer_note_files')->delete($customerNote->value);
        $customerNote->delete();

        return back()->with('success', 'Notiz erfolgreich gelöscht');
    }

    public function getFile(CustomerNote $customerNote)
    {
        Gate::authorize('publicAuth', User::class);

        if ($customerNote->type != 'file') abort(404);

        return response()->file(Storage::disk('customer_note_files')->path($customerNote->value));
    }
}
