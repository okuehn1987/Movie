<?php

use App\Models\User;
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
        Schema::table('users', function (Blueprint $table) {
            $table->date('resignation_date')->nullable();
        });

        User::whereId(12)->update([
            'resignation_date' => '2025-04-30',
        ]);
        User::whereId(15)->update([
            'resignation_date' => '2025-06-30',
        ]);
        User::whereId(5)->update([
            'resignation_date' => '2025-06-30',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
