<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Carbon\Carbon;
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

    protected $hidden = [
        'password',
        'remember_token',
    ];



    public static $PERMISSIONS = [
        'all' => [
            ['name' => 'user_permission', 'label' => 'Mitarbeitende verwalten'],
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

    public function hasPermission(User | null $user, string $permissionName, string $permissionLevel)
    {
        if ($permissionLevel != 'read' && $permissionLevel != 'write') abort(404);

        if (
            array_key_exists($permissionName, $this->organizationUser->toArray()) && ($this->organizationUser->{$permissionName} == 'write' || $this->organizationUser->{$permissionName} == $permissionLevel) &&
            (!$user || $user->organizationUser->organization_id == $this->organizationUser->organization_id)
        ) return true;

        if (
            array_key_exists($permissionName, $this->groupUser->toArray()) && ($this->groupUser->{$permissionName} == 'write' || $this->groupUser->{$permissionName} == $permissionLevel) &&
            (!$user || $user->group_id == $this->groupUser->group_id)
        ) return true;

        if (
            array_key_exists($permissionName, $this->operatingSiteUser->toArray()) && ($this->operatingSiteUser?->{$permissionName} == 'write' || $this->operatingSiteUser->{$permissionName} == $permissionLevel) &&
            (!$user || $user->operating_site_id == $this->operatingSiteUser->operating_site_id)
        ) return true;

        return false;
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function workLogs()
    {
        return $this->hasMany(WorkLog::class);
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
    public function userLeaveDays()
    {
        return $this->hasMany(UserLeaveDay::class);
    }

    public function timeAccounts()
    {
        return $this->hasMany(TimeAccount::class);
    }

    public function defaultTimeAccount()
    {
        return $this->timeAccounts()->whereHas('timeAccountSetting', fn($q) => $q->whereNull('type'))->first();
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

    public function getOvertimeAttribute()
    {
        return $this->timeAccounts()->whereHas('timeAccountSetting', fn($q) => $q->where('type', 'default'))->sum('balance');
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

        foreach ($currentWeekWorkLogs as $worklog) {
            $handledWorklogs[] = $worklog->id;

            if (!$worklog['patch']) {
                $currentWeekHours += Carbon::parse($worklog->start)->diffInMinutes(Carbon::parse($worklog->end)) / 60;
            } else {
                foreach ($currentWeekPatches as $p) {
                    if ($p->work_log_id == $worklog['patch']->work_log_id) {
                        $currentWeekHours += Carbon::parse($p->start)->diffInMinutes(Carbon::parse($p->end)) / 60;
                    }
                }
            }
        }

        foreach ($currentWeekPatches as $patch) {
            if (!in_array($patch->work_log_id, $handledWorklogs)) {
                $currentWeekHours += Carbon::parse($patch->start)->diffInMinutes(Carbon::parse($patch->end)) / 60;
            }
        }

        return $currentWeekHours;
    }
}
