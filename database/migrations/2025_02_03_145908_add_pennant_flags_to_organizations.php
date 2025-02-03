<?php

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
        Schema::table('organizations', function (Blueprint $table) {
            $table->boolean("auto_accept_travel_logs")->default(false);
            $table->boolean("christmas_vacation_day")->default(false);
            $table->boolean("new_year_vacation_day")->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn("auto_accept_travel_logs");
            $table->dropColumn("christmas_vacation_day");
            $table->dropColumn("new_year_vacation_day");
        });
    }
};
