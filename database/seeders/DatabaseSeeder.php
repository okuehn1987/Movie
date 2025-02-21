<?php

namespace Database\Seeders;

use App\Models\AbsenceType;
use App\Models\Group;
use App\Models\GroupUser;
use App\Models\OperatingSite;
use App\Models\OperatingSiteUser;
use App\Models\OperatingTime;
use App\Models\Organization;
use App\Models\OrganizationUser;
use App\Models\Shift;
use App\Models\TimeAccount;
use App\Models\TimeAccountSetting;
use App\Models\User;
use App\Models\UserWorkingHour;
use App\Models\UserWorkingWeek;
use App\Models\WorkLog;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $users = User::factory(10);

        $org = Organization::factory()
            ->has(TimeAccountSetting::factory(1))
            ->has(
                OperatingSite::factory(3)
                    ->has(
                        $users
                            ->has(TimeAccount::factory(1, [
                                'time_account_setting_id' => 1,
                                'balance_limit' => random_int(20, 100),
                                'balance' => random_int(0, 20)
                            ]))
                            ->has(UserWorkingHour::factory(1))
                            ->has(UserWorkingWeek::factory(1))
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
            ->has(Group::factory(3))->create();

        foreach (AbsenceType::$DEFAULTS as $type) {
            AbsenceType::factory([
                "organization_id" => $org->id,
                'type' => $type['name'],
                ...$type,
            ])->create();
        }

        foreach (User::all() as $user) {
            Shift::factory(1, ['start' => now()->subDay(), 'end' => now(), 'user_id' => $user->id])
                ->has(WorkLog::factory(3, ['user_id' => $user->id]))
                ->has(WorkLog::factory(1, ['end' => null, 'start' => now()->subDay(), 'user_id' => $user->id]))
                ->create();
        }

        $admin = User::factory([
            'operating_site_id' => 1,
            'password' => Hash::make('admin'),
            'email' => 'admin@admin.com',
            'first_name' => 'admin',
            'last_name' => 'admin',
            'role' => 'super-admin',
            'organization_id' => 1,
        ])->has(
            Shift::factory(1, ['start' => now()->subHour(), 'end' => now()])
                ->has(WorkLog::factory(3))
                ->has(WorkLog::factory(1, ['end' => null, 'start' => now()->subHour()]))
        )
            ->has(UserWorkingHour::factory(1))
            ->has(TimeAccount::factory(1, ['time_account_setting_id' => 1]))
            ->has(UserWorkingWeek::factory(1))
            ->create();

        Organization::find(1)->update(['owner_id' => $admin->id]);

        $admin->isSubstitutedBy()->attach(User::find(1));
        $admin->isSubstitutionFor()->attach(User::find(6));

        foreach (User::with(['organization', 'organization.groups', 'timeAccounts', 'operatingSite'])->get() as $user) {
            // $group = $user->organization->groups->random();
            // $user->group_id = $group->id;
            $user->timeAccounts()->first()->addBalance(10 * 3600, 'seeder balance');
            $user->supervisor_id = User::whereIn('operating_site_id', $user->organization->operatingSites()->get()->pluck('id'))->where('id', '!=', $user->id)
                ->whereNotIn('id', $user->allSuperviseesFlat()->pluck('id'))
                ->inRandomOrder()->first()?->id;
            $user->save();
            User::find($user->supervisor_id)?->update(['is_supervisor' => true]);

            OrganizationUser::create([
                "user_id" => $user->id,
                "organization_id" => $user->operatingSite->organization_id,
                ...($user->id === $admin->id ?
                    collect(array_keys(User::$PERMISSIONS))->flatMap(fn($key) =>
                    collect(User::$PERMISSIONS[$key])
                        ->flatMap(fn($g) => [$g['name'] => 'write'])->toArray())
                    ->toArray()
                    : [])
            ]);

            // GroupUser::create([
            //     "user_id" => $user->id,
            //     "group_id" => $user->group_id,
            // ]);

            OperatingSiteUser::create([
                "user_id" => $user->id,
                "operating_site_id" => $user->operating_site_id,
            ]);
        }
    }
}
