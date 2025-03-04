<?php

namespace App\Models;

use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Casts\Attribute;
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
        $query = fn($q) => $q->where('status', 'accepted');
        $this->loadMissing([
            'workLogs' => $query,
            'workLogs.currentAcceptedPatch',
            'travelLogs' => $query,
            'travelLogs.currentAcceptedPatch',
        ]);

        return Attribute::make(
            get: fn() => $this->workLogs->map(fn($w) => $w->currentAcceptedPatch ?? $w)
                ->merge($this->travelLogs->map(fn($w) => $w->currentAcceptedPatch ?? $w))
        )->shouldCache();
    }

    public function getStartAttribute()
    {
        return Carbon::parse($this->attributes['start'])->startOfSecond();
    }

    public function getEndAttribute()
    {
        return Carbon::parse($this->attributes['end'])->startOfSecond();
    }

    public function getDurationAttribute()
    {
        $start = Carbon::parse($this->start);
        return $start->diffInSeconds($this->end);
    }

    public function getHasEndedAttribute()
    {
        return
            !$this->workLogs()->whereNull('end')->exists() &&
            Carbon::parse($this->end)->lte(Carbon::now()->subHours(self::$MINIMUM_SHIFT_SEPARATION_TIME_IN_HOURS));
    }

    /** 
     * @param \Illuminate\Support\Collection<
     *  int,
     *  \App\Models\WorkLog | \App\Models\WorkLogPatch |
     *  \App\Models\TravelLog | \App\Models\TravelLogPatch
     * > 
     * $entries */
    public function breakDuration(\Illuminate\Support\Collection $entries)
    {
        return $this->duration - self::workDuration($entries);
    }

    /** 
     * @param \Illuminate\Support\Collection<
     *  int,
     *  \App\Models\WorkLog | \App\Models\WorkLogPatch |
     *  \App\Models\TravelLog | \App\Models\TravelLogPatch
     * > 
     * $entries */
    public static function workDuration(\Illuminate\Support\Collection $entries)
    {
        return $entries->sum('duration');
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

    public function durationThreshold(int $duration)
    {
        $this->loadMissing('user');
        return match (true) {
            ($duration / 3600) > 9 && $this->user->age >= 18 => 9,
            ($duration / 3600) > 6 => 6,
            ($duration / 3600) > 4.5 && $this->user->age < 18 => 4.5,
            default => 0,
        } * 3600;
    }

    /** 
     * @param \Illuminate\Support\Collection<
     *  int,
     *  \App\Models\WorkLog | \App\Models\WorkLogPatch |
     *  \App\Models\TravelLog | \App\Models\TravelLogPatch
     * >  | null
     * $entries */
    public function missingBreakDuration(?\Illuminate\Support\Collection $entries = null)
    {
        $entries ??= $this->entries;
        $workDuration = self::workDuration($entries);
        $breakDuration = $this->breakDuration($entries);
        $val = min((
                ($workDuration - $this->durationThreshold($workDuration)) +
                max(
                    0,
                    // get the missing break from the previous threshold
                    $this->requiredBreakDuration($this->durationThreshold($workDuration))
                        - $breakDuration
                )
            ),
            max(
                0,
                $this->requiredBreakDuration($workDuration) - $breakDuration
            )
        );
        return $val;
    }

    /** 
     * @param \App\Models\WorkLog | \App\Models\WorkLogPatch |
     *  \App\Models\TravelLog | \App\Models\TravelLogPatch |
     *  \App\Models\Absence | \App\Models\AbsencePatch 
     *  $model 
     */
    public static function computeAffected(Model $model)
    {
        DB::transaction(function () use ($model) {
            $modelClass = match (true) {
                $model instanceof WorkLog => WorkLog::class,
                $model instanceof WorkLogPatch => WorkLogPatch::class,
                $model instanceof TravelLog => TravelLog::class,
                $model instanceof TravelLogPatch => TravelLogPatch::class,
                    // $model instanceof Absence => Absence::class, // TODO: handle differently
                    // $model instanceof AbsencePatch => AbsencePatch::class, // TODO: handle differently
                default => throw new Exception('Invalid model type')
            };

            if (!$model->accepted_at) return;

            Shift::lockForUpdate()->where('user_id', $model->user_id)->get();

            /** @var \Illuminate\Support\Collection<int, \App\Models\Shift>*/
            $affectedShifts = Shift::where('user_id', $model->user_id)
                ->where('start', '<=', Carbon::parse($model->end)->addHours(self::$MINIMUM_SHIFT_SEPARATION_TIME_IN_HOURS))
                ->where('end', '>=', Carbon::parse($model->start)->subHours(self::$MINIMUM_SHIFT_SEPARATION_TIME_IN_HOURS))
                ->get();

            $currentEntry = $modelClass::find($model->id) ?? $model;
            $baseLog = $currentEntry?->log ?? $currentEntry;
            /** @var Model | null $previousModel */
            $previousModel = match (true) {
                $model instanceof WorkLog, $model instanceof TravelLog => $baseLog,
                $model instanceof WorkLogPatch, $model instanceof TravelLogPatch => $baseLog?->currentAcceptedPatch ?? $baseLog,
            };

            /** @var Shift|null $originShift */
            $originShift = Shift::whereId($previousModel->shift_id)->first(); //maybe crashed for absences because they dont have a shift_id

            $oldShifts = collect([$originShift, ...$affectedShifts])
                ->unique('id')
                ->filter(fn($s) => $s !== null);

            $newShifts = self::handleModelMovement($model, $previousModel, $oldShifts);

            $affectedDays = $oldShifts->flatMap(
                fn($s) =>
                collect([Carbon::parse($s->start)->startOfDay(), Carbon::parse($s->end)->startOfDay()])
            )->merge([
                Carbon::parse($model->start)->startOfDay(),
                Carbon::parse($model->end)->startOfDay()
            ])->unique();

            $diffToApply = 0;
            foreach ($affectedDays as $day) {
                $query =
                    fn($q) =>
                    $q->where('user_id', $model->user_id)
                        ->where('accepted_at', '<', $model->accepted_at)
                        ->whereBetween('start', [$day->copy()->startOfDay(), $day->copy()->endOfDay()]);

                $workLogsOfAffectedDay = WorkLog::doesntHave('currentAcceptedPatch')->where($query)->get();
                $travelLogsOfAffectedDay = TravelLog::doesntHave('currentAcceptedPatch')->where($query)->get();

                $workLogPatchesOfAffectedDay = WorkLogPatch::with('log.currentAcceptedPatch')
                    ->where($query)
                    ->get()
                    ->filter(fn($p) => $p->log->currentAcceptedPatch->id == $p->id);
                $travelLogPatchesOfAffectedDay = TravelLogPatch::with('log.currentAcceptedPatch')
                    ->where($query)
                    ->get()
                    ->filter(fn($p) => $p->log->currentAcceptedPatch->id == $p->id);

                $oldIstForAffectedDay = $workLogsOfAffectedDay
                    ->merge($workLogPatchesOfAffectedDay)
                    ->merge($travelLogsOfAffectedDay)
                    ->merge($travelLogPatchesOfAffectedDay)
                    ->sum('accountableDuration');

                $sollForAffectedDay = $model->user->getSollsekundenForDate($day);

                $entriesOfUnaffectedShifts = $model->user->getEntriesForDate($day)
                    ->filter(
                        fn($e) => !$newShifts->flatMap(fn($s) => $s['entries'])
                            ->merge([$previousModel])
                            ->contains(fn($se) => $se->is($e))
                    );

                $istForNewShifts = $newShifts->flatMap(
                    fn($s) => $s['entries']
                        ->filter(
                            fn($e) =>
                            Carbon::parse($e->start)->between($day->copy()->startOfDay(), $day->copy()->endOfDay())
                        )
                )->sum('accountableDuration');

                $newIstForAffectedDay = $entriesOfUnaffectedShifts->sum('accountableDuration') + $istForNewShifts;

                //if ist > soll overtime can be added immediatly else schedule will subtract the missing amount at 0:00 
                if ($day->startOfDay() == Carbon::parse($model->accepted_at)->startOfDay())
                    $diffToApply += max($sollForAffectedDay, $newIstForAffectedDay) - max($sollForAffectedDay, $oldIstForAffectedDay);
                else
                    $diffToApply += $newIstForAffectedDay - $oldIstForAffectedDay;
            }

            $previousMissingBreakDuration = $oldShifts->map->missingBreakDuration()->sum();
            $newMissingBreakDuration = $newShifts->map(fn($s) => $s['shift']->missingBreakDuration($s['entries']))->sum();
            $breakDurationChange =  $previousMissingBreakDuration - $newMissingBreakDuration;

            $diffToApply += $breakDurationChange;

            $transactionDescription = match (true) {
                $model instanceof WorkLog => 'neuer Buchung',
                $model instanceof WorkLogPatch => 'Zeitkorrektur',
                $model instanceof TravelLog => 'neuer Dienstreise',
                $model instanceof TravelLogPatch => 'Dienstreisekorrektur',
                $model instanceof Absence => 'neuer Abwesenheit',
                $model instanceof AbsencePatch => 'Abwesenheitskorrektur',
            };
            $model->user->defaultTimeAccount->addBalance(
                $diffToApply,
                'Berechnung für ' .
                    Carbon::parse($newShifts->map(fn($s) => $s['shift'])->min('start'))->format('d.m.Y H:i') .
                    ' - ' .
                    Carbon::parse($newShifts->map(fn($s) => $s['shift'])->max('end'))->format('d.m.Y H:i') .
                    ' aufgrund von ' . $transactionDescription
            );

            $baseLog?->updateQuietly(['shift_id' => $model->shift_id]);
            $oldShifts->each->delete();
        });
    }

    /**
     *  @param \Illuminate\Support\Collection<int, \App\Models\Shift> $oldShifts
     *  @param \App\Models\WorkLog | \App\Models\WorkLogPatch | \App\Models\TravelLog | \App\Models\TravelLogPatch $model 
     *  @param \App\Models\WorkLog | \App\Models\WorkLogPatch | \App\Models\TravelLog | \App\Models\TravelLogPatch $previousModel 
     */
    public static function handleModelMovement($model, $previousModel, $oldShifts)
    {
        /** @var \Illuminate\Support\Collection<
         *  int, 
         *    array{
         *     shift: \App\Models\Shift,
         *     entries: \Illuminate\Support\Collection<
         *         int,
         *         \App\Models\WorkLog | \App\Models\WorkLogPatch | \App\Models\TravelLog | \App\Models\TravelLogPatch
         *     >
         *    }
         *  >
         */
        $newShifts = collect([]);

        $allEntries = $oldShifts->flatMap(fn($s) => $s->entries)
            ->filter(fn($e) => !$previousModel || $e->id != $previousModel->id)
            ->merge([$model])
            ->sortBy('start');

        $shiftWithEntries = [
            'shift' => Shift::create([
                'user_id' => $model->user_id,
                'is_accounted' => false,
                'start' => $model->start,
                'end' => $model->end
            ]),
            'entries' => collect([])
        ];

        foreach ($allEntries as $entry) {
            $prev = $shiftWithEntries['entries']->last();
            if (!$prev || Carbon::parse($entry->start)->subHours(self::$MINIMUM_SHIFT_SEPARATION_TIME_IN_HOURS) < $prev->end) {
                $shiftWithEntries['shift']->update([
                    'start' => min($shiftWithEntries['shift']->start, $entry->start),
                    'end' => max($shiftWithEntries['shift']->end, $entry->end),
                ]);
                $shiftWithEntries['entries']->push($entry);
            } else {
                $newShifts->push($shiftWithEntries);
                $shiftWithEntries = [
                    'shift' => Shift::create([
                        'user_id' => $entry->user_id,
                        'is_accounted' => false,
                        'start' => $entry->start,
                        'end' => $entry->end,
                    ]),
                    'entries' => collect([$entry])
                ];
            }
            $entry->shift_id = $shiftWithEntries['shift']->id;
            $entry->saveQuietly();
        }
        $newShifts->push($shiftWithEntries);

        return $newShifts;
    }
}
