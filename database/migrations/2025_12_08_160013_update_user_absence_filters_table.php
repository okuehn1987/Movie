<?php

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
        UserAbsenceFilter::all()->each(function (UserAbsenceFilter $filter) {
            $data = $filter->data;
            $data['version'] = 'v2';
            $data['operating_site_ids'] = [];
            $date['group_ids'] = [];
            $filter->data = $data;
            $filter->save();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        UserAbsenceFilter::all()->each(function (UserAbsenceFilter $filter) {
            $data = $filter->data;
            unset($data['operating_site_ids']);
            unset($data['group_ids']);
            $data['version'] = 'v1';
            $filter->data = $data;
            $filter->save();
        });
    }
};
