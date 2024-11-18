<?php

namespace App\Http\Controllers;

use App\Models\TimeAccount;
use App\Models\TimeAccountTransaction;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TimeAccountTransactionController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'from_id' => ['nullable', 'exists:time_accounts,id'], // TODO: Rule exists with inOrganization scope
            'to_id' => 'required|exists:time_accounts,id', // TODO: Rule exists with inOrganization scope
            'amount' => 'required|numeric',
            'description' => 'required|string',
        ]);

        $fromAccount = TimeAccount::find($validated['from_id']);
        $toAccount = TimeAccount::find($validated['to_id']);

        if ($fromAccount) {
            $fromAccount->balance -= $validated['amount'];
            $fromAccount->save();
        }

        $toAccount->balance += $validated['amount'];
        $toAccount->save();

        TimeAccountTransaction::create($validated);

        return back()->with('success', 'Transaktion erfolgreich durchgeführt.');
    }
}
