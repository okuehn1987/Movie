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
        Schema::create('absence_types', function (Blueprint $table) {
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
            $table->boolean('requires_approval')->default(false);
            $table->foreignId('organization_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absence_types');
    }
};
