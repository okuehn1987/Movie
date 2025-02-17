<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Shift extends Model
{
    use HasFactory;

    private static $MINIMUM_SHIFT_SEPARATION_TIME_IN_HOURS = 9;

    protected $guarded = [];

    public function workLogs()
    {
        return $this->hasMany(WorkLog::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getDurationAttribute()
    {
        $start = Carbon::parse($this->workLogs->min('start'));
        $end = Carbon::parse($this->workLogs->max('end'));
        return $start->diffInSeconds($end);
    }

    public function getEndAttribute()
    {
        return $this->workLogs->max('end');
    }

    public function getStartAttribute()
    {
        return $this->workLogs->min('start');
    }

    public function getHasEndedAttribute()
    {
        return
            !$this->workLogs()->whereNull('end')->exists() &&
            Carbon::parse($this->end)->lte(Carbon::now()->subHours(self::$MINIMUM_SHIFT_SEPARATION_TIME_IN_HOURS));
    }

    public function getBreakDurationAttribute()
    {
        return $this->duration - $this->work_duration;
    }

    public function getWorkDurationAttribute()
    {
        return $this->workLogs->sum('duration');
    }

    public function requiredBreakDuration(int $duration)
    {
        return match ($this->durationThreshold($duration) / 3600) {
            0 => 0,
            4.5 => 0.5,
            6 => $this->user->age >= 18 ? 0.5 : 1,
            9 => 0.75,
        } * 3600;
    }

    public function durationThreshold(float $duration)
    {
        return match (true) {
            ($duration / 3600) > 9 && $this->user->age >= 18 => 9,
            ($duration / 3600) > 6 => 6,
            ($duration / 3600) > 4.5 && $this->user->age < 18 => 4.5,
            default => 0,
        } * 3600;
    }

    public function getMissingBreakDurationAttribute()
    {
        return min((
                ($this->work_duration - $this->durationThreshold($this->work_duration)) +
                max(
                    0,
                    // get the missing break from the previous threshold
                    $this->requiredBreakDuration($this->durationThreshold($this->work_duration))
                        - $this->break_duration
                )
            ),
            max(
                0,
                $this->requiredBreakDuration($this->work_duration) - $this->break_duration
            )
        );
    }

    public function accountRequiredBreakAsTransaction()
    {
        if (!$this->has_ended || $this->is_accounted) return;
        DB::transaction(function () {
            if ($this->break_duration < $this->requiredBreakDuration($this->work_duration)) {
                $this
                    ->user
                    ->defaultTimeAccount
                    ->addBalance(
                        -1 * $this->missing_break_duration,
                        'Pflichtpause für Schicht gestartet am ' . Carbon::parse($this->start)->format('d.m.Y H:i')
                    );
            }
            $this['is_accounted'] = true;
            $this->save();
        });
    }
}
