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
}
