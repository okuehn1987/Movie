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
            $table->enum('chatAssistant_permission', ['read', 'write'])->nullable();
            $table->enum('chatFile_permission', ['read', 'write'])->nullable();
            $table->enum('isaPayment_permission', ['read', 'write'])->nullable();
            $table->after('chatFile_permission', function (Blueprint $table) {
                $table->timestamp('created_at')->nullable()->change();
                $table->timestamp('updated_at')->nullable()->change();
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organization_users', function (Blueprint $table) {
            $table->dropColumn('chatAssistant_permission');
            $table->dropColumn('chatFile_permission');
            $table->dropColumn('isaPayment_permission');
        });
    }
};
