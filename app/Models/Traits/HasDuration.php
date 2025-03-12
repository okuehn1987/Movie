<?php

namespace App\Models\Traits;

use Carbon\Carbon;

trait HasDuration
{
    public function getDurationAttribute(): int
    {
        if ($this->end == null) return 0;
        $duration = Carbon::parse($this->start)->diffInSeconds($this->end);
        if ($this->end == Carbon::parse($this->end)->endOfDay()->startOfSecond()) $duration += 1;
        return $duration;
    }

    public function durationThreshold()
    {
        $this->loadMissing('user:id,date_of_birth');

        return match (true) {
            ($this->duration / 3600) > 6 && $this->user->age >= 18 => 6,
            ($this->duration / 3600) > 4.5 && $this->user->age < 18 => 4.5,
            default => 0,
        } * 3600;
    }

    public function requiredBreakDuration()
    {
        return match ($this->durationThreshold($this->duration) / 3600) {
            6 => 0.25,
            4.5 => 0.25,
            0 => 0,
        } * 3600;
    }

    public function getMissingBreakDurationAttribute()
    {
        return max(0, min($this->requiredBreakDuration(), $this->duration - $this->durationThreshold()));
    }
}
