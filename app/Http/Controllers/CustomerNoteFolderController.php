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
    public function store(Request $request, Customer $customer, #[CurrentUser] User $authUser)
    {
        Gate::authorize('publicAuth', User::class);
        $validated = $request->validate([
            'name' => 'required|string',
        ]);

        $customer->customerNoteFolders()->create([
            'name' => $validated['name'],
            'customer_id' => $customer->id,
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
        //TODO: when entrys work, have a look at entry deletion
        $customerNoteFolder->delete();

        return back()->with('success', 'Kategorie erfolgreich gelöscht');
    }
}
