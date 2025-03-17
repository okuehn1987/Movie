<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class User extends Authenticatable
{
    use \Znck\Eloquent\Traits\BelongsToThrough;
    use HasFactory, Notifiable, SoftDeletes, ScopeInOrganization;

    protected $guarded = ['password', 'role', 'email_verified_at'];

    protected $casts = [
        'home_office' => 'boolean',
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_supervisor' => 'boolean',
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
        $this->load(['isSubstitutionFor']);
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

        $this->load(['organizationUser']);
        if ($user) $user->load(['organizationUser']);

        if (
            array_key_exists($permissionName, $this->organizationUser->toArray()) &&
            ($this->organizationUser->{$permissionName} == 'write' || $this->organizationUser->{$permissionName} == $permissionLevel) &&
            (!$user || $user->organizationUser->organization_id == $this->organizationUser->organization_id)
        ) return true;

        $this->load(['groupUser']);
        if ($user) $user->load(['groupUser']);

        if (
            $this->groupUser &&
            array_key_exists($permissionName, $this->groupUser->toArray()) &&
            ($this->groupUser->{$permissionName} == 'write' || $this->groupUser->{$permissionName} == $permissionLevel) &&
            (!$user || $user->group_id == $this->groupUser->group_id)
        ) return true;

        $this->load(['operatingSiteUser']);
        if ($user) $user->load(['operatingSiteUser']);

        if (
            array_key_exists($permissionName, $this->operatingSiteUser->toArray()) &&
            ($this->operatingSiteUser?->{$permissionName} == 'write' || $this->operatingSiteUser->{$permissionName} == $permissionLevel) &&
            (!$user || $user->operating_site_id == $this->operatingSiteUser->operating_site_id)
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
        return $this->supervisees()->with('allSupervisees:id,supervisor_id,first_name,last_name,email');
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
    public function absences()
    {
        return $this->hasMany(Absence::class);
    }
    public function absencePatches()
    {
        return $this->hasMany(AbsencePatch::class);
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
    public function userWorkingHours()
    {
        return $this->hasMany(UserWorkingHour::class);
    }

    public function userWorkingHoursForDate(CarbonInterface $date): UserWorkingHour | null
    {
        return $this->userWorkingHours()->where('active_since', '<=', $date->format('Y-m-d'))
            ->latest('active_since')
            ->first();
    }

    public function userLeaveDays()
    {
        return $this->hasMany(UserLeaveDay::class);
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

    public function userWorkingWeeks()
    {
        return $this->hasMany(UserWorkingWeek::class);
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

    public function userWorkingWeekForDate(CarbonInterface $date): UserWorkingWeek | null
    {
        return $this->userWorkingWeeks()->where('active_since', '<=', $date->format('Y-m-d'))
            ->latest('active_since')
            ->first();
    }

    public function latestWorkLog()
    {
        return $this->hasOne(WorkLog::class)->latestOfMany('start');
    }

    public function getOvertimeAttribute()
    {
        return $this->defaultTimeAccount->balance;
    }

    public function getAgeAttribute()
    {
        return Carbon::parse($this->date_of_birth)->age;
    }

    public static function getCurrentWeekWorkingHours(User $user)
    {
        //all logs that could be applicable
        $currentWeekWorkLogs = $user->workLogs()
            ->whereNotNull('end')
            ->whereBetween('start', [Carbon::now()->startOfWeek(), Carbon::now()])
            ->get();

        //all patches that are applicable
        $currentWeekPatches = WorkLogPatch::where('status', 'accepted')
            ->where('user_id', $user->id)
            ->whereBetween('start', [Carbon::now()->startOfWeek(), Carbon::now()])
            ->orderBy('created_at', 'Desc')
            ->get()
            ->unique('work_log_id');

        //alle patches der logs der woche
        foreach ($currentWeekWorkLogs as $worklog) {
            $worklog['patch'] = WorkLogPatch::where('status', 'accepted')
                ->where('user_id', $user->id)
                ->where('work_log_id', $worklog->id)
                ->orderBy('created_at', 'Desc')
                ->first();
        }

        $handledWorklogs = [];
        $currentWeekHours = 0;
        $currentWeekHomeOfficeHours = 0;

        foreach ($currentWeekWorkLogs as $worklog) {
            $handledWorklogs[] = $worklog->id;

            if (!$worklog['patch']) {
                $t = Carbon::parse($worklog->start)->diffInMinutes(Carbon::parse($worklog->end)) / 60;
                $currentWeekHours += $t;
                if ($worklog->is_home_office) $currentWeekHomeOfficeHours += $t;
            } else {
                foreach ($currentWeekPatches as $p) {
                    if ($p->work_log_id == $worklog['patch']->work_log_id) {
                        $t = Carbon::parse($p->start)->diffInMinutes(Carbon::parse($p->end)) / 60;
                        $currentWeekHours += $t;
                        if ($worklog['patch']->is_home_office) $currentWeekHomeOfficeHours += $t;
                    }
                }
            }
        }

        foreach ($currentWeekPatches as $patch) {
            if (!in_array($patch->work_log_id, $handledWorklogs)) {
                $currentWeekHours += Carbon::parse($patch->start)->diffInMinutes(Carbon::parse($patch->end)) / 60;
            }
        }

        return [
            'totalHours' => $currentWeekHours,
            'homeOfficeHours' => $currentWeekHomeOfficeHours,
        ];
    }

    public function usedLeaveDaysForYear(CarbonInterface $year): int
    {
        $relevantAbsences = $this->absences()
            ->whereHas('absenceType', fn($q) => $q->where('type', 'Urlaub'))
            ->whereDate('start', '<=', $year->copy()->endOfYear())
            ->whereDate('end', '>=', $year->copy()->startOfYear())
            ->get();

        $usedDays = 0;
        foreach ($relevantAbsences as $absence) {
            for ($day = Carbon::parse($absence->start)->startOfDay(); $day->lte(Carbon::parse($absence->end)); $day->addDay()) {
                if ($day->year != $year->year) continue;
                $currentWorkingWeek = $this->userWorkingWeekForDate($day);
                $workingDaysInWeek = $currentWorkingWeek?->numberOfWorkingDays;

                if (
                    $workingDaysInWeek > 0 &&
                    $currentWorkingWeek->hasWorkDay($day) &&
                    !$this->operatingSite->hasHoliday($day)
                ) {
                    $usedDays++;
                };
            }
        }

        return $usedDays;
    }

    public function leaveDaysForYear(CarbonInterface $year): int
    {
        $leaveDays = 0;
        for ($m = 1; $m <= 12; $m++) {
            $month = $year->setMonth($m)->startOfMonth();
            $activeEntry = $this->userLeaveDays()
                ->where('type', 'annual')
                ->whereDate('active_since', '<=', $month)
                ->latest('active_since')
                ->first();
            if ($activeEntry) { //FIXME: should not be possible to be false
                $leaveDays += $activeEntry->leave_days / 12;
            }
        }

        $leaveDays += $this->userLeaveDays()
            ->where('type', 'remaining')
            ->whereYear('active_since', $year)->first()?->leave_days ?? 0;

        return ceil($leaveDays);
    }

    public function hasAbsenceForDate(CarbonInterface $date)
    {
        $absences = $this->absences()
            ->with(['patches', 'currentAcceptedPatch'])
            ->where('status', 'accepted')
            ->get();

        $absences = $absences->map(fn($a) => $a->currentAcceptedPatch ?? $a);

        return $absences->contains(fn($a) => $date->between(Carbon::parse($a->start)->startOfDay(), Carbon::parse($a->end)->endOfDay()));
    }

    public function getSollsekundenForDate(CarbonInterface $date)
    {
        $currentWorkingHours = $this->userWorkingHoursForDate($date);
        $currentWorkingWeek = $this->userWorkingWeekForDate($date);

        if (!$currentWorkingHours || !$currentWorkingWeek) return 0;

        $shouldWork =
            $currentWorkingWeek->hasWorkDay($date) &&
            !$this->operatingSite->hasHoliday($date);

        if (!$shouldWork) return 0;

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
        if ($this->hasAbsenceForDate($date)) return;
        $this->defaultTimeAccount->addBalance(
            max(0, $this->getSollsekundenForDate($date) - $this->getWorkDurationForDate($date)) * -1,
            'Fehlende Stunden am ' . $date->format('d.m.Y')
        );
    }
}
