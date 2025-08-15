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
        Schema::table('work_log_patches', function (Blueprint $table) {
            $table->enum('type', ['patch', 'delete'])->default('patch');
        });

        Schema::table('travel_log_patches', function (Blueprint $table) {
            $table->enum('type', ['patch', 'delete'])->default('patch');
        });

        Schema::table('absence_patches', function (Blueprint $table) {
            $table->enum('type', ['patch', 'delete'])->default('patch');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
