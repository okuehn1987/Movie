<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TravelLogAddress extends Model
{
    use HasFactory;

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function travelLogs()
    {
        return $this->hasMany(TravelLog::class);
    }
    public function travelLogPatches()
    {
        return $this->hasMany(TravelLogPatch::class);
    }
}
