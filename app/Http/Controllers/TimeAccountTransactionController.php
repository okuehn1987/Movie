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
        Gate::authorize('create', [TimeAccountTransaction::class, (TimeAccount::find($request['from_id']) ?? TimeAccount::find($request['to_id']))->user]);

        $validated = $request->validate([
            'from_id' => [
                'nullable',
                'required_if:to_id,null',
                Rule::exists('time_accounts', 'id')
                    ->whereIn(
                        'time_account_setting_id',
                        TimeAccountSetting::where('organization_id', Organization::getCurrent()->id)->get(['id'])
                    )
            ],
            'to_id' => [
                'nullable',
                'required_if:from_id,null',
                Rule::exists('time_accounts', 'id')
                    ->whereIn(
                        'time_account_setting_id',
                        TimeAccountSetting::where('organization_id', Organization::getCurrent()->id)->get(['id'])
                    )
            ],
            'amount' => "required|numeric|min:0",
            'description' => 'required|string',
        ]);

        TimeAccount::transferBalanceFromTo(
            $validated["amount"],
            $validated["description"],
            TimeAccount::find($validated["from_id"]),
            TimeAccount::find($validated['to_id'])
        );


        return back()->with('success', 'Transaktion erfolgreich durchgeführt.');
    }
}
