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

        $validated = $request->validate([
            'type' => 'required|in:primitive,file',
            'title' => 'required|string',
            'value' => 'nullable|required_if:type,primitive|string|max:200',
            'file' => 'nullable|required_if:type,file|file',
            'selectedFolder' => 'required|int',
        ]);

        if ($validated['type'] == 'file') {
            $path = $validated['file'] ? Storage::disk('customer_note_files')->putFile($validated['file']) : null;

            $customer->customerNoteEntries()->create([
                'modified_by' => $authUser->id,
                'customer_note_folder_id' => $validated['selectedFolder'],
                'title' => $validated['title'],
                'value' => $path,
            ]);
        } else {
            $customer->customerNoteEntries()->create([
                'modified_by' => $authUser->id,
                'customer_note_folder_id' => $validated['selectedFolder'],
                'title' => $validated['title'],
                'value' => $validated['value'],
            ]);
        }

        return back()->with('success', 'Notiz wurde erfolgreich angelegt.');
    }

    public function update($request)
    {
        // $validated = $request->validate([
        //     'key' => 'required|string|max:65535',
        //     'value' => 'nullable|string|max:65535',
        //     'file' => 'nullable|file',
        // ]);

        // if ($customerNote->type == 'file') {
        //     $path = array_key_exists('file', $validated) && $validated['file'] ? Storage::disk('customer_note_files')->putFile($validated['file']) : null;

        //     if ($customerNote->value && $path)
        //         Storage::disk('customer_note_files')->delete($customerNote->value);

        //     $customerNote->update([
        //         'key' => $validated['key'],
        //         ...(array_key_exists('file', $validated) && $validated['file'] ? ['value' =>  $path] : []),
        //     ]);
    }

    public function destroy($request)
    {
        // Storage::disk('customer_note_files')->delete($customerNoteFolder->value);
    }


    public function getFile(CustomerNoteEntry $customerNoteEntry)
    {
        Gate::authorize('publicAuth', User::class);

        return response()->file(Storage::disk('customer_note_files')->path($customerNoteEntry->value));
    }
}
