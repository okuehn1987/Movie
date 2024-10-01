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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->softDeletes();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('role', ['super-admin', 'employee'])->default('employee');
            $table->foreignId('operating_site_id');
            $table->foreignId('group_id')->nullable();
            $table->foreignId('supervisor_id')->nullable()->references('id')->on('users');
            $table->foreignId('organization_id')->nullable();
            $table->string("staff_number")->nullable();
            $table->date("date_of_birth");
            $table->boolean("home_office")->default(false);
            $table->float("home_office_ratio")->nullable();
            $table->string("phone_number")->nullable();
            $table->string("street")->nullable();
            $table->string("house_number")->nullable();
            $table->string("address_suffix")->nullable();
            $table->string("country")->nullable();
            $table->string("city")->nullable();
            $table->string("zip")->nullable();
            $table->string("federal_state")->nullable();

            $table->boolean('work_log_patching')->default(false);

            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
