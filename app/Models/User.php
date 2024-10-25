<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

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
        ['name' => "work_log_patching", 'label' => "Zeitkorrekturen verwalten"],
        ['name' => "user_administration", 'label' => "Nutzer verwalten"]
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getNotificationCountAttribute()
    {
        $count = 0;

        if ($this->work_log_patching) {
            $count += WorkLog::inOrganization()->whereHas('workLogPatches', fn(Builder $q) => $q->where('status', 'created'))->count();
        }
        return $count;
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
        return $this->hasOne(Organization::class, 'organization_id');
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

    public function userWorkingWeeks()
    {
        return $this->hasMany(UserWorkingWeek::class);
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
