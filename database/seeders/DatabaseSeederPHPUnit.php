<?php

namespace Database\Seeders;

use App\Models\AbsenceType;
use App\Models\Group;
use App\Models\OperatingSite;
use App\Models\OperatingTime;
use App\Models\Organization;
use App\Models\TimeAccount;
use App\Models\TimeAccountSetting;
use App\Models\User;
use App\Models\UserWorkingHour;
use App\Models\UserWorkingWeek;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

class DatabaseSeederPHPUnit extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $org = Organization::factory()
            ->has(TimeAccountSetting::factory(1))
            ->has(
                OperatingSite::factory(1)
                    ->has(
                        User::factory(1, ['date_of_birth' => now()->subYears(30)])
                            ->has(TimeAccount::factory(1, [
                                'time_account_setting_id' => 1,
                                'balance_limit' => 52.5 * 2 * 3600,
                            ]))->has(UserWorkingHour::factory(1, [
                                'active_since' => now()->subYear()->startOfYear(),
                                'weekly_working_hours' => 52.5,
                            ]))->has(UserWorkingWeek::factory(1, [
                                'active_since' => now()->subYear()->startOfYear(),
                            ]))
                    )
                    ->has(
                        User::factory(1, ['date_of_birth' => now()->subYears(17)])
                            ->has(TimeAccount::factory(1, [
                                'time_account_setting_id' => 1,
                                'balance_limit' => 52.5 * 2 * 3600,
                            ]))->has(UserWorkingHour::factory(1, [
                                'active_since' => now()->subYear()->startOfYear(),
                                'weekly_working_hours' => 52.5,
                            ]))->has(UserWorkingWeek::factory(1, [
                                'active_since' => now()->subYear()->startOfYear(),
                            ]))
                    )
                    ->has(OperatingTime::factory(7, ['start' => '09:00:00', 'end' => '17:00:00'])
                        ->sequence(fn(Sequence $sequence) => ['type' => [
                            'monday',
                            'tuesday',
                            'wednesday',
                            'thursday',
                            'friday',
                            'saturday',
                            'sunday',
                        ][$sequence->index % 7]]))
            )
            ->has(Group::factory(1))->create();

        foreach (AbsenceType::$DEFAULTS as $type) {
            AbsenceType::factory([
                "organization_id" => $org->id,
                'type' => $type['name'],
                ...$type,
            ])->create();
        }
    }
}
