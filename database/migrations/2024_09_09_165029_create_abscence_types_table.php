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
        Schema::create('abscence_types', function (Blueprint $table) {
            $table->id();
            $table->softDeletes();
            $table->string("name");
            $table->string("abbreviation");
            $table->enum("type", [
                "Unbezahlter Urlaub",
                "Ausbildung/ Berufsschule",
                "Fort- und Weiterbildung",
                "AZV-Tag",
                "Bildungsurlaub",
                "Sonderurlaub",
                "Elternzeit",
                "Urlaub",
                "Andere"
            ]);
            $table->foreignId('organization_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('abscence_types');
    }
};
