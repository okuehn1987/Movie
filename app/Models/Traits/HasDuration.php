<?php

namespace App\Models\Traits;

use Carbon\Carbon;

trait HasDuration
{
    public function getDurationAttribute(): int
    {
        if ($this->end == null) return 0;
        return Carbon::parse($this->start)->diffInSeconds($this->end);
    }

    public function durationThreshold(?int $duration = null)
    {
        $duration ??= $this->duration;
        $this->loadMissing('user:id,date_of_birth');

        return match (true) {
            ($duration / 3600) > 9 && $this->user->age >= 18 => 9,
            ($duration / 3600) > 6 => 6,
            ($duration / 3600) > 4.5 && $this->user->age < 18 => 4.5,
            default => 0,
        } * 3600;
    }

    public function requiredBreakDuration(?int $duration = null)
    {
        $duration ??= $this->duration;
        return match ($this->durationThreshold($duration) / 3600) {
            0 => 0,
            4.5 => 0.5,
            6 => $this->user->age >= 18 ? 0.5 : 1,
            9 => 0.75,
        } * 3600;
    }

    public function getMissingBreakDurationAttribute()
    {
        $missingForCurrentThreshold = min(
            $this->duration,
            $this->durationThreshold($this->duration) + $this->requiredBreakDuration()
        ) - $this->durationThreshold($this->duration);

        $missingForPreviousThreshold = min(
            $this->durationThreshold($this->duration),
            $this->durationThreshold($this->durationThreshold($this->duration)) + $this->requiredBreakDuration($this->durationThreshold($this->duration))
        ) - $this->durationThreshold($this->durationThreshold($this->duration));

        return $missingForCurrentThreshold + $missingForPreviousThreshold;
    }
}
