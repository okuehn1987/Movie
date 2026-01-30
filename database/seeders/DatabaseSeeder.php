<?php

namespace Database\Seeders;

use App\Models\AbsenceType;
use App\Models\Address;
use App\Models\ChatAssistant;
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
use App\Services\OpenAIService;
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
    }

}
