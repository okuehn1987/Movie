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
        Schema::create('chat_assistants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained();
            $table->string('vector_store_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->integer('monthly_cost_limit')->default(150);
        });

        foreach (App\Models\Organization::all() as $organization) {
            App\Models\ChatAssistant::create([
                'organization_id' => $organization->id,
                'vector_store_id' => null,
                'monthly_cost_limit' => 150,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_assistants');
    }
};
