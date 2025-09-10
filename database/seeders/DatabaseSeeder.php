<?php

namespace Database\Seeders;

use App\Models\AbsenceType;
use App\Models\Address;
use App\Models\Customer;
use App\Models\CustomerOperatingSite;
use App\Models\Group;
use App\Models\OperatingSite;
use App\Models\OperatingSiteUser;
use App\Models\OperatingTime;
use App\Models\Organization;
use App\Models\OrganizationModule;
use App\Models\OrganizationUser;
use App\Models\TimeAccountSetting;
use App\Models\TimeAccountTransactionChange;
use App\Models\User;
use App\Models\UserWorkingHour;
use App\Models\UserWorkingWeek;
use App\Services\AppModuleService;
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
        self::createDefaultOrg();
        self::createTimesheetsOrg();
    }

    private static function createTimesheetsOrg()
    {
        $users = User::factory(10)->has(Address::factory(1));
        $operatingSites = OperatingSite::factory(3)->has(Address::factory(1));

        $org = Organization::factory()
            ->has(TimeAccountSetting::factory(1))
            ->has(
                $operatingSites
                    ->has($users->has(UserWorkingWeek::factory(1)))
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
            ->has(Group::factory(3))
            ->has(Customer::factory(10)->has(CustomerOperatingSite::factory(2)->has(Address::factory(1))))
            ->create();

        OrganizationModule::create([
            'organization_id' => $org->id,
            'module' => 'timesheets',
            'activated_at' => now(),
        ]);



        foreach (AbsenceType::$DEFAULTS as $type) {
            AbsenceType::factory([
                "organization_id" => $org->id,
                'type' => $type['name'],
                ...$type,
            ])->create();
        }

        $admin = User::factory([
            'operating_site_id' => $org->operatingSites()->first()->id,
            'password' => Hash::make('admin'),
            'email' => 'admin2@admin.com',
            'first_name' => 'admin',
            'last_name' => 'admin',
            'role' => 'super-admin',
            'organization_id' => $org->id,
        ])->has(UserWorkingWeek::factory(1))
            ->create();

        $org->users->each(fn($u) => $u->timeAccounts()->create([
            'name' => 'Standardkonto',
            'time_account_setting_id' => $org->timeAccountSettings()->first()->id,
            'balance_limit' => random_int(20, 100),
            'balance' => random_int(0, 20)
        ]));

        $org->update(['owner_id' => $admin->id]);

        foreach (User::whereIn('id', $org->users->pluck('id'))->with(['organization', 'organization.groups', 'timeAccounts', 'operatingSite'])->get() as $cu) {
            $cu->supervisor_id = $cu->supervisor_id ?? User::whereIn('operating_site_id', $cu->organization->operatingSites()->get()->pluck('id'))->where('id', '!=', $cu->id)
                ->whereNotIn('id', $cu->allSuperviseesFlat()->pluck('id'))
                ->inRandomOrder()->first()?->id;

            $cu->save();

            User::find($cu->supervisor_id)?->update(['is_supervisor' => true]);

            OrganizationUser::create([
                "user_id" => $cu->id,
                "organization_id" => $cu->operatingSite->organization_id,
                ...($cu->id === $admin->id ?
                    collect(array_keys(User::$PERMISSIONS))->flatMap(fn($key) =>
                    collect(User::$PERMISSIONS[$key])
                        ->flatMap(fn($g) => [$g['name'] => 'write'])->toArray())
                    ->toArray()
                    : [])
            ]);

            OperatingSiteUser::create([
                "user_id" => $cu->id,
                "operating_site_id" => $cu->operating_site_id,
            ]);
        }
    }

    private static function createDefaultOrg()
    {
        $users = User::factory(10)->has(Address::factory(1));

        $org = Organization::factory()
            ->has(TimeAccountSetting::factory(1))
            ->has(
                OperatingSite::factory(3)
                    ->has(Address::factory(1))
                    ->has($users->has(UserWorkingHour::factory(1))->has(UserWorkingWeek::factory(1)))
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
            ->has(Group::factory(3))
            ->has(Customer::factory(10)->has(CustomerOperatingSite::factory(2)->has(Address::factory(1))))
            ->create();

        foreach (array_keys(AppModuleService::$APP_MODULES) as $module) {
            OrganizationModule::create([
                'organization_id' => $org->id,
                'module' => $module,
                'activated_at' => now(),
            ]);
        }

        foreach (AbsenceType::$DEFAULTS as $type) {
            AbsenceType::factory([
                "organization_id" => $org->id,
                'type' => $type['name'],
                ...$type,
            ])->create();
        }

        $admin = User::factory([
            'operating_site_id' => 1,
            'password' => Hash::make('admin'),
            'email' => 'admin@admin.com',
            'first_name' => 'admin',
            'last_name' => 'admin',
            'role' => 'super-admin',
            'organization_id' => $org->id,
        ])
            ->has(UserWorkingHour::factory(1, ['weekly_working_hours' => 40, 'active_since' => now()->startOfYear()]))
            ->has(UserWorkingWeek::factory(1))
            ->create();



        User::factory([
            'operating_site_id' => 1,
            'password' => Hash::make('user'),
            'email' => 'user@user.com',
            'first_name' => 'user',
            'last_name' => 'user',
            'role' => 'employee',
            'supervisor_id' => $admin->id,
        ])
            ->has(Address::factory(1))
            ->has(UserWorkingHour::factory(1, ['weekly_working_hours' => 40, 'active_since' => now()->startOfYear()]))
            ->has(UserWorkingWeek::factory(1))
            ->create();

        $org->users->each(fn($u) => $u->timeAccounts()->create([
            'name' => 'Standardkonto',
            'time_account_setting_id' => $org->timeAccountSettings()->first()->id,
            'balance_limit' => random_int(20, 100),
            'balance' => random_int(0, 20)
        ]));
        // Shift::factory(1, ['start' => now()->subHour(), 'end' => now(), 'user_id' => $admin->id])
        //     ->has(WorkLog::factory(3, ['user_id' => $admin->id]))
        //     ->has(WorkLog::factory(1, ['end' => null, 'start' => now()->subHour(), 'user_id' => $admin->id]))->create();

        Organization::find(1)->update(['owner_id' => $admin->id]);

        $admin->isSubstitutedBy()->attach(User::find(1));
        $admin->isSubstitutionFor()->attach(User::find(6));
        foreach (User::whereIn('id', collect($org->users)->pluck('id'))->with(['organization', 'organization.groups', 'timeAccounts', 'operatingSite'])->get() as $cu) {
            TimeAccountTransactionChange::createFor($cu->timeAccounts()->first()->addBalance(10 * 3600, 'seeder balance'), now());

            $cu->supervisor_id = $cu->supervisor_id ?? User::whereIn('operating_site_id', $cu->organization->operatingSites()->get()->pluck('id'))->where('id', '!=', $cu->id)
                ->whereNotIn('id', $cu->allSuperviseesFlat()->pluck('id'))
                ->inRandomOrder()->first()?->id;

            $cu->save();

            User::find($cu->supervisor_id)?->update(['is_supervisor' => true]);

            OrganizationUser::create([
                "user_id" => $cu->id,
                "organization_id" => $cu->operatingSite->organization_id,
                ...($cu->id === $admin->id ?
                    collect(array_keys(User::$PERMISSIONS))->flatMap(fn($key) =>
                    collect(User::$PERMISSIONS[$key])
                        ->flatMap(fn($g) => [$g['name'] => 'write'])->toArray())
                    ->toArray()
                    : [])
            ]);

            OperatingSiteUser::create([
                "user_id" => $cu->id,
                "operating_site_id" => $cu->operating_site_id,
            ]);
        }
    }
}
