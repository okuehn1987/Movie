<?php

namespace Tests\Feature;

use App\Models\Shift;
use App\Models\User;
use App\Models\WorkLog;
use Carbon\CarbonInterface;
use Exception;
use Tests\TestCase;

class CalculationTest extends TestCase
{
    private $oldUser, $youngUser;
    public function setUp(): void
    {
        parent::setUp();
        $this->travelTo(now()->startOfDay()->addHours(9));
        $this->oldUser = User::whereDate('date_of_birth', '<=', now()->subYears(18))->first();
        $this->youngUser = User::whereDate('date_of_birth', '>', now()->subYears(18))->first();
    }
    public function test_should_only_handle_work_related_models(): void
    {
        $this->expectException(Exception::class);
        Shift::computeAffected(new User());
    }

    public function test_shift_creation(): void
    {
        $workLog = $this->oldUser->workLogs()->create([
            'start' =>  now(),
            'end' =>  now()->addHours(7),
            'status' => 'accepted',
        ]);
        //should be null because Shift::computeAffected() returns without accepted at
        $this->assertNull($workLog->shift);

        $workLog = $this->oldUser->workLogs()->create([
            'start' =>  now()->subDays(2),
            'end' =>  now()->subDay()->subHours(6),
            'status' => 'accepted',
            'accepted_at' =>  now()
        ]);
        $this->assertModelExists($workLog->shift);
        $this->assertTrue($workLog->shift->hasEnded);
        $this->assertEquals((24 - 9) * 3600 + 3 * 3600 - 45 * 60, $this->oldUser->defaultTimeAccount->balance);
    }

