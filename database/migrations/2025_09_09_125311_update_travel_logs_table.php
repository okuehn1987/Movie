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
        Schema::table('travel_logs', function (Blueprint $table) {
            $table->foreignId('from_id')->nullable()->references('id')->on('addresses');
            $table->foreignId('to_id')->nullable()->references('id')->on('addresses');
        });

        Schema::table('travel_log_patches', function (Blueprint $table) {
            $table->foreignId('from_id')->nullable()->references('id')->on('addresses');
            $table->foreignId('to_id')->nullable()->references('id')->on('addresses');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('travel_logs', function (Blueprint $table) {
            $table->dropForeign(['from_id']);
            $table->dropColumn('from_id');
            $table->dropForeign(['to_id']);
            $table->dropColumn('to_id');
        });

        Schema::table('travel_log_patches', function (Blueprint $table) {
            $table->dropForeign(['from_id']);
            $table->dropColumn('from_id');
            $table->dropForeign(['to_id']);
            $table->dropColumn('to_id');
        });
    }
};
