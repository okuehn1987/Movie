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
        Schema::create('home_office_day_generators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->date('start');
            $table->date('end');
            $table->boolean('monday')->default(false);
            $table->boolean('tuesday')->default(false);
            $table->boolean('wednesday')->default(false);
            $table->boolean('thursday')->default(false);
            $table->boolean('friday')->default(false);
            $table->boolean('saturday')->default(false);
            $table->boolean('sunday')->default(false);
            $table->timestamps();
        });

        Schema::create('home_office_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('home_office_day_generator_id')->nullable()->constrained();
            $table->date('date');
            $table->enum('status', ["created", "declined", "accepted"]);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('home_office_days');
        Schema::dropIfExists('home_office_day_generators');
    }
};
