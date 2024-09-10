<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use \Znck\Eloquent\Traits\BelongsToThrough;
    use HasFactory, Notifiable, SoftDeletes, ScopeInOrganization;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
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
    public function substitutes()
    {
        return $this->hasMany(User::class);
    }
    public function absences()
    {
        return $this->hasMany(Abscence::class);
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
}
