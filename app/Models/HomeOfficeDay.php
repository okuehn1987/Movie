<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HomeOfficeDay extends Model
{
    use SoftDeletes;
    use ScopeInOrganization;

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function homeOfficeDayGenerator()
    {
        return $this->belongsTo(HomeOfficeDayGenerator::class);
    }
}
