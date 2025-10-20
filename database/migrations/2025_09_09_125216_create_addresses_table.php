<?php

use App\Models\OperatingSite;
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
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->morphs("addressable");
            $table->string("street")->nullable();
            $table->string("house_number")->nullable();
            $table->string("address_suffix")->nullable();
            $table->string("country")->default("DE");
            $table->string("city")->nullable();
            $table->string("zip")->nullable();
            $table->string("federal_state")->default("SH");
            $table->date("active_since")->default("2025-01-01");
            $table->timestamps();
        });

        $operatingSites = OperatingSite::all();
        foreach ($operatingSites as $site) {
            $site->addresses()->create([
                "street" => $site->street,
                "house_number" => $site->house_number,
                "address_suffix" => $site->address_suffix,
                "country" => $site->country,
                "city" => $site->city,
                "zip" => $site->zip,
                "federal_state" => $site->federal_state,
            ]);
        }

        Schema::table('operating_sites', function (Blueprint $table) {
            $table->dropColumn("street");
            $table->dropColumn("house_number");
            $table->dropColumn("address_suffix");
            $table->dropColumn("country");
            $table->dropColumn("city");
            $table->dropColumn("zip");
            $table->dropColumn("federal_state");
        });

        $users = User::all();
        foreach ($users as $user) {
            $user->addresses()->create([
                "street" => $user->street,
                "house_number" => $user->house_number,
                "address_suffix" => $user->address_suffix,
                "country" => $user->country,
                "city" => $user->city,
                "zip" => $user->zip,
                "federal_state" => $user->federal_state,
            ]);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn("street");
            $table->dropColumn("house_number");
            $table->dropColumn("address_suffix");
            $table->dropColumn("country");
            $table->dropColumn("city");
            $table->dropColumn("zip");
            $table->dropColumn("federal_state");
        });

        Schema::table('travel_logs', function (Blueprint $table) {
            $table->dropForeign(["start_location_id"]);
            $table->dropForeign(["end_location_id"]);
            $table->dropColumn("start_location_id");
            $table->dropColumn("end_location_id");
        });

        Schema::table('travel_log_patches', function (Blueprint $table) {
            $table->dropForeign(["start_location_id"]);
            $table->dropForeign(["end_location_id"]);
            $table->dropColumn("start_location_id");
            $table->dropColumn("end_location_id");
        });

        Schema::table('custom_addresses', function (Blueprint $table) {
            $table->string('name');
        });

        Schema::dropIfExists('travel_log_addresses');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
