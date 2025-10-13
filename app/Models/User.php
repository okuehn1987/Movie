<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use App\Notifications\PasswordResetNotification;

class User extends Authenticatable
{
    use \Znck\Eloquent\Traits\BelongsToThrough;
    use HasFactory, Notifiable, SoftDeletes, ScopeInOrganization;

    /**
     * Send a password reset notification to the user.
     * Overrides native laravel Password Reset notification.
     *
     * @param  string  $token
     */
    public function sendPasswordResetNotification($token): void
    {
        $url = route('password.reset', ['token' => $token]);
        $this->notify(new PasswordResetNotification($url, $this));
    }

    protected $guarded = ['password', 'role', 'email_verified_at'];

    protected $casts = [
        'home_office' => 'boolean',
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_supervisor' => 'boolean',
        'notification_channels' => 'array',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public static $PERMISSIONS = [
        'all' => [
            ['name' => 'user_permission', 'label' => 'Mitarbeitende verwalten'],
            ['name' => 'workLog_permission', 'label' => 'Arbeitszeiten verwalten'],
            ['name' => 'workLogPatch_permission', 'label' => 'Zeitkorrekturen verwalten'],
            ['name' => 'absence_permission', 'label' => 'Abwesenheiten verwalten'],
            ['name' => 'timeAccount_permission', 'label' => 'Zeitkonten verwalten'],
            ['name' => 'timeAccountSetting_permission', 'label' => 'Zeitkontovarianten verwalten'],
            ['name' => 'timeAccountTransaction_permission', 'label' => 'Zeitkontotransaktionen verwalten'],
        ],
        'organization' => [
            ['name' => 'absenceType_permission', 'label' => 'Abwesenheitsgründe verwalten'],
            ['name' => 'specialWorkingHoursFactor_permission', 'label' => 'Sonderarbeitszeitfaktoren verwalten'],
            ['name' => 'organization_permission', 'label' => 'Organisation verwalten'],
        ],
        'operatingSite' => [['name' => 'operatingSite_permission', 'label' => 'Betriebsstätte verwalten']],
        'group' =>  [['name' => 'group_permission', 'label' => 'Abteilungen verwalten']],
    ];

    /** 
     * @param User | null $user
     * @param string $permissionName 
     * @param 'read'|'write' $permissionLevel
     *   */
    public function hasPermissionOrDelegation(User | null $user, string $permissionName, string $permissionLevel)
    {
        $this->loadMissing(['isSubstitutionFor']);
        return $this->hasPermission($user, $permissionName,  $permissionLevel) ||
            $this->isSubstitutionFor->some(fn($substitution) => $substitution->hasPermission($user, $permissionName,  $permissionLevel));
    }

    /** 
     * @param User | null $user
     * @param string $permissionName 
     * @param 'read'|'write' $permissionLevel
     *   */
    private function hasPermission(User | null $user, string $permissionName, string $permissionLevel)
    {
        if ($permissionLevel != 'read' && $permissionLevel != 'write') abort(404);

        //TODO: this removed a lot of queries but the nest relations are still queried 1 by 1 for each checked policy e.g. absence.index
        $user = $this->usersInOrganization->find($user?->id);

        $this->loadMissing(['organizationUser']);
        if ($user) {
            $organizationUser = $this->organizationUsers->where('user_id', $user->id)->first();
            $operatingSiteUser = $this->operatingSiteUsersInOrganization->where('user_id', $user->id)->first();
            $groupUser = $this->groupUsersInOrganization->where('user_id', $user->id)->first();
        }

        if (
            array_key_exists($permissionName, $this->organizationUser->toArray()) &&
            ($this->organizationUser->{$permissionName} == 'write' || $this->organizationUser->{$permissionName} == $permissionLevel) &&
            (!$user || $organizationUser?->organization_id == $this->organizationUser->organization_id)
        ) return true;

        $this->loadMissing(['operatingSiteUser']);

        if (
            array_key_exists($permissionName, $this->operatingSiteUser->toArray()) &&
            ($this->operatingSiteUser?->{$permissionName} == 'write' || $this->operatingSiteUser->{$permissionName} == $permissionLevel) &&
            (!$user || $operatingSiteUser?->operating_site_id == $this->operatingSiteUser->operating_site_id)
        ) return true;

        $this->loadMissing(['groupUser']);

        if (
            $this->groupUser &&
            array_key_exists($permissionName, $this->groupUser->toArray()) &&
            ($this->groupUser->{$permissionName} == 'write' || $this->groupUser->{$permissionName} == $permissionLevel) &&
            (!$user || $groupUser?->group_id == $this->groupUser->group_id)
        ) return true;

        return false;
    }

    public function getNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function shifts()
    {
        return $this->hasMany(Shift::class);
    }

    public function currentShift()
    {
        return $this->shifts()->one()->ofMany(
            ['start' => 'Max'],
            fn(Builder $q) => $q->where('end', '>=', now()->subHours(Shift::$MINIMUM_SHIFT_SEPARATION_TIME_IN_HOURS))
        );
    }

    public function workLogs()
    {
        return $this->hasMany(WorkLog::class);
    }

    public function latestWorkLog()
    {
        return $this->workLogs()->one()->ofMany(['start' => 'Max']);
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

    public function absences()
    {
        return $this->hasMany(Absence::class);
    }

    public function absencePatches()
    {
        return $this->hasMany(AbsencePatch::class);
    }

    public function supervisor()
    {
        return $this->belongsTo(User::class);
    }

    public function supervisees()
    {
        return $this->hasMany(User::class, 'supervisor_id');
    }

    public function allSupervisees()
    {
        return $this->supervisees()->with('allSupervisees:id,supervisor_id,first_name,last_name,email,job_role');
    }

    public function allSuperviseesFlat(): Collection
    {
        return $this->allSupervisees()->get()->flatMap(fn($u) => $u->allSuperviseesFlat()->push($u))->push($this);
    }

    public function isSubstitutedBy()
    {
        return $this->belongsToMany(User::class, 'substitutes', 'user_id', 'substitute_id');
    }

    public function isSubstitutionFor()
    {
        return $this->belongsToMany(User::class, 'substitutes', 'substitute_id', 'user_id');
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function operatingSite()
    {
        return $this->belongsTo(OperatingSite::class);
    }

    public function organization()
    {
        return $this->belongsToThrough(Organization::class, OperatingSite::class);
    }

    public function owns()
    {
        return $this->hasOne(Organization::class, 'owner_id', 'organization_id');
    }

    public function userAbsenceFilters()
    {
        return $this->hasMany(UserAbsenceFilter::class);
    }

    public function userWorkingHours()
    {
        return $this->hasMany(UserWorkingHour::class);
    }

    public function currentWorkingHours()
    {
        return $this->userWorkingHours()->one()->ofMany(['active_since' => 'Max'], fn($q) => $q->whereDate('active_since', '<=', now()));
    }

    public function workingHours(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->userWorkingHours()->get()
        )->shouldCache();
    }

    public function userWorkingHoursForDate(CarbonInterface $date, $userWorkingHours = null): UserWorkingHour | null
    {
        return ($userWorkingHours ?? $this->workingHours)->where('active_since', '<=', $date->format('Y-m-d'))
            ->sortByDesc('active_since')
            ->first();
    }

    public function userLeaveDays()
    {
        return $this->hasMany(UserLeaveDay::class);
    }

    public function currentLeaveDays()
    {
        return $this->userLeaveDays()->one()->ofMany(
            ['active_since' => 'Max'],
            fn($q) => $q->whereDate('active_since', '<=', now())->where('type', 'annual')
        );
    }

    public function userWorkingWeeks()
    {
        return $this->hasMany(UserWorkingWeek::class);
    }

    public function currentWorkingWeek()
    {
        return $this->userWorkingWeeks()->one()->ofMany(
            ['active_since' => 'Max'],
            fn($q) => $q->whereDate('active_since', '<=', now())
        );
    }

    public function timeAccounts()
    {
        return $this->hasMany(TimeAccount::class);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\HasOne<\App\Models\TimeAccount, \App\Models\User>*/
    public function defaultTimeAccount()
    {
        return $this->hasOne(TimeAccount::class)->whereHas('timeAccountSetting', fn($q) => $q->whereNull('type'));
    }

    public function organizationUser()
    {
        return $this->hasOne(OrganizationUser::class);
    }

    public function groupUser()
    {
        return $this->hasOne(GroupUser::class);
    }

    public function operatingSiteUser()
    {
        return $this->hasOne(OperatingSiteUser::class);
    }

    public function workingWeeks(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->userWorkingWeeks()->get()
        )->shouldCache();
    }

    public function userWorkingWeekForDate(CarbonInterface $date, $userWorkingWeeks = null): UserWorkingWeek | null
    {
        return ($userWorkingWeeks ?? $this->workingWeeks)->where('active_since', '<=', $date->format('Y-m-d'))
            ->sortByDesc('active_since')
            ->first();
    }

    public function overtime(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->defaultTimeAccount->balance,
        );
    }

    public function age(): Attribute
    {
        return Attribute::make(
            get: fn() => Carbon::parse($this->date_of_birth)->age,
        )->shouldCache();
    }

    public function currentWeekShifts(): Attribute
    {
        return Attribute::make(
            get: function () {
                return $this->shifts()
                    ->where('start', '>=', now()->startOfWeek())
                    ->get();
            }
        )->shouldCache();
    }

    public function currentWeekWorkingHours(): Attribute
    {
        return Attribute::make(
            get: function () {
                $shifts = $this->currentWeekShifts;

                $totalHours = $shifts->map(function (Shift $s) {
                    $stats = $s->accountableDuration();
                    return $stats['workDuration'] - $stats['missingBreakDuration'];
                })->sum();

                $partialShifts = $this->shifts()
                    ->where('end', '>=', now()->startOfWeek())
                    ->where('start', '<', now()->startOfWeek())
                    ->get();

                $partialShiftEntries = collect($partialShifts)->flatMap(
                    fn($s) => $s->entries->filter(
                        fn($e) => Carbon::parse($e->start)->gte(now()->startOfWeek())
                    )
                );

                $totalHours += Shift::workDuration($partialShiftEntries) - $partialShiftEntries->sum('missingBreakDuration');

                $homeOfficeShifts = $shifts->filter(
                    fn($s) => $s->entries->filter(
                        fn($e) => !property_exists($e, 'is_home_office') || !$e->is_home_office
                    )->count() == 0
                );

                $homeOfficeHours = $homeOfficeShifts->map(function (Shift $s) {
                    $stats = $s->accountableDuration($s->entries->filter(
                        fn($e) => property_exists($e, 'is_home_office') && $e->is_home_office
                    ));
                    return $stats['workDuration'] - $stats['missingBreakDuration'];
                })->sum();

                $otherHomeOfficeEntries = $shifts->filter(fn($s) => !$homeOfficeShifts->contains($s))->flatMap->entries->filter(
                    fn($e) => property_exists($e, 'is_home_office') && $e->is_home_office
                );

                $homeOfficeHours += Shift::workDuration($otherHomeOfficeEntries);

                return [
                    'totalHours' => $totalHours,
                    'homeOfficeHours' => $homeOfficeHours,
                ];
            }
        )->shouldCache();
    }

    public function usedLeaveDaysForYear(CarbonInterface $year, $userWorkingWeeks = null, $absences = null): int
    {
        $startOfYear = $year->copy()->startOfYear();
        $endOfYear = $year->copy()->endOfYear();

        $relevantAbsences = ($absences ?? collect(
            $this->absences()->doesntHave('currentAcceptedPatch')
                ->whereHas('absenceType', fn($q) => $q->where('type', 'Urlaub'))
                ->whereDate('start', '<=', $endOfYear)
                ->whereDate('end', '>=', $startOfYear)
                ->get()
        )->merge(
            $this->absencePatches()
                ->with('log.currentAcceptedPatch')
                ->whereHas('absenceType', fn($q) => $q->where('type', 'Urlaub'))
                ->where('status', 'accepted')
                ->whereNot('type', 'delete')
                ->whereDate('start', '<=', $endOfYear)
                ->whereDate('end', '>=', $startOfYear)
                ->get()
                ->filter(fn($p) => $p->log->currentAcceptedPatch->is($p))
        ));

        $absenceData = $relevantAbsences->flatMap(fn($a) => collect(
            range(0, Carbon::parse(max($a->start, $startOfYear))->startOfDay()->diffInDays(Carbon::parse(min($a->end, $endOfYear))->startOfDay()))
        )->map(
            fn($i) => Carbon::parse(max($a->start, $startOfYear))->addDays($i)->startOfDay()
        ))->unique()->sort();

        $usedDays = 0;
        foreach ($absenceData as $day) {
            $currentWorkingWeek = $this->userWorkingWeekForDate($day, $userWorkingWeeks);
            if (
                $currentWorkingWeek?->hasWorkDay($day) &&
                !$this->operatingSite->hasHoliday($day)
            ) {
                $usedDays++;
            };
        }
        return $usedDays;
    }

    public function usedLeaveDaysForMonth(CarbonInterface $month, $userWorkingWeeks = null, $absences = null): int
    {
        $startOfMonth = $month->copy()->startOfMonth();
        $endOfMonth = $month->copy()->endOfMonth();

        $relevantAbsences = ($absences ?? collect(
            $this->absences()->doesntHave('currentAcceptedPatch')
                ->whereHas('absenceType', fn($q) => $q->where('type', 'Urlaub'))
                ->whereDate('start', '<=', $endOfMonth)
                ->whereDate('end', '>=', $startOfMonth)
                ->get()
        )->merge(
            $this->absencePatches()
                ->with('log.currentAcceptedPatch')
                ->whereHas('absenceType', fn($q) => $q->where('type', 'Urlaub'))
                ->where('status', 'accepted')
                ->whereNot('type', 'delete')
                ->whereDate('start', '<=', $endOfMonth)
                ->whereDate('end', '>=', $startOfMonth)
                ->get()
                ->filter(fn($p) => $p->log->currentAcceptedPatch->is($p))
        ));

        $absenceData = $relevantAbsences->filter(fn($a) => Carbon::parse($a->end)->gte($startOfMonth) && Carbon::parse($a->start)->lte($endOfMonth))
            ->flatMap(fn($a) => collect(
                range(0, Carbon::parse(max($a->start, $startOfMonth))->diffInDays(min($a->end, $endOfMonth)))
            )->map(
                fn($i) => Carbon::parse(max($a->start, $startOfMonth))->addDays($i)->startOfDay()
            ))->unique()->sort();

        $usedDays = 0;
        foreach ($absenceData as $day) {
            $currentWorkingWeek = $this->userWorkingWeekForDate($day, $userWorkingWeeks);
            if (
                $currentWorkingWeek?->hasWorkDay($day) &&
                !$this->operatingSite->hasHoliday($day)
            ) {
                $usedDays++;
            };
        }
        return $usedDays;
    }

    public function shouldWork(CarbonInterface $date)
    {
        return $this->userWorkingWeekForDate($date)?->hasWorkDay($date) && !$this->operatingSite->hasHoliday($date);
    }

    public function leaveDays(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->userLeaveDays()->get()
        )->shouldCache();
    }