    public function test_single_log_states(): void
    {
        //above 18 years old
        $workLog = $this->oldUser->workLogs()->create([
            'start' =>  now(),
            'end' => now()->addHours(6),
            'status' => 'accepted',
            'accepted_at' =>  now()
        ]);
        $this->assertEquals(6 * 3600, $workLog->duration);
        $this->assertEquals(0, $workLog->missingBreakDuration);
        $this->assertEquals(0, $workLog->fresh()->shift->missingBreakDuration());

        $workLog->update([
            'end' => now()->addHours(6)->addMinutes(5),
        ]);
        $this->assertEquals(6 * 3600 + 5 * 60, $workLog->duration);
        $this->assertEquals(5 * 60, $workLog->missingBreakDuration);
        $this->assertEquals(5 * 60, $workLog->fresh()->shift->missingBreakDuration());

        $workLog->update([
            'end' =>  now()->addHours(7),
        ]);
        $this->assertEquals(7 * 3600, $workLog->duration);
        $this->assertEquals(0.25 * 3600, $workLog->missingBreakDuration);
        $this->assertEquals(0.5 * 3600, $workLog->fresh()->shift->missingBreakDuration());


        $workLog->update([
            'end' => now()->addHours(9)->addMinutes(5)
        ]);
        $this->assertEquals(9 * 3600 + 5 * 60, $workLog->duration);
        $this->assertEquals(15 * 60, $workLog->missingBreakDuration);
        $this->assertEquals(35 * 60, $workLog->fresh()->shift->missingBreakDuration());

        $workLog->update([
            'end' => now()->addHours(9)->addMinutes(30)
        ]);
        $this->assertEquals(9 * 3600 + 30 * 60, $workLog->duration);
        $this->assertEquals(15 * 60, $workLog->missingBreakDuration);
        $this->assertEquals(45 * 60, $workLog->fresh()->shift->missingBreakDuration());

        //below 18 years old 
        $workLog = $this->youngUser->workLogs()->create([
            'start' =>  now(),
            'end' => now()->addHours(4)->addMinutes(30),
            'status' => 'accepted',
            'accepted_at' =>  now()
        ]);
        $this->assertEquals(4 * 3600 + 30 * 60, $workLog->duration);
        $this->assertEquals(0, $workLog->missingBreakDuration);
        $this->assertEquals(0, $workLog->fresh()->shift->missingBreakDuration());

        $workLog->update([
            'end' => now()->addHours(4)->addMinutes(35),
        ]);
        $this->assertEquals(4 * 3600 + 35 * 60, $workLog->duration);
        $this->assertEquals(5 * 60, $workLog->missingBreakDuration);
        $this->assertEquals(5 * 60, $workLog->fresh()->shift->missingBreakDuration());

        $workLog->update([
            'end' =>  now()->addHours(5),
        ]);
        $this->assertEquals(5 * 3600, $workLog->duration);
        $this->assertEquals(0.25 * 3600, $workLog->missingBreakDuration);
        $this->assertEquals(0.5 * 3600, $workLog->fresh()->shift->missingBreakDuration());


        $workLog->update([
            'end' => now()->addHours(6)->addMinutes(5)
        ]);
        $this->assertEquals(6 * 3600 + 5 * 60, $workLog->duration);
        $this->assertEquals(15 * 60, $workLog->missingBreakDuration);
        $this->assertEquals(35 * 60, $workLog->fresh()->shift->missingBreakDuration());

        $workLog->update([
            'end' => now()->addHours(6)->addMinutes(30)
        ]);
        $this->assertEquals(6 * 3600 + 30 * 60, $workLog->duration);
        $this->assertEquals(15 * 60, $workLog->missingBreakDuration);
        $this->assertEquals(1 * 3600, $workLog->fresh()->shift->missingBreakDuration());
    }
    public function test_extend_shift(): void
    {
        $this->oldUser->workLogs()->create([
            'start' =>  now(),
            'end' =>  now()->addHours(7),
            'status' => 'accepted',
            'accepted_at' =>  now()
        ]);

        $nextStart =  now()->addHours(7.5);
        $nextWorkLog = $this->oldUser->workLogs()->create([
            'start' => $nextStart,
            'end' => $nextStart->copy()->addHours(3),
            'status' => 'accepted',
            'accepted_at' => $nextStart
        ]);

        // ist = 7h + 3h + 30min pause
        $this->assertEquals(10.5 * 3600, $nextWorkLog->shift->duration);
        // ist > 9 also 45min notwendige pause nicht nur 30min
        $this->assertEquals(0.25 * 3600, $nextWorkLog->shift->missingBreakDuration());
    }

    public function test_no_balance_change(): void
    {
        $workLog = $this->oldUser->workLogs()->create([
            'start' =>  now(),
            'end' =>  now()->addHours(8),
            'status' => 'accepted',
            'accepted_at' =>  now()
        ]);
        // soll = 7:30
        $this->assertEquals(7 * 3600 + 30 * 60, $this->oldUser->getSollSekundenForDate(now()));
        // ist = 8:00 
        $this->assertEquals(8 * 3600, $workLog->duration);
        // 15min abzug wegen mehr als 6 stunden am stück 
        $this->assertEquals(15 * 60, $workLog->missingBreakDuration);
        // 30min fehlen für ganze schicht
        $this->assertEquals(30 * 60, $workLog->shift->missingBreakDuration());
        // 7:30 == 8:00 - 30min 
        $this->assertEquals(0, $this->oldUser->defaultTimeAccount->fresh()->balance);
    }

    public function test_balance_change_with_missing_break_subtraction(): void
    {
        $this->oldUser->workLogs()->create([
            'start' =>  now(),
            'end' =>  now()->addHours(7),
            'status' => 'accepted',
            'accepted_at' => now()
        ]);

        $this->assertEquals(-0.5 * 3600, $this->oldUser->defaultTimeAccount->fresh()->balance);

        $this->oldUser->workLogs()->create([
            'start' => now()->addHours(7.5),
            'end' => now()->addHours(8.5),
            'status' => 'accepted',
            'accepted_at' => now()->addHours(7.5)
        ]);

        //ist = 8:00 ; soll = 7:30 ; 15min abzug wegen mehr als 6 stunden am stück
        $this->assertEquals(0.25 * 3600, $this->oldUser->defaultTimeAccount->fresh()->balance);
    }

