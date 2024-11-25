<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OperatingSite extends Model
{
    use HasFactory, SoftDeletes, ScopeInOrganization;

    protected $guarded = [];

    public function operatingTimes()
    {
        return $this->hasMany(OperatingTime::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function operatingSiteUser()
    {
        return $this->hasMany(OperatingSiteUser::class);
    }
}
