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
        Schema::table('user_leave_days', function (Blueprint $table) {
            $table->date('active_since');
            $table->string('type')->default('annual');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_leave_days', function (Blueprint $table) {
            $table->dropColumn('active_since');
            $table->dropColumn('type');
        });
    }
};
