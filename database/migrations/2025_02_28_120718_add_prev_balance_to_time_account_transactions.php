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
        Schema::table('time_account_transactions', function (Blueprint $table) {
            $table->integer('from_previous_balance')->nullable();
            $table->integer('to_previous_balance')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('time_account_transactions', function (Blueprint $table) {
            $table->dropColumn('from_previous_balance');
            $table->dropColumn('to_previous_balance');
        });
    }
};
