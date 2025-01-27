<?php

namespace App\Models;

use App\FloorToMinutes;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkLog extends Model
{
    use HasFactory, SoftDeletes;
    use ScopeInOrganization, FloorToMinutes;

    protected $guarded = [];

    protected $casts = ['is_home_office' => 'boolean'];

    public static function boot()
    {
        parent::boot();
        self::updating(function ($model) {
            if (!$model->end) return;

            //if the worklog exceeds 6 hours force the next 30 mins to be break
            if (Carbon::parse($model->start)->floatDiffInHours(Carbon::parse($model->end)) > 6) {

                $maxEnd = Carbon::parse($model->start)->addHours(6)->addMinutes(30);

                if (Carbon::parse($model->end)->lt($maxEnd)) $end = $maxEnd;
                else $end = Carbon::parse($model->end);

                ForcedWorkLogBreak::create([
                    'start' => Carbon::parse($model->start)->addHours(6),
                    'end' => $end,
                    'work_log_id' => $model->id
                ]);
            }
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

    public function nextShiftLog(): WorkLog | null
    {
        return $this->user->workLogs()
            ->where('start', '>', $this->end)
            ->where('start', '<', Carbon::parse($this->end)->addHours(9))
            ->orderBy('start')
            ->first();
    }

    public function previousShiftLog(): WorkLog | null
    {
        return $this->user->workLogs()
            ->where('end', '<', $this->start)
            ->where('end', '>', Carbon::parse($this->start)->subHours(9))
            ->orderByDesc('start')
            ->first();
    }

    public function shiftLogs()
    {
        //get the next and previous shiftLogs recoursively until there is a 9 hour gap
        $shiftLogs = collect([$this]);
        $nextShiftLog = $this->nextShiftLog();
        $previousShiftLog = $this->previousShiftLog();
        if ($nextShiftLog && Carbon::parse($nextShiftLog->start)->floatDiffInHours($this->end) < 9) {
            $shiftLogs = $shiftLogs->merge($nextShiftLog->shiftLogs());
        }
        if ($previousShiftLog && Carbon::parse($this->start)->floatDiffInHours($previousShiftLog->end) < 9) {
            $shiftLogs = $shiftLogs->merge($previousShiftLog->shiftLogs());
        }
        return $shiftLogs;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne<\App\Models\WorkLogPatch, \App\Models\WorkLog>
     */
    public function currentAccountedPatch()
    {
        return $this->hasOne(WorkLogPatch::class)->latest('accepted_at')->where('status', 'accepted')->where('is_accounted', true);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne<\App\Models\WorkLogPatch, \App\Models\WorkLog>
     */
    public function currentAcceptedPatch()
    {
        return $this->hasOne(WorkLogPatch::class)->latest('accepted_at')->where('status', 'accepted');
    }

    public function getDurationAttribute(): int | float
    {
        if ($this->end == null) return 0;
        $forcedBreaks = ForcedWorkLogBreak::where('work_log_id', $this->id)->get();
        $forcedBreaks->sum('duration');
        return Carbon::parse($this->start)->floatDiffInHours($this->end);
    }
}
