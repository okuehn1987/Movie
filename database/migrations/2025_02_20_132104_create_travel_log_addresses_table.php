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
        Schema::create('travel_log_addresses', function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->string("street");
            $table->string("house_number");
            $table->string("address_suffix")->nullable();
            $table->string("country")->default("DE");
            $table->string("city");
            $table->string("zip");
            $table->string("federal_state")->default("SH");
            $table->foreignId("organization_id");
            $table->timestamps();
        });

        Schema::table('travel_logs', function (Blueprint $table) {
            $table->dropColumn("start_location_id");
            $table->dropColumn("end_location_id");
            $table->dropColumn("start_location_type");
            $table->dropColumn("end_location_type");

            $table->foreignId("start_location_id")->references("id")->on("travel_log_addresses")->nullable();
            $table->foreignId("end_location_id")->references("id")->on("travel_log_addresses")->nullable();

            $table->foreignId('shift_id')->nullable();
            $table->enum('status', ["created", "declined", "accepted"]);
            $table->dateTime('accepted_at')->nullable();
            $table->text("comment")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('travel_log_addresses');
    }
};
