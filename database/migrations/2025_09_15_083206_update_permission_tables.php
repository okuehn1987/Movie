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
        Schema::table('organization_users', function (Blueprint $table) {
            $table->enum('ticket_permission', ['read', 'write'])->nullable();
            $table->enum('customer_permission', ['read', 'write'])->nullable();
        });

        Schema::table('operating_site_users', function (Blueprint $table) {
            $table->enum('ticket_permission', ['read', 'write'])->nullable();
            $table->enum('customer_permission', ['read', 'write'])->nullable();
            $table->enum('absenceType_permission', ['read', 'write'])->nullable();
        });

        Schema::table('group_users', function (Blueprint $table) {
            $table->enum('ticket_permission', ['read', 'write'])->nullable();
            $table->enum('customer_permission', ['read', 'write'])->nullable();
            $table->enum('absenceType_permission', ['read', 'write'])->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organization_users', function (Blueprint $table) {
            $table->dropColumn('ticket_permission');
        });

        Schema::table('operating_site_users', function (Blueprint $table) {
            $table->dropColumn('ticket_permission');
            $table->dropColumn('absenceType_permission');
        });

        Schema::table('group_users', function (Blueprint $table) {
            $table->dropColumn('ticket_permission');
            $table->dropColumn('absenceType_permission');
        });
    }
};
