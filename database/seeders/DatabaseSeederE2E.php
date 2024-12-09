<?php

namespace Database\Seeders;

use App\Models\Absence;
use App\Models\AbsenceType;
use App\Models\Group;
use App\Models\GroupUser;
use App\Models\OperatingSite;
use App\Models\OperatingSiteUser;
use App\Models\OperatingTime;
use App\Models\Organization;
use App\Models\OrganizationUser;
use App\Models\TimeAccount;
use App\Models\TimeAccountSetting;
use App\Models\User;
use App\Models\UserWorkingHour;
use App\Models\UserWorkingWeek;
use App\Models\WorkLog;
use App\Models\WorkLogPatch;
use Database\Factories\OperatingSiteFactory;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Holidays\Countries\Country;

class DatabaseSeederE2E extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $users = User::factory(2) //anzahl user pro Betriebsstätte
            ->has(WorkLog::factory(3))
            ->has(WorkLog::factory(1, ['end' => null, 'start' => now()->subDay()]))
            ->has(UserWorkingHour::factory(1))
            ->has(UserWorkingWeek::factory(1))
            ;

        $org = Organization::factory()
            ->has(TimeAccountSetting::factory(1))
            ->has(
                OperatingSite::factory(2) //Anzahl Betriebsstätten
                    ->has(
                        $users->has(TimeAccount::factory(1, [
                            'time_account_setting_id' => 1,
                            'balance_limit' => random_int(20, 100),
                            'balance' => random_int(0, 20)
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
            ->has(Group::factory(3))->create();

        foreach (AbsenceType::$DEFAULTS as $type) {
            AbsenceType::factory([
                "organization_id" => $org->id,
                'type' => $type['name'],
                ...$type,
            ])->create();
        }

        


       

        $testUser = User::factory([
            'operating_site_id' => 1,
            'password' => Hash::make('test'),
            'email' => 'user@user.com',
            'first_name' => 'user',
            'last_name' => 'user',
            'role' => 'super-admin',
            'organization_id' => 1,
        ])
        ->has(UserWorkingHour::factory(1))
        ->has(TimeAccount::factory(1, ['time_account_setting_id' => 1]))
        ->has(UserWorkingWeek::factory(1))
        ->create();

        $testWorklog = WorkLog::factory([
            'user_id' => $testUser->id,
            'start' => '2024-12-06 08:15:12',
            'end' => '2024-12-06 18:00:01',

        ])->create();

        $testWorklogPatch = WorkLogPatch::factory([
            'work_log_id' => $testWorklog->id,
            'user_id' => $testUser->id,
            'start' => '2024-12-06 08:00:12',
            'end' => '2024-12-06 16:00:01',
            'created_at'=> '2024-12-09 09:15:01',
            'status' => 'created',  
        ])->create();

      


       

        $admin = User::factory([
            'operating_site_id' => 1,
            'password' => Hash::make('admin'),
            'email' => 'admin@admin.com',
            'first_name' => 'admin',
            'last_name' => 'admin',
            'role' => 'super-admin',
            'organization_id' => 1,
            'is_supervisor' => 1,
        ])->has(WorkLog::factory(3))
            ->has(WorkLog::factory(1, ['end' => null, 'start' => now()->subHour()]))
            ->has(UserWorkingHour::factory(1))
            ->has(TimeAccount::factory(1, ['time_account_setting_id' => 1]))
            ->has(UserWorkingWeek::factory(1))
            ->create();

    
        Organization::find(1)->update(['owner_id' => $admin->id]);

        $admin->isSubstitutedBy()->attach(User::find(1));
        $admin->isSubstitutionFor()->attach(User::find(6));

        $testOperatingSite = OperatingSite::factory([
            'organization_id'=>1,
            'name'=> 'delete me ORG',
            'email'=> 'delete@me.de',
            'phone_number' =>666666666,
            'street' => 'lösch mich',
            'house_number' => 666,
            'country'=> 'DE',
            'city' => 'Testcity',
            'zip' => 12345,
            'federal_state' => 'SH',
        
            

        ])->create();

        foreach (User::with(['organization', 'organization.groups', 'timeAccounts', 'operatingSite'])->get() as $user) {
            $group = $user->organization->groups->random();
            $user->group_id = $group->id;
            $user->timeAccounts()->first()->addBalance(100, 'seeder balance');
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

            GroupUser::create([
                "user_id" => $user->id,
                "group_id" => $user->group_id,
            ]);

            OperatingSiteUser::create([
                "user_id" => $user->id,
                "operating_site_id" => $user->operating_site_id,
            ]);

           
        }
     

        
    }
}
