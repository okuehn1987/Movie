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
        Schema::create('custom_address', function (Blueprint $table) {
            $table->id();
            $table->string("street")->nullable();
            $table->string("house_number")->nullable();
            $table->string("address_suffix")->nullable();
            $table->string("country")->nullable();
            $table->string("city")->nullable();
            $table->string("zip")->nullable();
            $table->string("federal_state")->nullable();
            $table->foreignId("organization_id");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_address');
    }
};
