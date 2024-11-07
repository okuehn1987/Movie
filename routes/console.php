<?php

use App\Models\Organization;
use App\Models\TimeAccount;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();


Schedule::call(function () {
    $timeAccounts = TimeAccount::all();

    foreach ($timeAccounts as $timeAccount) {
        if ($timeAccount->balance > $timeAccount->balance_limit)
            $timeAccount->updateBalance(- ($timeAccount->balance - $timeAccount->balance_limit), 'monthly balance truncation', true);
    }
})->name('monthlyBalanceTruncation')->monthlyOn(
    Organization::getCurrent()->balance_truncation_day,
    '00:00'
);
