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

    public static $MINIMUM_SHIFT_SEPARATION_TIME_IN_HOURS = 9;

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
        return Attribute::make(
            get: function () {
                $workLogs = $this->workLogs()
                    ->doesntHave('currentAcceptedPatch')
                    ->where('status', 'accepted')->get();
                $travelLogs = $this->travelLogs()
                    ->doesntHave('currentAcceptedPatch')
                    ->where('status', 'accepted')->get();
                $workLogPatches = $this->workLogPatches()
                    ->with('log.currentAcceptedPatch')
                    ->where('status', 'accepted')->get()
                    ->filter(fn($p) => $p->log->currentAcceptedPatch->is($p));
                $travelLogPatches = $this->travelLogPatches()
                    ->with('log.currentAcceptedPatch')
                    ->where('status', 'accepted')->get()
                    ->filter(fn($p) => $p->log->currentAcceptedPatch->is($p));
                return  collect($workLogs)->merge($travelLogs)->merge($workLogPatches)->merge($travelLogPatches);
            }
        )->shouldCache();
    }

    public function getDurationAttribute()
    {
        $start = Carbon::parse($this->start)->startOfSecond();
        $end = Carbon::parse($this->end)->startOfSecond();

        $duration = $start->diffInSeconds($end);
        if ($end == Carbon::parse($this->end)->endOfDay()->startOfSecond()) $duration += 1;

        return $duration;
    }

    public function getHasEndedAttribute()
    {
        return
            !$this->workLogs()->whereNull('end')->exists() &&
            Carbon::parse($this->end)->lte(now()->subHours(self::$MINIMUM_SHIFT_SEPARATION_TIME_IN_HOURS));
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
        $entries = $entries->filter(fn($e) => $e->end !== null);
        $duration = 0;
        $currentEnd = $entries->min('start');
        foreach ($entries->sortBy('start') as $entry) {
            $end = Carbon::parse(max($currentEnd, $entry->end))->startOfSecond();
            $start = Carbon::parse(max($currentEnd, $entry->start))->startOfSecond();

            $duration += $start->diffInSeconds($end);

            $currentEnd = $entry->end;
        }

        foreach ($entries->unique(fn($e) => Carbon::parse($e->start)->startOfDay()) as $entry) {
            if (Carbon::parse($entry->end)->startOfSecond() == Carbon::parse($entry->end)->endOfDay()->startOfSecond()) $duration += 1;
        }
        return $duration;
    }

    public function currentWorkDuration(): Attribute
    {
        return Attribute::make(
            get: function () {
                return self::workDuration($this->entries);
            }
        );
    }

    public function requiredBreakDuration(int $duration)
    {
        $this->loadMissing('user:id,date_of_birth');
        return match ($this->durationThreshold($duration) / 3600) {
            0
            => 0,
            4.5
            => 0.5,
            6
            => $this->user->age >= 18 ? 0.5 : 1,
            9
            => 0.75,
        } * 3600;
    }

    public function durationThreshold(int $duration)
    {
        $this->loadMissing('user:id,date_of_birth');
        return match (true) {
            ($duration / 3600) > 9 && $this->user->age >= 18
            => 9,
            ($duration / 3600) > 6
            => 6,
            ($duration / 3600) > 4.5 && $this->user->age < 18
            => 4.5,
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
        $missingShiftBreakDuration = min((
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

        return $missingShiftBreakDuration;
    }

    public function accountableDuration(?\Illuminate\Support\Collection $entries = null)
    {
        $entries ??= $this->entries;
        $missingBreak = max(
            $this->missingBreakDuration(),
            $entries->sum('missingBreakDuration'),
        );
        $workDuration = self::workDuration($entries);

        return [
            'missingBreakDuration' => $missingBreak,
            'workDuration' => $workDuration
        ];
    }

    public static function lockFor(User $user)
    {
        Shift::lockForUpdate()->where('user_id', $user->id)->get();
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
            [$modelClass, $type, $variant, $baseClass] = match (true) {
                $model instanceof WorkLog
                => [WorkLog::class, 'work', 'log', WorkLog::class],
                $model instanceof WorkLogPatch
                => [WorkLogPatch::class, 'work', 'patch', WorkLog::class],
                $model instanceof TravelLog
                => [TravelLog::class, 'work', 'log', TravelLog::class],
                $model instanceof TravelLogPatch
                => [TravelLogPatch::class, 'work', 'patch', TravelLog::class],
                $model instanceof Absence
                => [Absence::class, 'absence', 'log', Absence::class],
                $model instanceof AbsencePatch
                => [AbsencePatch::class, 'absence', 'patch', Absence::class],
                default => throw new Exception('Invalid model type for shift calculation'),
            };

            if (!$model->accepted_at) return;

            Shift::lockFor($model->user);
            /** @var \Illuminate\Support\Collection<int,\App\Models\Shift>*/
            $affectedShifts = match ($type) {
                'work'
                => Shift::where('user_id', $model->user_id)
                    ->where('start', '<=', Carbon::parse($model->end)->addHours(self::$MINIMUM_SHIFT_SEPARATION_TIME_IN_HOURS))
                    ->where('end', '>=', Carbon::parse($model->start)->subHours(self::$MINIMUM_SHIFT_SEPARATION_TIME_IN_HOURS))
                    ->get(),
                'absence'
                => Shift::where('user_id', $model->user_id)
                    ->whereDate('start', '<=', $model->end)
                    ->whereDate('end', '>=', $model->start)
                    ->whereDate('end', '<=', $model->end)
                    ->get(),
            };
            $currentEntry = $modelClass::find($model->id) ?? $model;
            $baseLog = match ($variant) {
                'log'
                => $currentEntry,
                'patch'
                //                   eg: Absence   ::find($model->absence_id                         )
                => $currentEntry->log ?? $baseClass::find($model->{(new $baseClass)->getForeignKey()}),
            };
            /** @var Model $previousModel */
            $previousModel = match ($variant) {
                'log'
                => $baseLog,
                'patch'
                => $baseLog->currentAcceptedPatch ?? ($baseLog->status == 'accepted' ? $baseLog : $model),
            };

            /** @var \Illuminate\Support\Collection<int,\App\Models\Shift>*/
            $originShifts = match ($type) {
                'work'
                => Shift::whereId($previousModel->shift_id)->get(),
                'absence'
                => Shift::where('user_id', $model->user_id)
                    ->whereDate('start', '<=', $previousModel->end)
                    ->whereDate('end', '>=', $previousModel->start)
                    ->whereDate('end', '<=', $previousModel->end)
                    ->get()
            };

            $oldShifts = $originShifts->merge($affectedShifts)->unique('id');

            $previousMissingBreakDuration = match ($type) {
                'work'
                => $oldShifts->map(fn($s) => $s->accountableDuration()['missingBreakDuration'])->sum(),
                'absence'
                => $oldShifts->map(fn($s) => max(0, $s->missingBreakDuration() -  $s->entries->sum('missingBreakDuration')))->sum()
            };

            /** @var \Illuminate\Support\Collection<int, 
             *  array{shift: \App\Models\Shift,
             *    entries: \Illuminate\Support\Collection<
             *      int,
             *      \App\Models\WorkLog|\App\Models\WorkLogPatch|\App\Models\TravelLog|\App\Models\TravelLogPatch>
             *  }
             * > $newShifts */
            $newShifts = match ($type) {
                'work'
                => self::handleModelMovement($model, $previousModel, $oldShifts),
                'absence'
                => collect([])
            };

            $newMissingBreakDuration = $newShifts->map(
                fn($s) => $s['shift']->accountableDuration($s['entries'])['missingBreakDuration']
            )->sum();
            $breakDurationChange = $previousMissingBreakDuration - $newMissingBreakDuration;

            $affectedDays = $oldShifts->flatMap(
                fn($s) =>
                collect([Carbon::parse($s->start)->startOfDay(), Carbon::parse($s->end)->startOfDay()])
            )->merge(
                collect(
                    range(0, floor(Carbon::parse($model->start)->diffInDays($model->end)))
                )->map(
                    fn($i) => Carbon::parse($model->start)->addDays($i)->startOfDay()
                )
            )->merge(
                collect(
                    range(0, floor(Carbon::parse($previousModel->start)->diffInDays($previousModel->end)))
                )->map(
                    fn($i) => Carbon::parse($previousModel->start)->addDays($i)->startOfDay()
                )
            )->unique();

            $diffToApply = 0;
            foreach ($affectedDays as $day) {
                if ($type == 'absence' && $day->gt(now()->endOfDay())) continue;

                $AbbauGleitzeitkonto = AbsenceType::inOrganization()->where('type', 'Abbau Gleitzeitkonto')->first();

                $previousAbsencesForDay = $model->user->getAbsencesForDate($day)
                    ->filter(fn($a) => !$a->is($model) && $a->absence_type_id !== $AbbauGleitzeitkonto->id);

                $hasAbsenceForDay = $previousAbsencesForDay->isNotEmpty();
                $hasAbbauGleitzeitkontoForDay = $previousAbsencesForDay->contains(
                    fn($a) => $a->absence_type_id === $AbbauGleitzeitkonto->id
                ) || $type == 'absence' && $model->absence_type_id === $AbbauGleitzeitkonto->id;
                //erste abwesenheit des Tages, die nicht Abbau Gleitzeitkonto ist
                $hasNewAbsence = $type == 'absence' && $previousAbsencesForDay->isEmpty() && $model->absence_type_id !== $AbbauGleitzeitkonto->id;

                $oldEntriesForAffectedDay = match ($type) {
                    'work'
                    => $model->user->getEntriesForDate($day)
                        ->merge([$previousModel])
                        ->filter(
                            fn($e) =>
                            $e !== null &&
                                !$e->is($model) &&
                                $e->shift_id != null &&
                                Carbon::parse($e->accepted_at)->startOfSecond() <=
                                Carbon::parse($model->accepted_at)->startOfSecond()
                        ),
                    'absence'
                    => $hasNewAbsence ?
                        $model->user->getEntriesForDate($day)->filter(
                            fn($e) =>
                            Carbon::parse($e->accepted_at)->startOfSecond() <= Carbon::parse($model->accepted_at)->startOfSecond()
                        ) :
                        collect([])
                };

                $oldIstForAffectedDay = self::workDuration($oldEntriesForAffectedDay);

                $sollForAffectedDay = $model->user->getSollsekundenForDate($day);

                $entriesOfUnaffectedShifts = $model->user->getEntriesForDate($day)
                    ->filter(
                        fn($e) => !$newShifts->flatMap(fn($s) => $s['entries'])
                            ->merge([$previousModel])
                            ->contains(fn($se) => $se->is($e))
                    );

                // 0 if tpye is absence
                $entriesOfNewShiftsForDay = $newShifts->flatMap(
                    fn($s) => $s['entries']
                        ->filter(
                            fn($e) =>
                            Carbon::parse($e->start)->between($day->copy()->startOfDay(), $day->copy()->endOfDay())
                        )
                );

                $newIstForAffectedDay = match ($type) {
                    'work'
                    => self::workDuration($entriesOfUnaffectedShifts) + self::workDuration($entriesOfNewShiftsForDay),
                    'absence'
                    => max($oldIstForAffectedDay, $sollForAffectedDay)
                };

                //if ist > soll overtime can be added immediatly else schedule will subtract the missing amount at 0:00 
                if ($day->isSameDay(now()) || $hasAbsenceForDay || $hasAbbauGleitzeitkontoForDay)
                    $diffToApply += max($sollForAffectedDay, $newIstForAffectedDay) - max($sollForAffectedDay, $oldIstForAffectedDay);
                else
                    $diffToApply += $newIstForAffectedDay - $oldIstForAffectedDay;
            }

            $diffToApply += $breakDurationChange;

            $transactionDescription = match (true) {
                $model instanceof WorkLog
                => 'neuer Buchung',
                $model instanceof WorkLogPatch
                => 'Zeitkorrektur',
                $model instanceof TravelLog
                => 'neuer Dienstreise',
                $model instanceof TravelLogPatch
                => 'Dienstreisekorrektur',
                $model instanceof Absence
                => 'neuer Abwesenheit',
                $model instanceof AbsencePatch
                => 'Abwesenheitskorrektur',
            };

            //TODO: message doesnt make sense for multiple affected shifts
            $transactionMessage = match ($type) {
                'work' =>  'Berechnung für ' .
                    Carbon::parse($newShifts->map(fn($s) => $s['shift'])->min('start') ?? $model->start)->format('d.m.Y H:i') .
                    ' - ' .
                    Carbon::parse($newShifts->map(fn($s) => $s['shift'])->max('end') ?? $model->end)->format('d.m.Y H:i') .
                    ' aufgrund von ' . $transactionDescription,
                'absence' => 'Berechnung für ' .
                    ($model->start == $model->end ?
                        Carbon::parse($model->start)->format('d.m.Y') :
                        Carbon::parse($model->start)->format('d.m.Y') . ' - ' . Carbon::parse($model->end)->format('d.m.Y')
                    ) .
                    ' aufgrund von ' . $transactionDescription,
            };

            $model->user->defaultTimeAccount->addBalance(
                $diffToApply,
                $transactionMessage
            );

            if ($type == 'work') {
                $baseLog?->updateQuietly(['shift_id' => $model->shift_id]);
                $oldShifts->each->delete();
                $newShifts->each(fn($s) => $s['shift']->updateQuietly(['is_accounted' => true]));
            };
        });
    }

    /**
     *  @param \Illuminate\Support\Collection<int,\App\Models\Shift> $oldShifts
     *  @param \App\Models\WorkLog|\App\Models\WorkLogPatch|\App\Models\TravelLog|\App\Models\TravelLogPatch $model 
     *  @param \App\Models\WorkLog|\App\Models\WorkLogPatch|\App\Models\TravelLog|\App\Models\TravelLogPatch $previousModel 
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
            ->filter(fn($e) => !$e->is($previousModel))
            ->merge([$model])
            ->filter(fn($e) => !property_exists($e, 'type') || $e->type !== 'delete')
            ->sortBy('start');

        $shiftWithEntries = [
            'shift' => Shift::create([
                'user_id' => $model->user_id,
                'is_accounted' => false,
                'start' => max($allEntries->min('start'), $allEntries->max('end')),
                'end' => $allEntries->min('start')
            ]),
            'entries' => collect([])
        ];

        foreach ($allEntries as $entry) {
            $prev = $shiftWithEntries['entries']->last();
            if (!$prev || Carbon::parse($entry->start)->subHours(self::$MINIMUM_SHIFT_SEPARATION_TIME_IN_HOURS)->lte($prev->end)) {
                $shiftWithEntries['shift']->update([
                    'start' => min($shiftWithEntries['shift']->start, $entry->start),
                    'end' => max($shiftWithEntries['shift']->end, $entry->end, $entry->start),
                ]);
                $shiftWithEntries['entries']->push($entry);
            } else {
                $newShifts->push($shiftWithEntries);
                $shiftWithEntries = [
                    'shift' => Shift::create([
                        'user_id' => $entry->user_id,
                        'is_accounted' => false,
                        'start' => $entry->start,
                        'end' => max($entry->start, $entry->end),
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
