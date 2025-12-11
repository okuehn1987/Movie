<?php

use App\Enums\Status;
use App\Models\User;
use App\Models\UserAbsenceFilter;
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
        Schema::table('user_absence_filters', function (Blueprint $table) {
            $table->boolean('is_default')->default(false)->after('name');
        });

        User::with('operatingSite.currentAddress')->get()->each(function ($user) {
            UserAbsenceFilter::create([
                'user_id' => $user->id,
                'name' => 'Standort und Abteilung',
                'is_default' => true,
                'data' => [
                    'version' => 'v2',
                    'user_ids' => [],
                    'absence_type_ids' => [],
                    'operating_site_ids' => [$user->operating_site_id],
                    'group_ids' => $user->group_id ? [$user->group_id] : [],
                    'statuses' => [Status::Created, Status::Accepted],
                    'holidays_from_federal_states' => [$user->operatingSite->currentAddress->federal_state],
                ],
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_absence_filters', function (Blueprint $table) {
            $table->dropColumn('is_default');
        });
    }
};
