<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\TimeAccount;
use App\Models\TimeAccountSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class TimeAccountController extends Controller
{
    public function store(Request $request, User $user)
    {
        Gate::authorize('create', [TimeAccount::class, $user]);

        $validated = $request->validate([
            'name' => 'required|string',
            'balance' => 'required|numeric',
            'balance_limit' => 'required|numeric',
            'time_account_setting_id' => [
                'required',
                Rule::exists('time_account_settings', 'id')
                    ->whereNotNull('type')
                    ->where('organization_id', Organization::getCurrent()->id)
            ],
        ]);

        $timeAccount = TimeAccount::create([...$validated, 'user_id' => $user->id]);

        $timeAccount->addBalance($validated['balance'], 'Initialer Kontostand');

        return back()->with('success', 'Arbeitszeitkonto erfolgreich erstellt.');
    }

    public function update(Request $request, TimeAccount $timeAccount)
    {
        Gate::authorize('update', $timeAccount);

        $validated = $request->validate([
            'name' => 'required|string',
            'balance_limit' => 'required|numeric',
            'time_account_setting_id' => [
                'required',
                function ($attr, $val, $fail) use ($timeAccount) {
                    if ($timeAccount->id == $timeAccount->user->defaultTimeAccount()->id) {
                        return;
                    } else {
                        return TimeAccountSetting::where('id', $val)
                            ->where('organization_id', Organization::getCurrent()->id)
                            ->whereNotNull('type')
                            ->exists() ?: $fail('Ungültige Einstellung');
                    }
                },
            ],
        ]);

        $timeAccount->update($validated);

        return back()->with('success', 'Arbeitszeitkonto erfolgreich aktualisiert.');
    }

    public function destroy(TimeAccount $timeAccount)
    {
        Gate::authorize('delete', [TimeAccount::class, $timeAccount->user]);

        if ($timeAccount->balance != 0 || $timeAccount->id == $timeAccount->user->defaultTimeAccount()->id) {
            return back()->with('error', 'Das Arbeitszeitkonto kann nicht gelöscht werden');
        }

        $timeAccount->delete();

        return back()->with('success', 'Arbeitszeitkonto erfolgreich gelöscht.');
    }
}
