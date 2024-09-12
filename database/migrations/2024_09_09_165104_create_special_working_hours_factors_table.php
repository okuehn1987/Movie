<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**s
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('special_working_hours_factors', function (Blueprint $table) {
            $table->id();
            $table->softDeletes();
            $table->foreignId('organization_id');
            $table->enum("type", ["monday", "tuesday", "wednesday", "thursday", "friday", "saturday", "sunday", "holiday", "nightshift"]);
            $table->float("extra_charge");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('special_working_hours_factors');
    }
};
