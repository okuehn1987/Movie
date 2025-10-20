<?php

use App\Models\Organization;
use App\Models\OrganizationModule;
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
        Schema::create('organization_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->string('module');
            $table->dateTime('activated_at')->nullable();
            $table->timestamps();
        });

        Organization::all()->each(fn($org) => $org->modules()->create(['module' => 'herta', 'activated_at' => '2024-12-01']));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organization_modules');
    }
};
