<?php

use App\Models\Organization;
use App\Models\TimeAccount;
use Carbon\Carbon;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();


Schedule::call(function () {
    $organizations = Organization::where('balance_truncation_day', Carbon::now()->day)->with('users')->get();
    $timeAccounts = $organizations->flatMap(fn($o) => $o->users->flatMap(
        fn($u) => $u->timeAccounts()
            ->whereHas(
                'timeAccountSetting',
                fn($q) => $q
                    ->whereNotNull("truncation_cycle_length_in_months")
                    // where the current month is a month that should be truncated eg. 6 MOD 3 = 0, 7 MOD 3 != 0
                    ->where(DB::raw(Carbon::now()->month . " MOD truncation_cycle_length_in_months"), 0)
            )->get()
    ));

    foreach ($timeAccounts as $timeAccount) {
        if ($timeAccount->balance > $timeAccount->balance_limit)
            $timeAccount->addBalance(- ($timeAccount->balance - $timeAccount->balance_limit), 'Monatsabrechnung');
    }
})->name('monthlyBalanceTruncation')->dailyAt("01:00");
