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

    public function durationThreshold(int|null $age = null)
    {
        if (is_null($age) && !isset($this->user?->date_of_birth)) $this->loadMissing(['user' => fn($q) => $q->select('id', 'date_of_birth')->withTrashed()]);
        $age ??= $this->user->age;
        return match (true) {
            ($this->duration / 3600) > 6 && $age >= 18 => 6,
            ($this->duration / 3600) > 4.5 && $age < 18 => 4.5,
            default => 0,
        } * 3600;
    }

    public function requiredBreakDuration(int|null $age = null)
    {
        return match ($this->durationThreshold($age) / 3600) {
            6 => 0.25,
            4.5 => 0.25,
            0 => 0,
        } * 3600;
    }

    public function getMissingBreakDuration(int|null $age = null)
    {
        return max(0, min($this->requiredBreakDuration($age), $this->duration - $this->durationThreshold($age)));
    }

    public function missingBreakDuration(): Attribute
    {
        return Attribute::make(
            get: function () {
                $age = isset($this->user) && isset($this->user->date_of_birth) ? $this->user->age : null;
                return $this->getMissingBreakDuration($age);
            }
        )->shouldCache();
    }
}
