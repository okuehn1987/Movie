<?php

namespace App\Http\Controllers;

use App\Models\CustomerNote;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CustomerNoteController extends Controller
{
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
