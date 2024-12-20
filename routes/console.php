<?php

use App\Models\Absence;
use App\Models\Organization;
use App\Models\User;
use App\Models\UserLeaveDay;
use App\Models\WorkingHoursCalculation;
use App\Models\WorkLog;
use App\Models\WorkLogPatch;
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


Schedule::call(function () {
    dump('start');

    $users = User::with('operatingSite')->get();
    $workLogsToCut = WorkLog::whereBetween('start', [Carbon::yesterday()->startOfDay(), Carbon::yesterday()->endOfDay()])
        ->where('end', null)
        ->latest('start')
        ->get();

    //cut the current active worklog at 23:59:59 to make calculations easier
    foreach ($users as $user) {
        $lastWorkLog = $workLogsToCut->first(fn($w) => $w->user_id == $user->id);

        if ($lastWorkLog && $lastWorkLog->end != null) {
            $user->latestWorkLog->update([
                'end' => Carbon::yesterday()->endOfDay()
            ]);

            WorkLog::create([
                'start' => Carbon::now()->startOfDay(),
                'end' => null,
                'user_id' => $user->id,
                'is_home_office' => $user->latestWorkLog->is_home_office
            ]);
        }
    }

    dump('end of cuts');

    //for each past day where the calculation has not been done yet
    $daysWithoutCalculation = collect(range(1, 30))->map(fn($i) => Carbon::now()->subDays($i)->startOfDay())->filter(
        fn($day) => !WorkingHoursCalculation::where('day', $day)->exists()
    )->reverse();

    foreach ($daysWithoutCalculation as $day) {
        dump('start calculation for ' . $day);
        // save that the calculation has started for this day (if it fails, it will not be started again automatically)
        $workingHourCalculation = WorkingHoursCalculation::create([
            'day' => $day,
            'status' => 'started'
        ]);
        //calculate overtime of $day
        foreach ($users as $user) {
            $currentWorkingHours = $user->userWorkingHoursForDate($day);
            $currentWorkingWeek = $user->userWorkingWeekForDate($day);

            $workingDaysInWeek = $currentWorkingWeek?->numberOfWorkingDays;

            $hasAbsenceForDay = $user->absences()
                ->where('status', 'accepted')
                ->whereDate('start', '<=', $day)
                ->whereDate('end', '>=', $day)->exists();

            $shouldWorkYesterday =
                !$hasAbsenceForDay &&
                $workingDaysInWeek > 0 &&
                $currentWorkingWeek->hasWorkDay($day) &&
                !$user->operatingSite->hasHoliday($day);

            $workLogs = $user->workLogs()->whereNotNull('end')->whereBetween('start', [$day->startOfDay(), $day->endOfDay()])->get();

            $sollStunden = 0;
            $istStunden = 0;
            if ($shouldWorkYesterday && $currentWorkingHours != null) {
                $sollStunden = $currentWorkingHours['weekly_working_hours'] / $workingDaysInWeek;
                $istStunden = $workLogs->sum('duration');
            } else if ($currentWorkingHours != null) {
                $istStunden = max($workLogs->sum('duration') - $currentWorkingHours['weekly_working_hours'] / $workingDaysInWeek, 0);
            }

            $user->defaultTimeAccount()->addBalance($istStunden - $sollStunden, 'Tägliche Überstundenberechnung ' . $day->format('d.m.Y'));

            //TODO: add SWHF's & Nachtschicht, etc.
        }
        dump('end worklog calculations for ' . $day);

        foreach (WorkLogPatch::where('is_accounted', false)->whereDate('accepted_at', $day)->get() as $patch) {
            $patch->accountAsTransaction();
        }
        dump('end patch calculations for ' . $day);

        $workingHourCalculation->update([
            'status' => 'completed'
        ]);
    }
    dump('end');
})->name('dailyWorkLogCut')->dailyAt("00:00");

Schedule::call(function () {
    dump('start');

    foreach (User::all() as $user) {
        $usedDays = $user->absences()
            ->whereHas('absenceType', fn($q) => $q->where('type', 'Urlaub'))
            ->where(
                fn($q) =>
                $q->whereYear('start', '<=', Carbon::now()->subYear()->endOfYear()->year)
                    ->orWhereYear('end', '>=', Carbon::now()->subYear()->startOfYear()->year)
            )
            ->get()
            ->sum('duration');

        $lastYearUserLeaveDays = $user->userLeaveDays()
            ->where('type', 'annual')
            ->whereYear('active_since', Carbon::now()->subYear()->year);

        $previousUserLeaveDays = null;
        if (!Carbon::parse($lastYearUserLeaveDays->whereMonth('active_since', 1)->first()))
            $previousUserLeaveDays = $user->userLeaveDays()
                ->where('type', 'annual')
                ->whereYear('active_since', '<=', Carbon::now()->subYears(2)->year)
                ->latest('active_since')
                ->first();

        $leaveDays = 0;
        for ($i = 1; $i <= 12; $i++) {
            $month = Carbon::now()->subYear()->setMonth($i)->startOfMonth();
            $activeEntry = collect([$lastYearUserLeaveDays, ...$previousUserLeaveDays])
                ->whereDate('active_since', '<=', $month)
                ->latest('active_since')
                ->first();
            $leaveDays += $activeEntry->leave_days / 12;
        }

        $leaveDays += $user->userLeaveDays()
            ->where('type', 'remaining')
            ->whereYear('active_since', Carbon::now()->subYear()->year)->first()->leave_days;

        $newRemainingLeaveDays = $leaveDays - $usedDays;

        UserLeaveDay::create([
            'user_id' => $user->id,
            'leave_days' => $newRemainingLeaveDays,
            'type' => 'remaining',
            'active_since' => Carbon::now()->startOfYear()->subYear()
        ]);
    }

    dump('end');
})->name('yearlyLeaveDaysCalculation')->yearlyOn();
