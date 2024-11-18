<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TimeAccountSetting extends Model
{
    use HasFactory, ScopeInOrganization, SoftDeletes;

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function timeAccounts()
    {
        return $this->hasMany(TimeAccount::class);
    }
}
