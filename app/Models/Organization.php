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

    public static $FLAGS = [
        // "night_surcharges" => 'Nachtzuschüsse',
        // "vacation_limitation_period" => "Verjährungsfrist bei Urlaub",
        // "auto_accept_travel_logs" => "Dienstreisen standardmäßig genehmigen",
        "christmas_vacation_day" => "24.12 als Urlaubstag",
        "new_year_vacation_day" => "31.12 als Urlaubstag"
    ];

    public function modules()
    {
        return $this->hasMany(OrganizationModule::class);
    }

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    public function tickets()
    {
        return $this->hasManyThrough(Ticket::class, Customer::class);
    }

    public function operatingSites()
    {
        return $this->hasMany(OperatingSite::class);
    }
    public function operatingSiteUsers()
    {
        return $this->hasManyThrough(OperatingSiteUser::class, OperatingSite::class);
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
    public function groupUsers()
    {
        return $this->hasManyThrough(GroupUser::class, Group::class);
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
        return Organization::whereId(self::getOrganizationIdByDomain())->first() ?? Organization::first();
    }

    public static function getOrganizationIdByDomain(): string
    {
        if (app()->environment('local')) {
            if (request()->organization) session(['org' => request()->organization]);
            return session('org') ?? 1;
        }
        $domain = str_replace('www.', '', request()->getHost());

        return match ($domain) {
            'staging.herta.mbd-team.de' => 1,
            'sbl.tide-hrta.de' => 2,
            'bsp.tide-hrta.de' => 3,
            default => 1,
        };
    }
}