    public function leaveDaysForYear(CarbonInterface $year, $userLeaveDays = null): int
    {
        $leaveDays = $userLeaveDays ?? $this->leaveDays;

        $annual = $leaveDays->where('type', 'annual')
            ->filter(fn($ld) => $ld->active_since <= $year->copy()->endOfYear()->format('Y-m-d'))
            ->sortBy('active_since')
            ->values();

        $jan1 = $year->copy()->startOfYear();
        $dec31 = $year->copy()->endOfYear();

        $changePoints = $annual
            ->map(fn($e) => Carbon::parse($e->active_since)->startOfMonth())
            ->filter(fn($d) => $d->betweenIncluded($jan1, $dec31))
            ->prepend($jan1)
            ->unique(fn($d) => $d->format('Y-m'))
            ->values();

        $changePoints->push($dec31->copy()->addMonth()->startOfMonth());


        $leaveDayCount = 0;

        for ($i = 0; $i < $changePoints->count() - 1; $i++) {
            $start = $changePoints[$i];
            $end   = $changePoints[$i + 1]->copy();

            $entry = $annual
                ->filter(fn($e) => Carbon::parse($e->active_since)->startOfMonth()->lte($start))
                ->last();

            if ($entry) {
                $months = $start->diffInMonths($end);
                $leaveDayCount += ($entry->leave_days / 12.0) * $months;
            }
        }

        $remaining = $leaveDays
            ->where('type', 'remaining')
            ->firstWhere(fn($ld) => Carbon::parse($ld->active_since)->year === $year->year);

        if ($remaining) {
            $leaveDayCount += $remaining->leave_days;
        }

        return (int)ceil($leaveDayCount);
    }

