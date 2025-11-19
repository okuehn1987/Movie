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
        Schema::table('customer_operating_sites', function (Blueprint $table) {
            $table->string('email')->nullable();
            $table->string('phone_number')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_operating_sites', function (Blueprint $table) {
            $table->dropColumn('email');
            $table->dropColumn('phone_number');
        });
    }
};
