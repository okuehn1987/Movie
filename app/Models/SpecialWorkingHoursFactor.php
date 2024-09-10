<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SpecialWorkingHoursFactor extends Model
{
    use HasFactory, SoftDeletes, ScopeInOrganization;

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
