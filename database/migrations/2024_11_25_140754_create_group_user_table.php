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
        Schema::create('group_user', function (Blueprint $table) {
            $table->id();
            $table->softDeletes();
            $table->foreignId("group_id");
            $table->foreignId("user_id");
            $table->enum('user_permission', ['read', 'write'])->nullable();
            $table->enum('workLogPatch_permission', ['read', 'write'])->nullable();
            $table->enum('absence_permission', ['read', 'write'])->nullable();
            $table->enum('group_permission', ['read', 'write'])->nullable();
            $table->enum('timeAccount_permission', ['read', 'write'])->nullable();
            $table->enum('timeAccountSetting_permission', ['read', 'write'])->nullable();
            $table->enum('timeAccountTransaction_permission', ['read', 'write'])->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_user');
    }
};
