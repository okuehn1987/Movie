<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkLogPatch extends Model
{
    use HasFactory, SoftDeletes, ScopeInOrganization;

    protected $guarded = [];

    public function workLog()
    {
        return $this->belongsTo(WorkLog::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
