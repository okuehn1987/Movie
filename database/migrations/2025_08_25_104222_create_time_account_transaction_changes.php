<?php

use App\Models\Shift;
use App\Models\TimeAccountTransactionChange;
use App\Models\User;
use App\Models\UserWorkingHour;
use App\Models\WorkLog;
use App\Models\WorkLogPatch;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('time_account_transaction_changes', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('time_account_transaction_id')->constrained()->onDelete('cascade')->index('time_account_transaction_change_transaction_id');
            $table->date('date');
            $table->integer('amount');
        });

        // DB::beginTransaction();

        Shift::query()->delete();

        $users = User::with([
            'defaultTimeAccount',
            'operatingSite',
        ])->get();


        $start = Carbon::parse(max(WorkLog::max('start'), WorkLogPatch::where('status', 'accepted')->max('start')));

        UserWorkingHour::where('weekly_working_hours', 38)->where('user_id', 3)->update([
            'weekly_working_hours' => 37.5
        ]);

        User::whereIn('id', [1, 2, 16, 17])->each(function ($u) {
            $u->userWorkingHours()->first()->update([
                'active_since' => Carbon::parse('2025-01-01'),
                'weekly_working_hours' => 0
            ]);
        });

        foreach ($users as $user) {
            $user->defaultTimeAccount->updateQuietly(['balance' => 0]);
            $user->defaultTimeAccount->toTransactions()->delete();
            $user->defaultTimeAccount->fromTransactions()->delete();

            $user->workLogs()->get()->each->updateQuietly(['shift_id' => null]);
            $user->workLogPatches()->get()->each->updateQuietly(['shift_id' => null]);

            $affectedDays =
                collect(
                    range(0, $start->copy()->startOfYear()->diffInDays($start))
                )->map(
                    fn($i) => $start->copy()->subDays($i)->startOfDay()
                )->sort();


            foreach ($affectedDays as $day) {

                if ($user->id == 3 && collect(['2025-08-06', '2025-07-08', '2025-06-16'])->contains($day->format('Y-m-d'))) {
                    TimeAccountTransactionChange::createFor($user->defaultTimeAccount->addBalance(60 * 30, "Einkaufen"), $day);
                }

                if ($day->lt($user->overtime_calculations_start)) continue;
                if (!$day->isSameDay(now())) $user->removeMissingWorkTimeForDate($day);

                $workLogs = $user->workLogs()
                    ->whereBetween('start', [
                        $day->copy()->startOfDay(),
                        $day->copy()->endOfDay()
                    ])
                    ->doesntHave('currentAcceptedPatch')
                    ->with(['user'])
                    ->get();
                $workLogPatches = $user->workLogPatches()
                    ->whereBetween('start', [
                        $day->copy()->startOfDay(),
                        $day->copy()->endOfDay()
                    ])
                    ->where('status', 'accepted')
                    ->with(['user', 'log.currentAcceptedPatch'])
                    ->get()
                    ->filter(fn($p) => $p->log->currentAcceptedPatch->is($p));

                $entriesForDay = collect($workLogs)->merge($workLogPatches)->sortBy(fn($e) => $e->accepted_at ?? $e->start);

                $entriesForDay->each(fn($e) => $e->update([
                    'status' => 'accepted',
                    'end' => $e->end ? $e->end : (now()->isSameDay($e->start) ? null : $e->start),
                    'accepted_at' => $e->accepted_at ?? $e->start,
                ]));
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_account_transaction_changes');
    }
};
