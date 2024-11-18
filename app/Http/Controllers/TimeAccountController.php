<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\TimeAccount;
use App\Models\TimeAccountTransaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TimeAccountController extends Controller
{
    public function store(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'balance' => 'required|numeric',
            'balance_limit' => 'required|numeric',
            'time_account_setting_id' => ['required', Rule::exists('time_account_settings', 'id')->where('organization_id', Organization::getCurrent()->id)],
        ]);

        $timeAccount = TimeAccount::create([...$validated, 'user_id' => $user->id]);

        TimeAccountTransaction::create([
            'to_id' => $timeAccount->id,
            'modified_by' => Auth::id(),
            'amount' => $validated['balance'],
            'is_system_generated' => true,
            'description' => 'Initialer Kontostand',
        ]);

        return back()->with('success', 'Arbeitszeitkonto erfolgreich erstellt.');
    }
}
