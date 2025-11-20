<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerNote;
use App\Models\CustomerNoteFolder;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class CustomerNoteFolderController extends Controller
{
    public function store(Request $request, Customer $customer)
    {
        Gate::authorize('publicAuth', User::class);

        $topLevelFolders = $customer->customerNoteFolders()
            ->whereNull('customer_note_folder_id')
            ->get(['id']);

        $firstLevelSubfolders = $customer->customerNoteFolders()
            ->whereIn('customer_note_folder_id', $topLevelFolders->pluck('id'))
            ->get(['id', 'customer_note_folder_id']);

        $validated = $request->validate([
            'name' => 'required|string',
            'customerNoteFolder' => [
                'nullable',
                Rule::in($topLevelFolders->pluck('id')->merge($firstLevelSubfolders->pluck('id')))
            ]
        ], ['name.required' => 'Bezeichnung ist erforderlich.']);

        $customerNoteFolder = $validated['customerNoteFolder'] ?? null;

        $customer->customerNoteFolders()->create([
            'name' => $validated['name'],
            'customer_id' => $customer->id,
            'customer_note_folder_id' => $customerNoteFolder,
        ]);

        return back()->with('success', 'Kategorie erfolgreich erstellt.');
    }

    public function update(Request $request, CustomerNoteFolder $customerNoteFolder)
    {
        Gate::authorize('publicAuth', User::class);

        $validated = $request->validate([
            'name' => 'required|string',
        ]);

        $customerNoteFolder->update([
            'name' => $validated['name'],
        ]);

        return back()->with('success', 'Kategorie erfolgreich aktualisiert');
    }

    public function destroy(CustomerNoteFolder $customerNoteFolder)
    {
        Gate::authorize('publicAuth', User::class);

        $customerNoteFolder->delete();

        return redirect(route('customer.show', ['customer' => $customerNoteFolder->customer_id]))->with('success', 'Kategorie erfolgreich gelöscht');
    }
}
