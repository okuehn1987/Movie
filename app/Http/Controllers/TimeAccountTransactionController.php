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
        Gate::authorize('create', [TimeAccountTransaction::class, (TimeAccount::find($request['from_id']) ?? TimeAccount::find($request['to_id']))?->user]);

        $validated = $request->validate([
            'from_id' => [
                Rule::in([null, ...TimeAccount::inOrganization()->get(['id'])->pluck('id')]),
                'required_if:to_id,null',
            ],
            'to_id' => [
                Rule::in([null, ...TimeAccount::inOrganization()->get(['id'])->pluck('id')]),
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

        TimeAccount::transferBalanceFromTo(
            $validated["amount"],
            $validated["description"],
            $fromAccount,
            $toAccount
        );


        return back()->with('success', 'Transaktion erfolgreich durchgeführt.');
    }
}