    public function getAbsencesForDate(CarbonInterface $date)
    {
        $absences = $this->absences()
            ->with(['patches', 'currentAcceptedPatch'])
            ->where('status', 'accepted')
            ->get();

        $absences = $absences->map(fn($a) => $a->currentAcceptedPatch ?? $a);

        return $absences->filter(fn($a) => $date->betweenIncluded(Carbon::parse($a->start)->startOfDay(), Carbon::parse($a->end)->endOfDay()));
    }

    public function hasAbsenceForDate(CarbonInterface $date)
    {
        return $this->getAbsencesForDate($date)->count() > 0;
    }

    public function getSollsekundenForDate(CarbonInterface $date)
    {
        if ($this->resignation_date && $date->gt(Carbon::parse($this->resignation_date))) {
            return 0;
        }

        if (!$this->shouldWork($date)) return 0;

        $currentWorkingHours = $this->userWorkingHoursForDate($date);
        $currentWorkingWeek = $this->userWorkingWeekForDate($date);

        return $currentWorkingHours['weekly_working_hours'] / $currentWorkingWeek->numberOfWorkingDays * 3600;
    }

    public function getEntriesForDate(CarbonInterface $date)
    {
        $shifts = $this->shifts()
            ->where('end', '>=', $date->copy()->startOfDay())
            ->where('start', '<=', $date->copy()->endOfDay())
            ->get();

        return collect($shifts)->flatMap(
            fn($s) =>
            $s->entries->filter(
                fn($e) =>
                $e->status == 'accepted' &&
                    $e->accepted_at != null &&
                    Carbon::parse($e->start)->between($date->copy()->startOfDay(), $date->copy()->endOfDay()) &&
                    Carbon::parse($e->end)->between($date->copy()->startOfDay(), $date->copy()->endOfDay())
            )
        );
    }

