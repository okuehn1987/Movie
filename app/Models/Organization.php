<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Organization extends Model
{
    use HasFactory, SoftDeletes, ScopeInOrganization;

    protected $fillable = [
        'name'
    ];

    public function operatingSites()
    {
        return $this->hasMany(OperatingSite::class);
    }
    public function users()
    {
        return $this->hasManyThrough(User::class, OperatingSite::class);
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

    public static function getCurrent()
    {
        if (Auth::check()) return Auth::user()->organization;
        return Organization::find(1);
    }


    public static function getOrganizationNameByDomain()
    {
        if (app()->environment('local')) {
            if (request()->organization) session(['org' => request()->organization]);
            return session('org') ?? 'MBD';
        }
        $domain = str_replace('www.', '', request()->getHost());
        // $domain = str_replace('brittaai', 'britta-ai', $domain);

        return match ($domain) {
            default => 'MBD',
        };
    }
}
