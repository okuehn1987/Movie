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
        Schema::create('work_log_patches', function (Blueprint $table) {
            $table->id();
            $table->softDeletes();
            $table->foreignId('work_log_id');
            $table->foreignId('user_id');
            $table->dateTime("start");
            $table->dateTime("end");
            $table->boolean("is_home_office")->default(false);
            $table->enum('status', ["created", "declined", "accepted"]);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_log_patches');
    }
};
