<?php

namespace Database\Seeders;

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
        $admin = User::create(['operating_site_id' => 1, 'password' => Hash::make('admin'), 'email' => 'admin@admin.com', 'name' => 'admin', 'role' => 'super-admin']);
    }
}