    public function test_balance_change_for_correct_shift(): void
    {
        $this->oldUser->workLogs()->create([
            'start' =>  now(),
            'end' =>  now()->addHours(6),
            'status' => 'accepted',
            'accepted_at' =>  now()
        ]);

        $nextStart =  now()->addHours(6.5);
        $this->oldUser->workLogs()->create([
            'start' => $nextStart,
            'end' => $nextStart->copy()->addHours(2),
            'status' => 'accepted',
            'accepted_at' => $nextStart
        ]);

        //ist = 8:00 ; soll = 7:30
        $this->assertEquals(0.5 * 3600, $this->oldUser->defaultTimeAccount->fresh()->balance);
    }

    public function test_complex_shift_with_patches()
    {
        $this->oldUser->workLogs()->create([
            'start' =>  now(),
            'end' =>  now()->addHours(6),
            'status' => 'accepted',
            'accepted_at' =>  now()
        ]);
        $nextStart = now()->addHours(6.5);
        $second = $this->oldUser->workLogs()->create([
            'start' =>  $nextStart,
            'end' =>  $nextStart->copy()->addHours(2),
            'status' => 'accepted',
            'accepted_at' =>  $nextStart
        ]);
        $this->assertEquals(30 * 60, $this->oldUser->defaultTimeAccount->fresh()->balance);
        $this->assertEquals(0, $second->shift->missingBreakDuration());

        $this->oldUser->workLogPatches()->create([
            'start' =>  $nextStart,
            'end' =>  $nextStart->copy()->addHours(3),
            'status' => 'accepted',
            'accepted_at' =>  $nextStart,
            'work_log_id' => $second->id,
        ]);
        $this->assertEquals(0, $second->shift->missingBreakDuration());
        $this->assertEquals(1 * 3600 + 30 * 60, $this->oldUser->defaultTimeAccount->fresh()->balance);

        $this->oldUser->workLogPatches()->create([
            'start' =>  $nextStart,
            'end' =>  $nextStart->copy()->addHours(4),
            'status' => 'accepted',
            'accepted_at' =>  $nextStart->copy()->addMinutes(1),
            'work_log_id' => $second->id,
        ]);
        $this->assertEquals(2 * 3600 + 15 * 60, $this->oldUser->defaultTimeAccount->fresh()->balance);
        $this->assertEquals(15 * 60, $second->fresh()->shift->missingBreakDuration());
    }

    public function test_absence_before_creating_log()
    {
        $this->oldUser->absences()->create([
            'start' =>  now(),
            'end' =>  now(),
            'status' => 'accepted',
            'accepted_at' =>  now(),
            'absence_type_id' => 1,
        ]);

        $this->oldUser->workLogs()->create([
            'start' =>  now(),
            'end' =>  now()->addHours(6),
            'status' => 'accepted',
            'accepted_at' =>  now()
        ]);
        $this->assertEquals(0, $this->oldUser->defaultTimeAccount->fresh()->balance);

        $nextStart = now()->addHours(6.5);
        $this->oldUser->workLogs()->create([
            'start' =>  $nextStart,
            'end' =>  $nextStart->copy()->addHours(3),
            'status' => 'accepted',
            'accepted_at' =>  $nextStart
        ]);
        $this->assertEquals(1 * 3600 + 30 * 60, $this->oldUser->defaultTimeAccount->fresh()->balance);
    }

