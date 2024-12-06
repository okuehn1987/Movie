<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Organization extends Model
{
    use HasFactory, SoftDeletes, ScopeInOrganization;

    protected $guarded = [];
    protected $casts = ['night_surcharges' => 'boolean', 'vacation_limitation_period' => 'boolean' ];

    public function operatingSites()
    {
        return $this->hasMany(OperatingSite::class);
    }
    public function users()
    {
        return $this->hasManyThrough(User::class, OperatingSite::class);
    }

    public function organizationUsers()
    {
        return $this->hasMany(OrganizationUser::class);
    }

    public function absenceTypes()
    {
        return $this->hasMany(AbsenceType::class);
    }
    public function groups()
    {
        return $this->hasMany(Group::class);
    }
    public function specialWorkingHoursFactors()
    {
        return $this->hasMany(SpecialWorkingHoursFactor::class);
    }

    public function timeAccountSettings()
    {
        return $this->hasMany(TimeAccountSetting::class);
    }

    public function owner()
    {
        return $this->hasOne(User::class, 'owner_id');
    }
    public function customAddresses()
    {
        return $this->hasMany(CustomAddress::class);
    }

    public static function getCurrent(): Organization
    {
        if (Auth::check()) return Auth::user()->organization;
        return Organization::where('name', self::getOrganizationNameByDomain())->first() ?? Organization::first();
    }


    public static function getOrganizationNameByDomain(): string
    {
        if (app()->environment('local')) {
            if (request()->organization) session(['org' => request()->organization]);
            return session('org') ?? 'MBD';
        }
        $domain = str_replace('www.', '', request()->getHost());

        return match ($domain) {
            'staging.herta.mbd-team.de' => 'MBD',
            default => 'MBD',
        };
    }
}
