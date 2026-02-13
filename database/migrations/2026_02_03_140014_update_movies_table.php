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
        Schema::table('movies', function (Blueprint $table) {
            // $table->dropColumn('id');
            // $table->text('name')->change();
            $table->dropColumn('name');
            $table->dropColumn('year');


            $table->string('title')->unique();
            $table->string('actor');
            $table->string('publicationDate');
            $table->string('duration_in_seconds');
            $table->unsignedTinyInteger('rating');
            $table->boolean('hidden');
            $table->string('movie_file_path');
            $table->string('thumbnail_file_path');
            $table->text('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movies');
    }
};
