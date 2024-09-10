<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Abscence extends Model
{
    use HasFactory, SoftDeletes, ScopeInOrganization;

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function abscenceType()
    {
        return $this->belongsTo(AbscenceType::class);
    }
}
