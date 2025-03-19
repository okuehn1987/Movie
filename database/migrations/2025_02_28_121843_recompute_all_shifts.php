<?php

use App\Models\AbsenceType;
use App\Models\Shift;
use App\Models\User;
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

        AbsenceType::whereId(10)->first()->update([
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

            if ($user->first_name . ' ' . $user->last_name == 'Martin Krawtzow')
                $user->defaultTimeAccount->addBalance(30 * 3600, 'Ausgleich der Stunden vor dem 15.1.25 weil Martin vorher nicht Herta benutzt hat ...');

            $start = now();
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
