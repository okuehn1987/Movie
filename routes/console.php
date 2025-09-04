<?php

use App\Models\Absence;
use App\Models\Organization;
use App\Models\Shift;
use App\Models\TimeAccountTransactionChange;
use App\Models\User;
use App\Models\UserLeaveDay;
use App\Models\WorkingHoursCalculation;
use App\Models\WorkLog;
use App\Models\WorkLogPatch;
use Carbon\Carbon;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
            TimeAccountTransactionChange::createFor($timeAccount->addBalance(- ($timeAccount->balance - $timeAccount->balance_limit), 'Monatsabrechnung'), now());
    }
})->name('monthlyBalanceTruncation')->dailyAt("01:00");


Schedule::call(function () {
    $users = User::with(['operatingSite', 'defaultTimeAccount', 'latestWorkLog'])->get();

    //for each past day where the calculation has not been done yet
    $daysWithoutCalculation = collect(range(1, 30))->map(fn($i) => Carbon::now()->subDays($i)->startOfDay())->filter(
        fn($day) => !WorkingHoursCalculation::where('day', $day)->exists()
    )->reverse();

    foreach ($daysWithoutCalculation as $day) {
        // save that the calculation has started for this day (if it fails, it will not be started again automatically)
        $workingHourCalculation = WorkingHoursCalculation::create([
            'day' => $day,
            'status' => 'started'
        ]);
        //calculate overtime of $day
        foreach ($users as $user) {
            if ($day->lt(Carbon::parse($user->overtime_calculations_start))) continue;

            $user->removeMissingWorkTimeForDate($day);
        }

        $workingHourCalculation->update([
            'status' => 'completed'
        ]);
    }
})->name('dailyWorkLogCalculation')->dailyAt("00:00");

Schedule::call(function () {
    foreach (User::with('operatingSite')->get() as $user) {
        $newRemainingLeaveDays = $user->leaveDaysForYear(Carbon::now()->subYear()) - $user->usedLeaveDaysForYear(Carbon::now()->subYear());

        UserLeaveDay::create([
            'user_id' => $user->id,
            'leave_days' => $newRemainingLeaveDays,
            'type' => 'remaining',
            'active_since' => Carbon::now()->startOfYear()
        ]);
    }
})->name('yearlyLeaveDaysCalculation')->yearlyOn($month = 1, $dayOfMonth = 1, $time = '0:0');
