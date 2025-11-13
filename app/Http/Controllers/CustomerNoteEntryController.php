<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerNoteEntry;
use App\Models\CustomerNoteFolder;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class CustomerNoteEntryController extends Controller
{

    public function store(Request $request, Customer $customer,  #[CurrentUser] User $authUser)
    {
        Gate::authorize('publicAuth', User::class);
        $validated = $request->validate([
            'type' => 'required|in:text,file',
            'title' => 'required|string',
            'value' => 'nullable|required_if:type,text|string|max:200',
            'file' => 'nullable|required_if:type,file|file',
            'selectedFolder' => 'required|exists:customer_note_folders,id',

            'metadata' => 'nullable|array',
            'metadata.*' => 'string'
        ]);

        if ($validated['type'] == 'file') {
            $path = $validated['file'] ? Storage::disk('customer_note_files')->putFile($validated['file']) : null;

            $customer->customerNoteEntries()->create([
                'type' => $validated['type'],
                'modified_by' => $authUser->id,
                'customer_note_folder_id' => $validated['selectedFolder'],
                'title' => $validated['title'],
                'value' => $path,
                'metadata' => $validated['metadata'],
            ]);
        } else {
            $customer->customerNoteEntries()->create([
                'type' => $validated['type'],
                'modified_by' => $authUser->id,
                'customer_note_folder_id' => $validated['selectedFolder'],
                'title' => $validated['title'],
                'value' => $validated['value'],
                'metadata' => $validated['metadata'],
            ]);
        }

        return back()->with('success', 'Notiz wurde erfolgreich angelegt');
    }

    public function update(Request $request, CustomerNoteEntry $customerNoteEntry, #[CurrentUser] User $authUser)
    {
        Gate::authorize('publicAuth', User::class);

        $validated = $request->validate([
            'type' => 'required|in:text,file',
            'title' => 'required|string',
            'value' => 'nullable|required_if:type,text|string|max:200',
            'file' => 'nullable|required_if:type,file|file',
            'selectedFolder' => 'required|int',

            'metadata' => 'present|array',
            'metadata.*' => 'string'
        ]);

        if ($validated['type'] == 'file') {
            $path = array_key_exists('file', $validated) && $validated['file'] ? Storage::disk('customer_note_files')->putFile($validated['file']) : null;

            if ($customerNoteEntry->value && $path) {
                Storage::disk('customer_note_files')->delete($customerNoteEntry->value);
            }

            $customerNoteEntry->update([
                'modified_by' => $authUser->id,
                'type' => $validated['type'],
                'title' => $validated['title'],
                'metadata' => $validated['metadata'],
                ...(array_key_exists('file', $validated) && $validated['file'] ? ['value' =>  $path] : []),
            ]);
        } else {
            if ($customerNoteEntry->type == 'file' && $customerNoteEntry->value) {
                Storage::disk('customer_note_files')->delete($customerNoteEntry->value);
            }

            $customerNoteEntry->update([
                'modified_by' => $authUser->id,
                'type' => $validated['type'],
                'title' => $validated['title'],
                'value' => $validated['value'],
                'metadata' => $validated['metadata'],
            ]);
        }

        return back()->with('success', 'Notiz wurde erfolgreich aktualisiert');
    }

    public function destroy(CustomerNoteEntry $customerNoteEntry)
    {
        Gate::authorize('publicAuth', User::class);

        Storage::disk('customer_note_files')->delete($customerNoteEntry->value);

        $customerNoteEntry->delete();

        return back()->with('success', 'Die Notiz wurde erfolgreich gelöscht');
    }


    public function getFile(CustomerNoteEntry $customerNoteEntry)
    {
        Gate::authorize('publicAuth', User::class);

        return response()->file(Storage::disk('customer_note_files')->path($customerNoteEntry->value));
    }
}
