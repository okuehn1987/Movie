<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeOfficeDay extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function homeOfficeDayGenerators()
    {
        return $this->belongsTo(HomeOfficeDayGenerator::class);
    }
}
