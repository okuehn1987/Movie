<?php

use App\Models\WorkLogPatch;
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
        Schema::table('work_log_patches', function (Blueprint $table) {
            $table->dropColumn('is_accounted');
            $table->foreignId('shift_id')->nullable();
        });

        Schema::table('work_logs', function (Blueprint $table) {
            $table->enum('status', ["created", "declined", "accepted"]);
            $table->dateTime('accepted_at')->nullable();
            $table->text('comment')->nullable();
        });

        Schema::table('absences', function (Blueprint $table) {
            $table->text('comment')->nullable();
        });

        Schema::create('absence_patches', function (Blueprint $table) {
            $table->id();
            $table->softDeletes();
            $table->foreignId('absence_type_id');
            $table->foreignId('user_id');
            $table->foreignId('absence_id');
            $table->date("start");
            $table->date("end");
            $table->enum("status", ["created", "declined", "accepted"]);
            $table->dateTime('accepted_at')->nullable();
            $table->text('comment')->nullable();
            $table->timestamps();
        });

        Schema::table('shifts', function (Blueprint $table) {
            $table->boolean('is_accounted')->default(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_logs', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
