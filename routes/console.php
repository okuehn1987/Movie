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
    $organizations = Organization::where('balance_truncation_day', Carbon::now()->day)->get();
    $timeAccounts = TimeAccount::whereHas('user', function ($query) use ($organizations) {
        $query->whereHas(
            'operatingSite',
            fn($q) =>
            $q->whereIn('organization_id', $organizations->map(fn($o) => $o->id))
        );
    })->get();

    foreach ($timeAccounts as $timeAccount) {
        if ($timeAccount->balance > $timeAccount->balance_limit)
            $timeAccount->updateBalance(- ($timeAccount->balance - $timeAccount->balance_limit), 'monthly balance truncation', true);
    }
})->name('monthlyBalanceTruncation')->daily();