    public function getWorkDurationForDate(CarbonInterface $date)
    {
        return Shift::workDuration($this->getEntriesForDate($date));
    }

    public function removeMissingWorkTimeForDate(CarbonInterface $date)
    {
        if ($this->getAbsencesForDate($date)
            ->contains(fn($a) => $a->absence_type_id !== AbsenceType::inOrganization()->where('type', 'Abbau Gleitzeitkonto')->first()->id)
        ) return;

        $amount = min(0, $this->getWorkDurationForDate($date) - $this->getSollsekundenForDate($date));

        TimeAccountTransactionChange::createFor($this->defaultTimeAccount->addBalance(
            $amount,
            'Fehlende Stunden am ' . $date->format('d.m.Y')
        ), $date);
    }

    public function usersInOrganization(): Attribute
    {
        return Attribute::make(
            get: function () {
                return Organization::getCurrent()->users()->withTrashed()->get();
            }
        )->shouldCache();
    }
    public function groupUsersInOrganization(): Attribute
    {
        return Attribute::make(
            get: function () {
                return Organization::getCurrent()->groupUsers;
            }
        )->shouldCache();
    }
    public function operatingSiteUsersInOrganization(): Attribute
    {
        return Attribute::make(
            get: function () {
                return Organization::getCurrent()->operatingSiteUsers;
            }
        )->shouldCache();
    }