    public function test_absence_after_creating_log()
    {
        $this->oldUser->workLogs()->create([
            'start' =>  now(),
            'end' =>  now()->addHours(6),
            'status' => 'accepted',
            'accepted_at' =>  now()
        ]);
        $this->assertEquals(0, $this->oldUser->defaultTimeAccount->balance);

        $nextStart = now()->addHours(6.5);
        $this->oldUser->workLogs()->create([
            'start' =>  $nextStart,
            'end' =>  $nextStart->copy()->addHours(3),
            'status' => 'accepted',
            'accepted_at' =>  $nextStart
        ]);

        $this->oldUser->absences()->create([
            'start' =>  now(),
            'end' =>  now(),
            'status' => 'accepted',
            'accepted_at' =>  now(),
            'absence_type_id' => 1,
        ]);

        $this->assertEquals(1 * 3600 + 30 * 60, $this->oldUser->defaultTimeAccount->fresh()->balance);
    }

    public function test_restore_missing_time_after_absence()
    {
        $this->oldUser->removeMissingWorkTimeForDate(now()->subday());
        $this->oldUser->workLogs()->create([
            'start' =>  now()->subday(),
            'end' =>  now()->subday()->addHours(6),
            'status' => 'accepted',
            'accepted_at' =>  now()->subday()
        ]);
        $this->assertEquals(- (1 * 3600 + 30 * 60), $this->oldUser->defaultTimeAccount->fresh()->balance);

        $this->oldUser->absences()->create([
            'start' =>  now()->subday(),
            'end' =>  now()->subday(),
            'status' => 'accepted',
            'accepted_at' =>  now(),
            'absence_type_id' => 1,
        ]);

        $this->assertEquals(0, $this->oldUser->defaultTimeAccount->fresh()->balance);
    }

    public function test_patch_next_day()
    {
        $this->oldUser->removeMissingWorkTimeForDate(now()->subday());
        $this->oldUser->workLogs()->create([
            'start' =>  now()->subday(),
            'end' =>  now()->subday()->addHours(6),
            'status' => 'accepted',
            'accepted_at' =>  now()->subday()
        ]);

        $this->assertEquals(- (1 * 3600 + 30 * 60), $this->oldUser->defaultTimeAccount->fresh()->balance);

        //add workLog next day because you forgot to log in again
        $nextStart =  now()->subday()->addHours(6.5);
        $second = $this->oldUser->workLogs()->create([
            'start' =>   $nextStart,
            'end' =>   $nextStart->copy()->addHours(3),
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);
        // ist for shift = 9:00 ; soll = 7:30
        $this->assertEquals(1 * 3600 + 30 * 60, $this->oldUser->defaultTimeAccount->fresh()->balance);

        //patch second
        $this->oldUser->workLogPatches()->create([
            'start' =>   $nextStart,
            'end' =>   $nextStart->copy()->addHours(3)->addMinutes(30),
            'status' => 'accepted',
            'accepted_at' => now()->addMinutes(30),
            'work_log_id' => $second->id
        ]);
        // + 30min work time ; forgot 15min break
        $this->assertEquals(1 * 3600 + 45 * 60, $this->oldUser->defaultTimeAccount->fresh()->balance);
    }

    public function test_correcting_extreme_workLog()
    {

        for ($offset = 1; $offset <= 10; $offset++) {
            $this->oldUser->removeMissingWorkTimeForDate(now()->subDays($offset));
        }

        $this->oldUser->workLogs()->create([
            'start' =>  now()->subDays(10),
            'end' =>  now()->subday(),
            'status' => 'accepted',
            'accepted_at' =>  now()->subDays(10),
        ]);


        // dump($this->oldUser->getWorkDurationForDate(now()->subDay()));

        dump($this->oldUser->defaultTimeAccount->toTransactions->map(fn($t) => collect($t)->only(['amount', 'description'])->toArray()));
        dump($this->oldUser->workLogs->fresh()->map(fn($w) => collect($w)->only(['start', 'end'])->toArray()));

        $this->assertEquals(
            9 * 24 * 3600 - 10 * 15 * 60 - 7.5 * 10 * 3600,
            $this->oldUser->defaultTimeAccount->fresh()->balance
        );
    }
}
