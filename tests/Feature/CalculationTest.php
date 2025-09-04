<?php

namespace Tests\Feature;

use App\Models\AbsenceType;
use App\Models\Shift;
use App\Models\User;
use App\Models\WorkLog;
use Exception;
use Tests\TestCase;

class CalculationTest extends TestCase
{
    private $oldUser, $youngUser, $tim;
    public function setUp(): void
    {
        parent::setUp();
        $this->travelTo(now()->startOfDay()->addHours(9));
        $this->oldUser = User::whereDate('date_of_birth', '<=', now()->subYears(18))->first();
        $this->youngUser = User::whereDate('date_of_birth', '>', now()->subYears(18))->first();
        $this->tim = User::where('first_name', 'tim')->first();
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
        $this->assertNull($workLog->shift_id);

        $workLog = $this->oldUser->workLogs()->create([
            'start' =>  now()->subDays(2),
            'end' =>  now()->subDay()->subHours(6),
            'status' => 'accepted',
            'accepted_at' =>  now()
        ]);


        $this->assertModelExists($workLog->fresh()->shift);
        $this->assertTrue($workLog->fresh()->shift->hasEnded);
        $this->assertEquals((24 - 9) * 3600 + 3 * 3600 - 45 * 60, $this->oldUser->defaultTimeAccount->fresh()->balance);
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
        $this->assertEquals(5 * 60, $workLog->fresh()->missingBreakDuration);
        $this->assertEquals(5 * 60, $workLog->fresh()->shift->missingBreakDuration());

        $workLog->update([
            'end' =>  now()->addHours(7),
        ]);
        $this->assertEquals(7 * 3600, $workLog->duration);
        $this->assertEquals(0.25 * 3600, $workLog->fresh()->missingBreakDuration);
        $this->assertEquals(0.5 * 3600, $workLog->fresh()->shift->missingBreakDuration());


        $workLog->update([
            'end' => now()->addHours(9)->addMinutes(5)
        ]);
        $this->assertEquals(9 * 3600 + 5 * 60, $workLog->duration);
        $this->assertEquals(15 * 60, $workLog->fresh()->missingBreakDuration);
        $this->assertEquals(35 * 60, $workLog->fresh()->shift->missingBreakDuration());

        $workLog->update([
            'end' => now()->addHours(9)->addMinutes(30)
        ]);
        $this->assertEquals(9 * 3600 + 30 * 60, $workLog->duration);
        $this->assertEquals(15 * 60, $workLog->fresh()->missingBreakDuration);
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
        $this->assertEquals(5 * 60, $workLog->fresh()->missingBreakDuration);
        $this->assertEquals(5 * 60, $workLog->fresh()->shift->missingBreakDuration());

        $workLog->update([
            'end' =>  now()->addHours(5),
        ]);
        $this->assertEquals(5 * 3600, $workLog->duration);
        $this->assertEquals(0.25 * 3600, $workLog->fresh()->missingBreakDuration);
        $this->assertEquals(0.5 * 3600, $workLog->fresh()->shift->missingBreakDuration());


        $workLog->update([
            'end' => now()->addHours(6)->addMinutes(5)
        ]);
        $this->assertEquals(6 * 3600 + 5 * 60, $workLog->duration);
        $this->assertEquals(15 * 60, $workLog->fresh()->missingBreakDuration);
        $this->assertEquals(35 * 60, $workLog->fresh()->shift->missingBreakDuration());

        $workLog->update([
            'end' => now()->addHours(6)->addMinutes(30)
        ]);
        $this->assertEquals(6 * 3600 + 30 * 60, $workLog->duration);
        $this->assertEquals(15 * 60, $workLog->fresh()->missingBreakDuration);
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
        $holidays = 0;
        for ($offset = 1; $offset <= 10; $offset++) {
            if ($this->oldUser->operatingSite->hasHoliday(now()->subDays($offset))) $holidays++;
            $this->oldUser->removeMissingWorkTimeForDate(now()->subDays($offset));
        }

        $this->oldUser->workLogs()->create([
            'start' =>  now()->subDays(10),
            'end' =>  now()->subday(),
            'status' => 'accepted',
            'accepted_at' =>  now()->subDays(10),
        ]);

        $this->assertEquals(
            9 * 24 * 3600 - 10 * 15 * 60 - 7.5 * (10 - $holidays) * 3600,
            $this->oldUser->defaultTimeAccount->fresh()->balance
        );
    }

