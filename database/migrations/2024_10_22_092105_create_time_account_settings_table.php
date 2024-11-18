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
        Schema::create('time_account_settings', function (Blueprint $table) {
            $table->id();
            $table->softDeletes();
            $table->string('type')->nullable();
            $table->enum('truncation_cycle_length_in_months', [1, 3, 6, 12])->nullable(); // how often the truncation triggers (null means never, 12 means end of year)
            $table->foreignId('organization_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_account_settings');
    }
};
