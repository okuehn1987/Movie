<?php

namespace Database\Seeders;

use App\Models\AbscenceType;
use App\Models\OperatingSite;
use App\Models\Organization;
use App\Models\User;
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
        Organization::factory(3)->has(OperatingSite::factory(3)->has(User::factory(10)))->create();
        foreach (Organization::all() as $org) {
            foreach (AbscenceType::$DEFAULTS as $type) {
                AbscenceType::factory([
                    "organization_id" => $org->id,
                    'type' => $type['name'],
                    ...$type,
                ])->create();
            }
        }
        $admin = User::factory([
            'operating_site_id' => 1,
            'password' => Hash::make('admin'),
            'email' => 'admin@admin.com',
            'first_name' => 'admin',
            'last_name' => 'admin',
            'role' => 'super-admin',
            'organization_id' => 1
        ])->create();
        Organization::find(1)->update(['owner_id' => $admin->id]);
        $admin->isSubstitutedBy()->attach(User::find(1));
        $admin->isSubstitutedBy()->attach(User::find(2));
        $admin->isSubstitutedBy()->attach(User::find(3));
        $admin->isSubstitutionFor()->attach(User::find(3));
        $admin->isSubstitutionFor()->attach(User::find(5));
        $admin->isSubstitutionFor()->attach(User::find(6));
    }
}