    public function test_overlapping_entries()
    {
        $hasHoliday = $this->oldUser->operatingSite->hasHoliday(now()->subDays(10));
        $this->oldUser->removeMissingWorkTimeForDate(now()->subDays(10));

        $this->assertEquals(
            $hasHoliday  ? 0 : -1 * (7 * 3600 + 30 * 60),
            $this->oldUser->defaultTimeAccount->fresh()->balance
        );

        $workLog1 = $this->oldUser->workLogs()->createQuietly([
            'start' =>  now()->subDays(10),
            'end' =>  now()->subDays(10)->addHours(5),
            'status' => 'accepted',
            'accepted_at' =>  now()->subDays(10),
        ]);
        $this->oldUser->workLogPatches()->create([
            'start' =>  now()->subDays(10),
            'end' =>  now()->subDays(10)->addHours(5),
            'status' => 'accepted',
            'accepted_at' =>  now()->subDays(10),
            'work_log_id' => $workLog1->id
        ]);

        $this->assertEquals(
            -1 * (2 * 3600 + 30 * 60),
            $this->oldUser->defaultTimeAccount->fresh()->balance
        );

        $workLog2 = $this->oldUser->workLogs()->createQuietly([
            'start' =>  now()->subDays(10),
            'end' =>  now()->subDays(10)->addHours(5),
            'status' => 'accepted',
            'accepted_at' =>  now()->subDays(10),
        ]);
        $this->oldUser->workLogPatches()->create([
            'start' =>  now()->subDays(10),
            'end' =>  now()->subDays(10)->addHours(5),
            'status' => 'accepted',
            'accepted_at' =>  now()->subDays(10),
            'work_log_id' => $workLog2->id
        ]);

        $this->oldUser->workLogs()->create([
            'start' =>  now()->subDays(10)->addHours(6),
            'end' =>  now()->subDays(10)->addHours(7),
            'status' => 'accepted',
            'accepted_at' =>  now()->addHours(6),
        ]);

        $this->assertEquals(
            -1 * (1 * 3600 + 30 * 60),
            $this->oldUser->defaultTimeAccount->fresh()->balance
        );
    }

    public function test_restore_time_with_absences_with_breaks()
    {
        $date = now()->subDays(1);
        $this->oldUser->removeMissingWorkTimeForDate($date);

        $workLog1 = $this->oldUser->workLogs()->create([
            'start' =>  $date,
            'end' =>  $date->copy()->addHours(4)->addMinutes(11)->addSeconds(49),
            'status' => 'accepted',
            'accepted_at' =>  $date,
        ]);

        $nextStart = $date->copy()->addHours(4)->addMinutes(11)->addSeconds(49)->addMinutes(15)->addSeconds(11);

        $workLog2 = $this->oldUser->workLogs()->create([
            'start' =>   $nextStart,
            'end' =>   $nextStart->copy()->addHours(2)->addMinutes(8)->addSeconds(54),
            'status' => 'accepted',
            'accepted_at' =>   $nextStart,
        ]);

        $this->oldUser->absences()->create([
            'start' =>  $date,
            'end' =>  $date,
            'status' => 'accepted',
            'accepted_at' =>  now(),
            'absence_type_id' => 9,
        ]);

        $this->assertEquals(0, $this->oldUser->defaultTimeAccount->fresh()->balance);
    }
    public function test_restore_time_with_absences_with_breaks_2()
    {
        $date = now()->subDays(1);
        $this->oldUser->removeMissingWorkTimeForDate($date);

        $workLog1 = $this->oldUser->workLogs()->create([
            'start' =>  $date,
            'end' =>  $date->copy()->addHours(6)->addMinutes(30),
            'status' => 'accepted',
            'accepted_at' =>  $date,
        ]);

        $nextStart = $date->copy()->addHours(6)->addMinutes(30)->addMinutes(5);

        $workLog2 = $this->oldUser->workLogs()->create([
            'start' =>   $nextStart,
            'end' =>   $nextStart->copy()->addHours(1),
            'status' => 'accepted',
            'accepted_at' =>   $nextStart,
        ]);

        $this->oldUser->absences()->create([
            'start' =>  $date,
            'end' =>  $date,
            'status' => 'accepted',
            'accepted_at' =>  now(),
            'absence_type_id' => 9, //AU
        ]);

        $this->assertEquals(-15 * 60, $this->oldUser->defaultTimeAccount->fresh()->balance);
    }

    public function test_AbbauGleitzeitkonto()
    {
        $absenceType = AbsenceType::where('type', 'Abbau Gleitzeitkonto')->inOrganization()->first();

        $date = now()->subDays(1);
        $this->oldUser->removeMissingWorkTimeForDate($date);

        $this->assertEquals(-7 * 3600 - 30 * 60, $this->oldUser->defaultTimeAccount->fresh()->balance);

        $this->oldUser->absences()->create([
            'start' =>  $date,
            'end' =>  $date,
            'status' => 'accepted',
            'accepted_at' =>  now(),
            'absence_type_id' => $absenceType->id,
        ]);

        $this->assertEquals(-7 * 3600 - 30 * 60, $this->oldUser->defaultTimeAccount->fresh()->balance);
    }

