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
            $table->string('type')->default('default');
            $table->enum('truncation_cycle_length', [0, 1, 3, 6, 12])->default(1); // how often the truncation triggers (0 means never, 12 means end of year)
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
