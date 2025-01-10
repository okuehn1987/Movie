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

    public static function boot()
    {
        parent::boot();
        self::updating(function ($model) {
            if (!$model->end) return;

            $workLogs = WorkLog::where('end', '>=', Carbon::parse($model->start)->subHours(6.5));

            if ($workLogs->sum('duration') > 6) {
                $sum = $workLogs->sum('duration');
                for ($time = Carbon::parse($model->start)->startOfMinute(); $time->lte(Carbon::parse($model->end)); $time->addMinute()) {
                    if (Carbon::parse($model->start)->floatDiffInHours($time) + $sum >= 6) {
                        ForcedWorkLogBreak::create([
                            'start' => $time,
                            'end' => $time->copy()->addMinutes(15),
                            'work_log_id' => $model->id
                        ]);
                    }
                }
            };
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function workLogPatches()
    {
        return $this->hasMany(WorkLogPatch::class);
    }

    public function forcedWorkLogBreaks()
    {
        return $this->hasMany(ForcedWorkLogBreak::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne<\App\Models\WorkLogPatch, \App\Models\WorkLog>
     */
    public function currentAccountedPatch()
    {
        return $this->hasOne(WorkLogPatch::class)->latest('accepted_at')->where('status', 'accepted')->where('is_accounted', true);
    }

    public function getDurationAttribute(): int | float
    {
        if ($this->end == null) return 0;
        $forcedBreaks = ForcedWorkLogBreak::where('work_log_id', $this->id)->get();
        $forcedBreaks->sum('duration');
        return Carbon::parse($this->start)->floatDiffInHours($this->end);
    }
}
