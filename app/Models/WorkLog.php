<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkLog extends Model
{
    use HasFactory, SoftDeletes, ScopeInOrganization;

    protected $guarded = [];
    protected $casts = ['is_home_office' => 'boolean'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function workLogPatches()
    {
        return $this->hasMany(WorkLogPatch::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne<\App\Models\WorkLogPatch, \App\Models\WorkLog>
     */
    public function currentAccountedPatch()
    {
        return $this->hasOne(WorkLogPatch::class)->latest('updated_at')->where('status', 'accepted')->where('is_accounted', true);
    }

    public function getDurationAttribute(): int | float
    {
        if ($this->end == null) return 0;
        return Carbon::parse($this->start)->floatDiffInHours($this->end);
    }
}
