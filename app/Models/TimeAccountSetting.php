<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TimeAccountSetting extends Model
{
    use HasFactory, ScopeInOrganization, SoftDeletes;

    protected $guarded = [];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function timeAccounts()
    {
        return $this->hasMany(TimeAccount::class);
    }

    public static function getDefaultSettings()
    {
        return [
            [
                'type' => null,
                'truncation_cycle_length_in_months' => 1,
            ],
            [
                'type' => null,
                'truncation_cycle_length_in_months' => 3,
            ],
            [
                'type' => null,
                'truncation_cycle_length_in_months' => 6,
            ],
            [
                'type' => null,
                'truncation_cycle_length_in_months' => 12,
            ],
            [
                'type' => null,
                'truncation_cycle_length_in_months' => null,
            ]
        ];
    }
}
