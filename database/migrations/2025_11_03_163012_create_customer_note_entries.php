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
        Schema::dropIfExists('customer_notes');

        Schema::create('customer_note_folders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('customer_note_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_note_folder_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('value');
            $table->foreignId('modified_by')->constrained('users')->references('id')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_note_entries');
        Schema::dropIfExists('customer_note_folders');
    }
};
