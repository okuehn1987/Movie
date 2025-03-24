<?php

namespace App\Models\Traits;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;

trait HasDuration
{
    public function duration(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->end == null) return 0;
                $start = Carbon::parse($this->start)->startOfSecond();
                $end = Carbon::parse($this->end)->startOfSecond();

                $duration = $start->diffInSeconds($end);
                if ($end == Carbon::parse($this->end)->endOfDay()->startOfSecond()) $duration += 1;

                return $duration;
            }
        );
    }

    public function durationThreshold()
    {
        $user = ($this->shift ?? $this)->loadMissing('user:id,date_of_birth')->user;

        return match (true) {
            ($this->duration / 3600) > 6 && $user->age >= 18 => 6,
            ($this->duration / 3600) > 4.5 && $user->age < 18 => 4.5,
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

    public function missingBreakDuration(): Attribute
    {
        return Attribute::make(
            get: fn() => max(0, min($this->requiredBreakDuration(), $this->duration - $this->durationThreshold()))
        )->shouldCache();
    }
}
