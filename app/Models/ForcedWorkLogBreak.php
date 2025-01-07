<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForcedWorkLogBreak extends Model
{
    use HasFactory;


    public function workLog()
    {
        return $this->belongsTo(WorkLog::class);
    }

    public function getDurationAttribute(): int | float
    {
        return Carbon::parse($this->start)->floatDiffInHours($this->end);
    }
}