    public function test_futureAbsence()
    {
        $this->oldUser->absences()->create([
            'start' =>  now()->addDay(),
            'end' =>  now()->addDay()->addHours(5),
            'status' => 'accepted',
            'accepted_at' =>  now(),
            'absence_type_id' => 1,
        ]);

        $this->assertEquals(0, $this->oldUser->defaultTimeAccount->fresh()->balance);
    }

    public function test_delete_workLog()
    {
        $workLog = $this->oldUser->workLogs()->create([
            'start' =>  now(),
            'end' =>  now()->addHours(9),
            'status' => 'accepted',
            'accepted_at' =>  now()
        ]);
        $this->assertEquals(1 * 3600, $this->oldUser->defaultTimeAccount->fresh()->balance);

        $workLog->fresh()->delete();
        $this->assertEquals(0, $this->oldUser->defaultTimeAccount->fresh()->balance);
    }

    public function test_delete_workLog_with_overtime()
    {
        $workLog = $this->oldUser->workLogs()->create([
            'start' =>  now(),
            'end' =>  now()->addHours(6),
            'status' => 'accepted',
            'accepted_at' =>  now()
        ]);
        $this->assertEquals(0, $this->oldUser->defaultTimeAccount->fresh()->balance);

        $nextStart = now()->addHours(6.5);
        $nextWorkLog = $this->oldUser->workLogs()->create([
            'start' =>  $nextStart,
            'end' =>  $nextStart->copy()->addHours(3),
            'status' => 'accepted',
            'accepted_at' =>  now()
        ]);

        $this->assertEquals(1.5 * 3600, $this->oldUser->defaultTimeAccount->fresh()->balance);

        // $workLog->fresh()->delete();
        $nextWorkLog->fresh()->delete();

        $this->assertEquals(0, $this->oldUser->defaultTimeAccount->fresh()->balance);
    }

    public function test_delete_absence()
    {
        $date = now()->subDays(6);
        $this->oldUser->removeMissingWorkTimeForDate($date);

        $workLog = $this->oldUser->workLogs()->create([
            'start' =>  $date,
            'end' =>  $date->copy()->addHours(6),
            'status' => 'accepted',
            'accepted_at' => $date
        ]);

        $this->assertEquals(- (1 * 3600 + 30 * 60), $this->oldUser->defaultTimeAccount->fresh()->balance);

        $absence = $this->oldUser->absences()->create([
            'start' =>  $date,
            'end' =>  $date,
            'status' => 'accepted',
            'accepted_at' =>  now(),
            'absence_type_id' => 10,
        ]);

        $this->assertEquals(0, $this->oldUser->defaultTimeAccount->fresh()->balance);

        $absence->fresh()->delete();

        $this->assertEquals(- (1 * 3600 + 30 * 60), $this->oldUser->defaultTimeAccount->fresh()->balance);
    }

    public function test_delete_past_absence()
    {
        $date = now()->subDays(7);
        $this->oldUser->removeMissingWorkTimeForDate($date);

        $absence = $this->oldUser->absences()->create([
            'start' =>  $date,
            'end' =>  $date,
            'status' => 'accepted',
            'accepted_at' =>  now(),
            'absence_type_id' => 1,
        ]);

        $this->assertEquals(0, $this->oldUser->defaultTimeAccount->fresh()->balance);

        $absence->fresh()->delete();

        $this->assertEquals(-7 * 3600 - 30 * 60, $this->oldUser->defaultTimeAccount->fresh()->balance);
    }

    public function test_patching_splitted_logs()
    {
        $this->travelTo('2025-08-11 13:01:43');

        $this->tim->workLogs()->create([
            'start' =>  '2025-08-11 07:58:39',
            'end' =>  '2025-08-11 13:01:43',
            'status' => 'accepted',
            'accepted_at' => '2025-08-11 13:25:23'
        ]);

        $this->tim->removeMissingWorkTimeForDate(now());
        $this->travelTo('2025-08-12 8:00:00');

        $workLog = $this->tim->workLogs()->create([
            'start' =>  '2025-08-11 13:25:23',
            'end' =>  '2025-08-12 08:00:47',
            'status' => 'accepted',
            'accepted_at' => '2025-08-12 08:00:47'
        ]);
        $this->tim->workLogs()->create([
            'start' =>  '2025-08-12 08:01:09',
            'end' =>  null,
            'status' => 'accepted',
            'accepted_at' => '2025-08-12 08:01:09'
        ]);

        $splittedWorkLog = WorkLog::where('start', '2025-08-12 00:00:00')
            ->where('end', '2025-08-12 08:00:47')
            ->first();

        $this->tim->workLogPatches()->create([
            'start' =>  '2025-08-11 13:25:23',
            'end' =>  '2025-08-11 17:00:59',
            'status' => 'accepted',
            'accepted_at' => '2025-08-12 08:40:35',
            'work_log_id' => $workLog->id,
        ]);
        $this->tim->workLogPatches()->create([
            'start' =>  '2025-08-12 08:00:00',
            'end' =>  '2025-08-12 08:00:47',
            'status' => 'accepted',
            'accepted_at' => '2025-08-12 08:40:35',
            'work_log_id' => $splittedWorkLog->id,
        ]);

        $this->assertEquals(3740, $this->tim->defaultTimeAccount->fresh()->balance);
    }

