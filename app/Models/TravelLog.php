<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TravelLog extends Model
{
    use HasFactory, SoftDeletes;
    use ScopeInOrganization;

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
