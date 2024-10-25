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
        Schema::create('time_accounts', function (Blueprint $table) {
            $table->id();
            $table->softDeletes();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->float('balance')->default(0);
            $table->float('balance_limit')->default(0);
            $table->string('type')->default('default');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_accounts');
    }
};