    public function test_patching_running_shift()
    {
        $this->travelTo('2025-07-28 17:00:00');

        $log = $this->tim->workLogs()->create([
            'start' =>  '2025-07-28 16:55:21',
            'end' =>  '2025-07-28 16:56:21',
            'status' => 'accepted',
            'accepted_at' => '2025-07-28 16:55:21'
        ]);

        $patch = $this->tim->workLogPatches()->create([
            'start' =>  '2025-07-28 08:50:21',
            'end' =>  '2025-07-28 16:56:21',
            'status' => 'created',
            'work_log_id' => $log->id,
        ]);

        $workLog = $this->tim->workLogs()->create([
            'start' =>  '2025-07-28 16:56:22',
            'end' =>  null,
            'status' => 'accepted',
            'accepted_at' => '2025-07-28 16:56:22'
        ]);

        $patch->accept();

        $workLog->update([
            'end' =>  '2025-07-28 17:03:01',
            'accepted_at' => '2025-07-28 17:03:01'
        ]);

        $this->assertEquals(12 * 60 + 40, $this->tim->defaultTimeAccount->fresh()->balance);
    }

    // public function test_missing_break_in_absence()
    // {
    //  TODO: martin break 16.1.25 && 17.1.25
    // }

    // public function testWeirdShit()
    // {
    //     $this->travelTo('2025-06-20 13:15:02');

    //     $log = $this->tim->workLogs()->create([
    //         'start' =>  '2025-06-20 13:15:02',
    //         'end' =>  '2025-06-20 13:15:04',
    //         'status' => 'accepted',
    //         'accepted_at' =>  '2025-06-20 13:15:02',
    //     ]);
    //     $this->tim->workLogPatches()->create([
    //         'start' =>  '2025-06-20 08:40:02',
    //         'end' =>  '2025-06-20 13:15:04',
    //         'status' => 'accepted',
    //         'accepted_at' =>  '2025-06-23 09:01:18',
    //         'work_log_id' => $log->id,
    //     ]);
    //     $log2 = $this->tim->workLogs()->createQuietly([
    //         'start' => '2025-06-20 13:53:54',
    //         'end' =>  null,
    //         'status' => 'accepted',
    //         'accepted_at' =>  '2025-06-20 13:53:54',
    //     ]);

    //     $this->tim->removeMissingWorkTimeForDate(now());

    //     $this->travelTo(now()->startOfDay()->addHours(9)->addDays(3));

    //     $log2->update([
    //         'end' =>  '2025-06-23 08:37:51',
    //     ]);

    //     $this->tim->workLogPatches()->create([
    //         'start' =>  $log2->start,
    //         'end' =>  '2025-06-20 16:11:59',
    //         'status' => 'accepted',
    //         'accepted_at' =>  '2025-06-23 11:23:18',
    //         'work_log_id' => $log2->id,
    //     ]);

    //     $logs = $this->tim->workLogs()->whereNotIn('id', [$log->id, $log2->id])->get();

    //     dump($logs->map->only('start', 'end'));

    //     $logs->each(fn($wl) =>  $this->tim->workLogPatches()->create([
    //         'start' =>  $wl->start,
    //         'end' =>  $wl->start,
    //         'status' => 'accepted',
    //         'accepted_at' =>  '2025-06-23 11:23:18',
    //         'work_log_id' => $wl->id,
    //     ]));

    //     dump($this->tim->defaultTimeAccount->fromTransactions->map->toArray());
    //     dump($this->tim->defaultTimeAccount->toTransactions->map->toArray());


    //     // dump($this->tim->workLogs->map->toArray());
    //     dump(gmdate('H:i:s', $this->tim->defaultTimeAccount->fresh()->balance));
    //     $this->assertEquals(0, 0);
    // }
}
