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
            $table->enum('customerContact_permission', [null, 'read', 'write'])->default(null);
            $table->enum('customerOperatingSite_permission', [null, 'read', 'write'])->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organization_users', function (Blueprint $table) {
            $table->dropColumn('customerContact_permission');
            $table->dropColumn('customerOperatingSite_permission');
        });
    }
};
