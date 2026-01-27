<?php

namespace App\Models;

use App\Enums\Status;
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
                    ->where('status', Status::Accepted)
                    ->get();
                // $travelLogs = $this->travelLogs()
                //     ->doesntHave('currentAcceptedPatch')
                //     ->where('status', Status::Accepted)
                //     ->get();
                $workLogPatches = $this->workLogPatches()
                    ->with('log.currentAcceptedPatch')
                    ->where('status', Status::Accepted)
                    ->whereNot('type', 'delete')
                    ->get()
                    ->filter(fn($p) => $p->log->currentAcceptedPatch->is($p));
                // $travelLogPatches = $this->travelLogPatches()
                //     ->with('log.currentAcceptedPatch')
                //     ->where('status', Status::Accepted)
                //     ->whereNot('type', 'delete')
                //     ->get()
                //     ->filter(fn($p) => $p->log->currentAcceptedPatch->is($p));
                return  collect($workLogs)->merge($workLogPatches);
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
    public static function workDuration(\Illuminate\Support\Collection $entries): int
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

    public function homeofficeDuration(): Attribute
    {
        return Attribute::make(
            get: function () {
                return $this->entries->filter(fn($e) => $e->is_home_office)->sum(fn($e) => $e->duration);
            }
        );
    }

    public function travellogDuration(): Attribute
    {
        return Attribute::make(
            get: function () {
                return $this->entries->filter(fn($e) => $e instanceof TravelLog || $e instanceof TravelLogPatch)->sum(fn($e) => $e->duration);
            }
        );
    }

    public function requiredBreakDuration(int $duration)
    {
        if (!isset($this->user?->date_of_birth)) $this->loadMissing('user:id,date_of_birth');
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
        if (!isset($this->user?->date_of_birth)) $this->loadMissing('user:id,date_of_birth');
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
        if (!isset($this->user)) $this->loadMissing('user:id,date_of_birth');

        $entries ??= $this->entries;

        $missingBreak = max(
            $this->missingBreakDuration(),
            $entries->map->getMissingBreakDuration($this->user->age)->sum(),
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

            if (!$model->accepted_at || $model->status != Status::Accepted) return;

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
                    ->where('start', '<=', Carbon::parse($model->end)->copy()->endOfDay())
                    ->where('end', '>=', Carbon::parse($model->start)->copy()->startOfDay())
                    ->where('end', '<=', Carbon::parse($model->end)->copy()->endOfDay())
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
                => $baseLog->currentAcceptedPatch ?? ($baseLog->status == Status::Accepted ? $baseLog : $model),
            };

            /** @var \Illuminate\Support\Collection<int,\App\Models\Shift>*/
            $originShifts = match ($type) {
                'work'
                => Shift::whereId($previousModel->shift_id)->get(),
                'absence'
                => Shift::where('user_id', $model->user_id)
                    ->where('start', '<=', Carbon::parse($previousModel->end)->copy()->endOfDay())
                    ->where('end', '>=', Carbon::parse($previousModel->start)->copy()->startOfDay())
                    ->where('end', '<=', Carbon::parse($previousModel->end)->copy()->endOfDay())
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
                =>  match (true) {
                    $variant == 'log' || $model->type == 'patch' => collect([]),
                    $model->type == 'delete' => self::handleModelMovement($model, $previousModel, $oldShifts),
                }
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
            )->unique()->filter(fn($d) => $d->gte('1970-01-02'));

            $diffToApply = $breakDurationChange;
            $dayChanges = (object)[];
            foreach ($affectedDays as $day) {
                if ($type == 'absence' && $day->gt(now()->endOfDay())) continue;

                $AbbauGleitzeitkontoIds = AbsenceType::inOrganization()->where('type', 'Abbau Gleitzeitkonto')->withTrashed()->pluck('id');

                $previousAbsencesForDay = $model->user->getAbsencesForDate($day)
                    ->filter(
                        fn($a) => !$a->is($model) && $AbbauGleitzeitkontoIds->doesntContain($a->absence_type_id) &&
                            (!array_key_exists('type', $a->toArray()) || $a->type != 'delete') &&
                            (
                                $variant != 'patch' ||
                                ($variant == 'patch' && $model->type != 'delete') ||
                                ($model->type == 'delete' &&
                                    (!array_key_exists('absence_id', $a->toArray()) && $a->id != $model->absence_id) ||
                                    (array_key_exists('absence_id', $a->toArray()) && $a->absence_id != $model->absence_id)
                                )
                            )

                    );

                $hasAbsenceForDay = $previousAbsencesForDay->isNotEmpty();
                $hasAbbauGleitzeitkontoForDay = $previousAbsencesForDay->contains(
                    fn($a) => $AbbauGleitzeitkontoIds->contains($a->absence_type_id)
                ) || $type == 'absence' && $AbbauGleitzeitkontoIds->contains($model->absence_type_id);
                //erste abwesenheit des Tages, die nicht Abbau Gleitzeitkonto ist
                $hasNewAbsence = $type == 'absence' && ($variant == 'log' || $model->type != 'delete') && $previousAbsencesForDay->isEmpty() && $AbbauGleitzeitkontoIds->doesntContain($model->absence_type_id);

                $oldEntriesForAffectedDay = match ($type) {
                    'work'
                    => $model->user->getEntriesForDate($day)
                        ->merge([
                            array_key_exists('type', $previousModel->attributes) && $previousModel->type == 'delete'
                                ? null
                                : ($day->isSameDay($previousModel->start) ? $previousModel : null)
                        ])
                        ->filter(
                            fn($e) =>
                            $e !== null &&
                                !$e->is($model) &&
                                $e->shift_id != null &&
                                Carbon::parse($e->accepted_at)->startOfSecond() <=
                                Carbon::parse($model->accepted_at)->startOfSecond()
                        )->unique(fn($e) => get_class($e) . $e->id),
                    'absence'
                    => $hasNewAbsence ?
                        $model->user->getEntriesForDate($day)->filter(
                            fn($e) =>
                            Carbon::parse($e->accepted_at)->startOfSecond() <= Carbon::parse($model->accepted_at)->startOfSecond()
                        ) :  collect([])
                };


                $sollForAffectedDay = $model->user->getSollsekundenForDate($day);
                $oldIstForAffectedDay = match ($type) {
                    'work' => self::workDuration($oldEntriesForAffectedDay),
                    'absence' =>  match (true) {
                        $variant == 'log' || $model->type == 'patch' =>  self::workDuration($oldEntriesForAffectedDay),
                        $model->type == 'delete' => $sollForAffectedDay,
                    }
                };

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
                    =>  match (true) {
                        $variant == 'log' || $model->type == 'patch' =>  max($oldIstForAffectedDay, $sollForAffectedDay),
                        $model->type == 'delete' => self::workDuration($entriesOfUnaffectedShifts) + self::workDuration($entriesOfNewShiftsForDay),
                    }
                };

                //if ist > soll overtime can be added immediatly else schedule will subtract the missing amount at 0:00 
                if ($day->isSameDay(now()) || $hasAbsenceForDay || $hasAbbauGleitzeitkontoForDay) {
                    $change =  max($sollForAffectedDay, $newIstForAffectedDay) - max($sollForAffectedDay, $oldIstForAffectedDay);
                } else {
                    $change =  $newIstForAffectedDay - $oldIstForAffectedDay;
                }

                $diffToApply += $change;
                $dayChanges->{$day->format('Y-m-d')} = $change;
            }

            $transactionDescription = match (true) {
                $model instanceof WorkLog
                => 'neuer Buchung',
                $model instanceof WorkLogPatch
                => $model->type != 'delete' ? 'Zeitkorrektur' : 'Zeitlöschung',
                $model instanceof TravelLog
                => 'neuer Dienstreise',
                $model instanceof TravelLogPatch
                => $model->type != 'delete' ? 'Dienstreisekorrektur' : 'Dienstreiselöschung',
                $model instanceof Absence
                => 'neuer Abwesenheit',
                $model instanceof AbsencePatch
                => $model->type != 'delete' ? 'Abwesenheitskorrektur' : 'Abwesenheitslöschung',
            };

            $transactionMessage = match ($type) {
                'work' =>  'Berechnung für ' .
                    Carbon::parse($variant == 'log' || $model->type == 'patch' ? $model->start : $previousModel->start)->format('d.m.Y H:i') .
                    ' - ' .
                    Carbon::parse($variant == 'log' || $model->type == 'patch' ? $model->end : $previousModel->end)->format('d.m.Y H:i') .
                    ' aufgrund von ' . $transactionDescription,
                'absence' => 'Berechnung für ' .
                    ($model->start == $model->end ?
                        Carbon::parse($model->start)->format('d.m.Y') :
                        Carbon::parse($model->start)->format('d.m.Y') . ' - ' . Carbon::parse($model->end)->format('d.m.Y')
                    ) .
                    ' aufgrund von ' . $transactionDescription,
            };

            $createdTransaction = $model->user->defaultTimeAccount->addBalance(
                $diffToApply,
                $transactionMessage
            );

            if ($createdTransaction) {
                $createdTransaction->changes()->createMany([
                    [
                        'date' => $variant == 'log' || $model->type == 'patch' ? ($model->end ?? $model->start) : $previousModel->end,
                        'amount' => $breakDurationChange,
                    ],
                    ...array_map(
                        fn($date, $amount) => [
                            'date' => $date,
                            'amount' => $amount
                        ],
                        array_keys((array)$dayChanges),
                        array_values((array)$dayChanges)
                    )
                ]);
            }



            if ($type == 'work') {
                $baseLog?->updateQuietly([
                    'shift_id' => $model->shift_id,
                    'status' => Status::Accepted,
                    'accepted_at' => $baseLog->accepted_at ?? $baseLog->end
                ]);
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
            ->filter(fn($e) => !array_key_exists('type', $e->attributes) || $e->type !== 'delete')
            ->sortBy('start');

        if ($allEntries->isEmpty()) return $newShifts;

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



    public static function recomputeShifts(Carbon $start, Carbon $end, User $user)
    {



        $state = [
            'user' => $user->first_name . ' ' . $user->last_name,
            'balance_before' => $user->defaultTimeAccount->balance / 3600
        ];

        foreach ($user->timeAccounts as $timeAccount) {
            $transactions = $timeAccount->allTimeAccountTransactions()
                ->get()
                ->filter(
                    fn($transaction) =>
                    $transaction->changes()->where('date', '<=', $end)->where('date', '>=', $start)->exists()
                );

            $firstTransaction = $transactions
                ->sortBy(
                    fn($transaction) =>
                    $transaction->changes()->where('date', '<=', $end)->where('date', '>=', $start)->min('date')
                )
                ->first()->load(['from', 'to']);

            if ($firstTransaction->from_id == $timeAccount->id) {
                $timeAccount->updateQuietly(['balance' => $firstTransaction->from_previous_balance]);
            } else {
                $timeAccount->updateQuietly(['balance' => $firstTransaction->to_previous_balance]);
            }

            foreach ($transactions as $transaction) {
                $transaction->changes()->delete();
                $transaction->delete();
            }
        }

        $checkDate = fn($query, Carbon $start, Carbon $end) => $query->where('start', '<=', $end)->where('end', '>=', $start);

        $checkDate($user->load('workLogs')->workLogs(), $start, $end)->get()->each->updateQuietly(['shift_id' => null]);
        $checkDate($user->load('workLogPatches')->workLogPatches(), $start, $end)->get()->each->updateQuietly(['shift_id' => null]);
        $checkDate($user->load('shifts')->shifts(), $start, $end)->delete();


        $entries = WorkLogPatch::getCurrentEntries($user, false, $start, $end)->load('user');
        for ($day = $start->copy(); $day->lte($end); $day->addDay()) {
            if ($day->lt($user->overtime_calculations_start)) continue;

            if (!$day->isSameDay(now())) $user->removeMissingWorkTimeForDate($day);

            $entriesForDay = $entries->filter(fn($e) => $day->copy()->startOfDay() <= $e->start && $e->start <= $day->endOfDay())->sortBy(fn($e) => $e->accepted_at);

            $entriesForDay->each->save();
        }
        $state['balance_after'] = $user->defaultTimeAccount->fresh()->balance / 3600;
        dump($state);
    }
}
