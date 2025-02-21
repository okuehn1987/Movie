<?php

namespace App\Models;

use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
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

    public function workLogPatches()
    {
        return $this->hasMany(WorkLogPatch::class);
    }

    public function travelLogs()
    {
        return $this->hasMany(TravelLog::class);
    }

    public function travelLogPatches()
    {
        return $this->hasMany(TravelLogPatch::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function entries(): Attribute
    {
        $this->load(['workLogs', 'workLogPatches', 'travelLogs', 'travelLogPatches']);

        return Attribute::make(
            get: fn() => $this->workLogs
                ->merge($this->workLogPatches)
                ->merge($this->travelLogs)
                ->merge($this->travelLogPatches)
        )->shouldCache();
    }

    public function getDurationAttribute()
    {
        $start = Carbon::parse($this->entries->min('start'));
        $end = Carbon::parse($this->entries->max('end'));
        return $start->diffInSeconds($end);
    }

    public function getEndAttribute()
    {
        return $this->entries->max('end');
    }

    public function getStartAttribute()
    {
        return $this->entries->min('start');
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
        return $this->entries->sum('duration');
    }

    public function requiredBreakDuration(float $duration)
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

    public function test()
    {
        $builder = fn($q) => $q->lockForUpdate()->where('start', '<', $this->end)->where('end', '>', $this->start);

        $this->user()->with([
            'workLogs' => fn($q) => $builder($q)->select('id', 'shift_id', 'user_id'),
            'workLogPatches' => fn($q) => $builder($q)->select('id', 'shift_id', 'user_id'),
            'travelLogs' => fn($q) => $builder($q)->select('id', 'shift_id', 'user_id'),
            'travelLogPatches' => fn($q) => $builder($q)->select('id', 'shift_id', 'user_id'),
            'absences' => fn($q) => $builder($q)->select('id', 'user_id'),
            'absencePatches' => fn($q) => $builder($q)->select('id', 'user_id'),
        ])->lockForUpdate()->first();
    }

    public function accountAsTransaction()
    {
        if (!$this->has_ended || $this->is_accounted) return;
        DB::transaction(function () {

            $query = fn($q) => $q->lockForUpdate()->where('start', '<', $this->end)->where('end', '>', $this->start);

            $this->user()->with([
                'workLogs:id,shift_id,user_id' => $query,
                'workLogPatches:id,shift_id,user_id' => $query,
                'travelLogs:id,shift_id,user_id' => $query,
                'travelLogPatches:id,shift_id,user_id' => $query,
                'absences:id,user_id' => $query,
                'absencePatches:id,user_id' => $query,
            ])->lockForUpdate()->first();

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

    public static function computeAffected(Model $model)
    {
        if (!collect([
            WorkLog::class,
            WorkLogPatch::class,
            Absence::class,
            AbsencePatch::class,
            TravelLog::class,
            TravelLogPatch::class
        ])->contains(get_class($model)))
            throw new Exception('Model cant affect shifts.');
        $affectedShifts = Shift::where('user_id', $model->user_id)
            ->where(
                fn(Builder $query) =>
                $query->whereId($model->shift_id)
                    ->orWhereBetween('start', [
                        Carbon::parse($model->start)->subHours(self::$MINIMUM_SHIFT_SEPARATION_TIME_IN_HOURS),
                        Carbon::parse($model->end)->addHours(self::$MINIMUM_SHIFT_SEPARATION_TIME_IN_HOURS)
                    ])
                    ->orWhereBetween('end', [
                        Carbon::parse($model->start)->subHours(self::$MINIMUM_SHIFT_SEPARATION_TIME_IN_HOURS),
                        Carbon::parse($model->end)->addHours(self::$MINIMUM_SHIFT_SEPARATION_TIME_IN_HOURS)
                    ])
            )->get();
        // if count == 1 update shift
        // if count > 1 move all entries to first and delete the rest
        if ($affectedShifts->count() == 1) {

            dump(1);
            $shift = $affectedShifts->first();

            dump(min(Carbon::parse($shift->start), Carbon::parse($model->start)), max(Carbon::parse($shift->end), Carbon::parse($model->end)));
            // $shift->update([
            //     'start' => min(Carbon::parse($shift->start), Carbon::parse($model->start)),
            //     'end' => max(Carbon::parse($shift->end), Carbon::parse($model->end)),
            // ]);
            // $shift->accountAsTransaction();
        }
        // else {
        //     $firstShift = $affectedShifts->first();
        //     $affectedShifts->slice(1)->each(function ($shift) use ($firstShift) {
        //         $shift->workLogs->each(fn($workLog) => $workLog->update(['shift_id' => $firstShift->id]));
        //         $shift->workLogPatches->each(fn($workLogPatch) => $workLogPatch->update(['shift_id' => $firstShift->id]));
        //         $shift->travelLogs->each(fn($travelLog) => $travelLog->update(['shift_id' => $firstShift->id]));
        //         $shift->travelLogPatches->each(fn($travelLogPatch) => $travelLogPatch->update(['shift_id' => $firstShift->id]));
        //         $shift->delete();
        //     });
        //     $firstShift->update([
        //         'start' => min(Carbon::parse($firstShift->start), Carbon::parse($model->start)),
        //         'end' => max(Carbon::parse($firstShift->end), Carbon::parse($model->end)),
        //     ]);
        //     $firstShift->accountAsTransaction();
        // }
    }
}
