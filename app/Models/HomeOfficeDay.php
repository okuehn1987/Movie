<?php

namespace App\Models;

use App\Enums\Status;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HomeOfficeDay extends Model
{
    use SoftDeletes;
    use ScopeInOrganization;

    protected $guarded = [];

    protected $casts = [
        'status' => Status::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function homeOfficeDayGenerator()
    {
        return $this->belongsTo(HomeOfficeDayGenerator::class);
    }
}
