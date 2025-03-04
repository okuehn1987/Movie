<?php

use App\Models\TimeAccountTransaction;
use App\Models\User;
use App\Models\WorkLogPatch;
use Carbon\Carbon;
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
        Schema::table('work_logs', function (Blueprint $table) {
            $table->dropForeign(['shift_id']);
            $table->dropColumn('shift_id');
            $table->foreignId('shift_id')->nullable()->constrained()->onDelete('set null');
        });

        $users = User::with([
            'defaultTimeAccount' => [
                'toTransactions',
                'fromTransactions'
            ],
            'workLogs',
            'workLogPatches',
            'operatingSite'
        ])->get();

        foreach ($users as $user) {
            $state = [
                'user' => $user->first_name . ' ' . $user->last_name,
                'balance_before' => $user->defaultTimeAccount->balance
            ];

            $user->defaultTimeAccount->updateQuietly(['balance' => 0]);
            $user->defaultTimeAccount->toTransactions->each->forceDelete();
            $user->defaultTimeAccount->fromTransactions->each->forceDelete();

            $workLogs = $user->workLogs()->orderBy('start')->with(['user', 'currentAcceptedPatch.user'])->get();
            $entries = $workLogs->map(fn($w) => $w->currentAcceptedPatch ?? $w);

            $start = Carbon::parse('2025-02-27');
            $affectedDays = collect(
                range(1, $start->copy()->startOfYear()->diffInDays($start))
            )->map(
                fn($i) => $start->copy()->subDays($i)->startOfDay()
            )->sort();

            foreach ($affectedDays as $day) {
                if ($day < $user->overtime_calculations_start) continue;
                $entriesForDay = $entries->filter(
                    fn($e) => Carbon::parse($e->start)->between(
                        $day->copy()->startOfDay(),
                        $day->copy()->endOfDay()
                    )
                );
                $entriesForDay->each(fn($e) => $e->update([
                    'status' => 'accepted',
                    'accepted_at' => $e->start,
                ]));

                $user->defaultTimeAccount->addBalance(
                    min(0, $entriesForDay->sum('duration') - $user->getSollSekundenForDate($day)),
                    $day->format('d.m.Y')
                );
            }

            $state['balance_after'] = $user->fresh('defaultTimeAccount')->defaultTimeAccount->balance;
            dump($state);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
