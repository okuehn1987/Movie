<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SpecialWorkingHoursFactor extends Model
{
    use HasFactory, SoftDeletes, ScopeInOrganization;

    protected $guarded = [];

    public static $TYPES = [
        "monday" => "Montag",
        "tuesday" => "Dienstag",
        "wednesday" => "Mittwoch",
        "thursday" => "Donnerstag",
        "friday" => "Freitag",
        "saturday" => "Samstag",
        "sunday" => "Sonntag",
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
