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
use App\Models\WorkLog;
use Illuminate\Database\Eloquent\Factories\Sequence;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $users = User::factory(10)
            ->has(WorkLog::factory(3))
            ->has(WorkLog::factory(1, ['end' => null, 'start' => now()->subDay()]))
            ->has(UserWorkingHour::factory(1))
            ->has(UserWorkingWeek::factory(1));

        foreach ([1, 2, 3] as $n) {
            Organization::factory()
                ->has(TimeAccountSetting::factory(1))
                ->has(
                    OperatingSite::factory(3)
                        ->has($users
                            ->has(TimeAccount::factory(1, ['time_account_setting_id' => $n, 'balance_limit' => random_int(20, 100), 'balance' => random_int(0, 20)])))
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
                ->has(Group::factory(3))->create();
        }
        foreach (Organization::all() as $org) {

            foreach (AbsenceType::$DEFAULTS as $type) {
                AbsenceType::factory([
                    "organization_id" => $org->id,
                    'type' => $type['name'],
                    ...$type,
                ])->create();
            }
        }

        $admin = User::factory([
            ...collect(User::$PERMISSIONS)->flatMap(fn($p) => [$p['name'] => true])->toArray(),
            'operating_site_id' => 1,
            'password' => Hash::make('admin'),
            'email' => 'admin@admin.com',
            'first_name' => 'admin',
            'last_name' => 'admin',
            'role' => 'super-admin',
            'organization_id' => 1,
        ])->has(WorkLog::factory(3))
            ->has(WorkLog::factory(1, ['end' => null, 'start' => now()->subHour()]))
            ->has(UserWorkingHour::factory(1))
            ->has(TimeAccount::factory(1, ['time_account_setting_id' => 1]))
            ->has(UserWorkingWeek::factory(1))
            ->create();

        Organization::find(1)->update(['owner_id' => $admin->id]);

        $admin->isSubstitutedBy()->attach(User::find(1));
        $admin->isSubstitutionFor()->attach(User::find(6));

        foreach (User::with(['organization', 'organization.groups', 'timeAccounts'])->get() as $user) {
            $group = $user->organization->groups->random();
            $user->group_id = $group->id;
            $user->timeAccounts()->first()->addBalance(100, 'seeder balance');
            $user->supervisor_id = User::where('id', '!=', $user->id)
                ->whereNotIn('id', $user->allSuperviseesFlat()->pluck('id'))
                ->inRandomOrder()->first()?->id;
            $user->save();
        }
    }
}
