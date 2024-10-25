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
        Schema::create('travel_logs', function (Blueprint $table) {
            $table->id();
            $table->softDeletes();
            $table->foreignId('user_id');
            $table->enum("start_location_type", ["user", "operating_site", "custom_address"]);
            $table->bigInteger("start_location_id");
            $table->dateTime("start");
            $table->dateTime("end");
            $table->enum("end_location_type", ["user", "operating_site", "custom_address"]);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('travel_logs');
    }
};
