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
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->softDeletes();
            $table->string('name')->unique();
            $table->foreignId('owner_id')->nullable()->references('id')->on('users')->onDelete('set null');
            $table->string("tax_registration_id")->nullable();
            $table->string("commercial_registration_id")->nullable();
            $table->string("website")->nullable();
            $table->string("logo")->nullable();
            $table->boolean("night_surcharges")->default(false);
            $table->boolean("vacation_limitation_period")->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};
