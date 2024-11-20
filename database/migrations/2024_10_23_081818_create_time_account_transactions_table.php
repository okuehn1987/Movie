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
        Schema::create('time_account_transactions', function (Blueprint $table) {
            $table->id();
            $table->softDeletes();
            $table->foreignId('from_id')->nullable()->constrained('time_accounts')->cascadeOnDelete();
            $table->foreignId('to_id')->nullable()->constrained('time_accounts')->cascadeOnDelete();
            $table->foreignId('modified_by')->nullable()->constrained('users')->cascadeOnDelete();
            $table->decimal('amount');
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_account_transactions');
    }
};
