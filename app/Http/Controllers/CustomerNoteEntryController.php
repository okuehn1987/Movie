<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerNoteEntry;
use App\Models\CustomerNoteFolder;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

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
            'selectedFolder' => ['required', Rule::exists('customer_note_folders', 'id')->where('customer_id', $customer->id)],

            'metadata' => 'nullable|array',
            'metadata.*' => 'nullable|string'
        ], ['title.required' => 'Bezeichnung ist erforderlich.', 'file.required_if' => 'Datei ist erforderlich, wenn der Typ Datei ausgewählt ist.']);

        $path = array_key_exists('file', $validated) && $validated['file'] ?
            Storage::disk('customer_note_files')->putFile($validated['file']) :
            $validated['value'];

        $customer->customerNoteEntries()->create([
            ...Arr::except($validated, ['selectedFolder', 'file']),
            'modified_by' => $authUser->id,
            'customer_note_folder_id' => $validated['selectedFolder'],
            'value' => $path,
            'metadata' => $validated['metadata'] ?? []
        ]);

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
