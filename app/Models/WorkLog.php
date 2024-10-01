<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkLog extends Model
{
    use HasFactory, SoftDeletes, ScopeInOrganization;

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function workLogPatches()
    {
        return $this->hasMany(WorkLogPatch::class);
    }
}
