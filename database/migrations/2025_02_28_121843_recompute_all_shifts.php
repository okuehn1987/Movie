<?php

use App\Models\AbsenceType;
use App\Models\Shift;
use App\Models\User;
use App\Models\WorkLog;
use App\Models\WorkLogPatch;
use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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

        Shift::query()->delete();
        WorkLog::whereId(1642)->delete();

        //optional because of seeded data
        AbsenceType::where('name', 'Abbau Gleitzeitkonto')->where('type', 'andere')->first()?->update([
            'type' => 'Abbau Gleitzeitkonto',
        ]);

        $users = User::with([
            'defaultTimeAccount',
            'operatingSite',
        ])->get();

        foreach ($users as $user) {
            $state = [
                'user' => $user->first_name . ' ' . $user->last_name,
                'balance_before' => $user->defaultTimeAccount->balance
            ];

            $user->defaultTimeAccount->updateQuietly(['balance' => 0]);
            $user->defaultTimeAccount->toTransactions()->delete();
            $user->defaultTimeAccount->fromTransactions()->delete();

            $user->workLogs()->get()->each->updateQuietly(['shift_id' => null]);
            $user->workLogPatches()->get()->each->updateQuietly(['shift_id' => null]);
            $user->shifts()->delete();

            if ($user->first_name . ' ' . $user->last_name == 'Martin Krawtzow') {
                $user->userWorkingHours()->whereNot('id', 28)->delete();
                $user->userWorkingHours()->whereId(28)->update([
                    'active_since' => Carbon::parse('2025-01-15'),
                    'weekly_working_hours' => 18.75
                ]);
            }

            $start = Carbon::parse(max(WorkLog::max('start'), WorkLogPatch::where('status', 'accepted')->max('start')))->subDay();
            $affectedDays =
                collect(
                    range(0, $start->copy()->startOfYear()->diffInDays($start))
                )->map(
                    fn($i) => $start->copy()->subDays($i)->startOfDay()
                )->sort();

            foreach ($affectedDays as $day) {
                if ($day->lt($user->overtime_calculations_start)) continue;
                $user->removeMissingWorkTimeForDate($day);
                if ($user->first_name . ' ' . $user->last_name == 'Steffen Mosch' && $user->getSollsekundenForDate($day) == 38 / 5 * 3600)
                    $user->defaultTimeAccount->addBalance(6 * 60, 'Gutschrift aufgrund fehlerhafter wöchentlicher Arbeitszeit');

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

            $state['balance_after'] = $user->defaultTimeAccount->fresh()->balance;
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
