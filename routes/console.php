<?php

use App\Models\Absence;
use App\Models\Organization;
use App\Models\Shift;
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
            $timeAccount->addBalance(- ($timeAccount->balance - $timeAccount->balance_limit), 'Monatsabrechnung');
    }
})->name('monthlyBalanceTruncation')->dailyAt("01:00");


Schedule::call(function () {
    Log::info('Daily worklog calculation started');

    $users = User::with(['operatingSite', 'defaultTimeAccount'])->get();
    $workLogsToCut = WorkLog::whereBetween('start', [Carbon::yesterday()->startOfDay(), Carbon::yesterday()->endOfDay()])
        ->whereNull('end')
        ->orderBy('start', 'asc')
        ->get();

    //cut the current active worklog at 23:59:59 to make calculations easier
    foreach ($users as $user) {
        $lastWorkLog = $workLogsToCut->first(fn($w) => $w->user_id == $user->id);

        if ($lastWorkLog) {
            $lastWorkLog->update([
                'end' => Carbon::yesterday()->endOfDay()
            ]);

            WorkLog::create([
                'start' => Carbon::now()->startOfDay(),
                'end' => null,
                'user_id' => $user->id,
                'is_home_office' => $lastWorkLog->is_home_office
            ]);
        }
    }

    dump('end of cuts');

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

            $currentWorkingHours = $user->userWorkingHoursForDate($day);
            $currentWorkingWeek = $user->userWorkingWeekForDate($day);
            if (!$currentWorkingHours || !$currentWorkingWeek) continue;

            $hasAbsenceForDay = $user->absences()
                ->where('status', 'accepted')
                ->whereDate('start', '<=', $day)
                ->whereDate('end', '>=', $day)->exists();

            $shouldWorkYesterday =
                $currentWorkingWeek->hasWorkDay($day) &&
                !$user->operatingSite->hasHoliday($day);

            $workLogsForDay = $user->workLogs()
                ->whereNotNull('end')
                ->whereBetween('start', [$day->copy()->startOfDay(), $day->copy()->endOfDay()])
                ->get();

            $istSekunden = $workLogsForDay->sum('duration');
            $sollSekunden = $user->getSollsekundenForDate($day);

            if (!$shouldWorkYesterday) {
                $sollSekunden = 0;
            } else if ($hasAbsenceForDay) {
                $istSekunden = max($istSekunden - $sollSekunden, 0);
                $sollSekunden = 0;
            }
            $user->defaultTimeAccount->addBalance($istSekunden - $sollSekunden, 'Tägliche Überstundenberechnung ' . $day->format('d.m.Y'));

            //TODO: add SWHF's & Nachtschicht, etc.
        }

        foreach (WorkLogPatch::where('is_accounted', false)->whereDate('accepted_at', $day)->get() as $patch) {
            $patch->accountAsTransaction();
        }

        $workingHourCalculation->update([
            'status' => 'completed'
        ]);
    }
    Log::info('Daily worklog calculation end');
})->name('dailyWorkLogCalculation')->dailyAt("00:00");

Schedule::call(function () {
    foreach (Shift::where('is_accounted', false)->with('user')->get()->filter(fn($shift) => $shift->has_ended) as $shift) {
        $shift->accountRequiredBreakAsTransaction();
    }
})->name('shiftBreakCalculation')->dailyAt("09:00");

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