    public function organizationUsers(): Attribute
    {
        return Attribute::make(
            get: function () {
                return Organization::getCurrent()->organizationUsers;
            }
        )->shouldCache();
    }

    public function allAbsenceTypes(): Attribute
    {
        return Attribute::make(
            get: function () {
                return Organization::getCurrent()->absenceTypes()->get();
            }
        )->shouldCache();
    }

    public function currentAbsencePeriod(): Attribute
    {
        return Attribute::make(
            get: function () {
                $futureAbsences =
                    collect(
                        $this->absences()
                            ->doesntHave('currentAcceptedPatch')
                            ->whereDate('end', '>=', now())
                            ->get()
                    )
                    ->merge(
                        $this->absencePatches()
                            ->with('log.currentAcceptedPatch')
                            ->where('status', 'accepted')
                            ->whereDate('end', '>=', now())
                            ->get()
                            ->filter(fn($p) => $p->log->currentAcceptedPatch->is($p))
                    );

                $lastDay = null;
                $absenceTypes = collect();

                for ($day = now()->startOfDay();; $day->addDay()) {
                    $absencesForDay = $futureAbsences->filter(fn($a) => Carbon::parse($a['start'])->lte($day) && Carbon::parse($a['end'])->gte($day));

                    if ($absencesForDay->count() > 0 || !$this->shouldWork($day)) {
                        $absenceTypes = $absenceTypes->merge($absencesForDay->pluck('absence_type_id'));
                        $lastDay = $day->copy();
                    } else {
                        break;
                    }
                }
                return [
                    'end' => $lastDay?->format('d.m.Y'),
                    'type' => request()->user()->can(
                        'viewShow',
                        [AbsenceType::class, $this]
                    ) ?
                        $absenceTypes->unique()->map(fn($t) => request()->user()->allAbsenceTypes->find($t)->abbreviation)->join(' / ') :
                        null
                ];
            }
        )->shouldCache();
    }
}
