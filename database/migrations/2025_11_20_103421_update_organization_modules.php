<?php

use App\Models\OrganizationModule;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        OrganizationModule::all()->each(function (OrganizationModule $organizationModule) {
            if ($organizationModule->module === 'herta') {
                $organizationModule->module = 'tide';
                $organizationModule->save();
            }
            if ($organizationModule->module === 'timesheets') {
                $organizationModule->module = 'flow';
                $organizationModule->save();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        OrganizationModule::all()->each(function (OrganizationModule $organizationModule) {
            if ($organizationModule->value === 'tide') {
                $organizationModule->value = 'herta';
                $organizationModule->save();
            }
            if ($organizationModule->value === 'flow') {
                $organizationModule->value = 'timesheets';
                $organizationModule->save();
            }
        });
    }
};
