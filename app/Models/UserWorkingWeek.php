<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserWorkingWeek extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function hasWorkDay(CarbonInterface $day): bool
    {
        return $this->{strtolower($day->englishDayOfWeek)};
    }

    public function getNumberOfWorkingDaysAttribute(): int
    {
        return collect(Carbon::getDays())->filter(fn($day) => $this[strtolower($day)])->count();
    }
}
