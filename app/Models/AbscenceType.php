<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AbscenceType extends Model
{
    use HasFactory, SoftDeletes, ScopeInOrganization;

    public function abscences()
    {
        return $this->hasMany(Abscence::class);
    }
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
