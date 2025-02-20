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
        Schema::create('travel_log_patches', function (Blueprint $table) {
            $table->id();
            $table->softDeletes();
            $table->foreignId('user_id');
            $table->foreignId('travel_log_id');
            $table->enum('status', ["created", "declined", "accepted"]);
            $table->dateTime('accepted_at')->nullable();
            $table->foreignId("start_location_id")->references("id")->on("travel_log_addresses")->nullable();
            $table->foreignId("end_location_id")->references("id")->on("travel_log_addresses")->nullable();
            $table->dateTime("start");
            $table->dateTime("end");
            $table->boolean("is_accounted")->default(false);
            $table->dateTime('accounted_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('travel_log_patches');
    }
};
