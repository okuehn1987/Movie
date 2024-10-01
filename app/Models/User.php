<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

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
        ['name' => "work_log_patching", 'label' => "Zeitkorrekturen verwalten"]
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
}
