<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\TimeAccount;
use App\Models\TimeAccountSetting;
use App\Models\TimeAccountTransaction;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TimeAccountTransactionController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'from_id' => [
                'nullable',
                Rule::exists('time_accounts', 'id')
                    ->whereIn(
                        'time_account_settings_id',
                        TimeAccountSetting::where('organization_id', Organization::getCurrent()->id)->get(['id'])
                    )
            ],
            'to_id' => [
                'required',
                Rule::exists('time_accounts', 'id')
                    ->whereIn(
                        'time_account_settings_id',
                        TimeAccountSetting::where('organization_id', Organization::getCurrent()->id)->get(['id'])
                    )
            ],
            'amount' => 'required|numeric',
            'description' => 'required|string',
        ]);

        $fromAccount = array_key_exists('from_id', $validated) ? TimeAccount::find($validated['from_id']) : null;
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
