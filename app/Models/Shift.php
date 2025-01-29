<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Shift extends Model
{
    use HasFactory;

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
        return $start->floatDiffInHours($end);
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
        return Carbon::parse($this->end)->lte(Carbon::now()->subHours(9));
    }

    public function getBreakDurationAttribute()
    {
        return $this->duration - $this->workLogs->sum('duration');
    }

    public function getRequiredBreakDurationAttribute()
    {
        if ($this->duration <= 4.5) return 0;
        if ($this->duration <= 6 && $this->user->age >= 18) return 0;

        if ($this->duration <= 6 && $this->user->age < 18) return 0.5;
        if ($this->duration > 6 && $this->user->age < 18) return 1;

        if ($this->duration <= 9) return 0.5;
        return 0.75;
    }

    public function accountRequiredBreakAsTransaction()
    {
        if (!$this->has_ended || $this->is_accounted) return;
        DB::transaction(function () {
            if ($this->break_duration < $this->required_break_duration) {
                $this
                    ->user
                    ->defaultTimeAccount
                    ->addBalance(
                        ($this->required_break_duration - $this->break_duration) * -1,
                        'Pflichtpause für Schicht gestartet am ' . Carbon::parse($this->start)->format('d.m.Y H:i')
                    );
            }
            $this['is_accounted'] = true;
            $this->save();
        });
    }
}
