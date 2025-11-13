<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeOfficeDayGenerator extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function homeOfficeDays()
    {
        return $this->hasMany(HomeOfficeDay::class);
    }
}
