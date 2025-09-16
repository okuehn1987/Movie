<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\TimeAccount;
use App\Models\TimeAccountSetting;
use App\Models\TimeAccountTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class TimeAccountTransactionController extends Controller
{
    public function store(Request $request)
    {
        $timeAccount = TimeAccount::find($request['from_id'] ?? $request['to_id']);

        Gate::authorize('create', [TimeAccountTransaction::class, $timeAccount?->user]);

        $validated = $request->validate([
            'from_id' => [
                Rule::in([null, ...$timeAccount->user->timeAccounts->pluck('id')]),
                'required_if:to_id,null',
            ],
            'to_id' => [
                Rule::in([null, ...$timeAccount->user->timeAccounts->pluck('id')]),
                'required_if:from_id,null',
            ],
            'amount' => "required|numeric|min:0",
            'description' => 'required|string',
        ]);

        $fromAccount = TimeAccount::find($validated['from_id']);
        $toAccount = TimeAccount::find($validated['to_id']);

        if ($fromAccount && $toAccount && $fromAccount->user_id !== $toAccount->user_id) {
            return back()->with('error', 'Transaktionen zwischen zwei verschiedenen Benutzern sind nicht möglich.');
        }

        if ($toAccount->balance_limit !== null && ($toAccount->balance + ($validated["amount"] * 3600)) > $toAccount->balance_limit) {
            return back()->with('error', 'Das Guthaben des Zielkontos würde das Limit überschreiten.');
        }

        TimeAccount::transferBalanceFromTo(
            $validated["amount"] * 3600,
            $validated["description"],
            $fromAccount,
            $toAccount
        );


        return back()->with('success', 'Transaktion erfolgreich durchgeführt.');
    }
}
