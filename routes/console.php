<?php

use App\Models\Organization;
use App\Models\TimeAccount;
use Carbon\Carbon;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();


Schedule::call(function () {
    $organizations = Organization::where('balance_truncation_day', Carbon::now()->day)->with('users')->get();
    $timeAccounts = $organizations->flatMap(fn($o) => $o->users->flatMap(fn($u) => $u->timeAccounts));

    foreach ($timeAccounts as $timeAccount) {
        if ($timeAccount->balance > $timeAccount->balance_limit)
            $timeAccount->updateBalance(- ($timeAccount->balance - $timeAccount->balance_limit), 'Monatsabrechnung', true);
    }
})->name('monthlyBalanceTruncation')->daily();
