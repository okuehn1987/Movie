<?php

namespace App\Models;

use App\Services\HolidayService;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OperatingSite extends Model
{
    use HasFactory, SoftDeletes, ScopeInOrganization;

    protected $guarded = [];
    protected $casts = ['is_headquarter' => 'boolean'];

    public function operatingTimes()
    {
        return $this->hasMany(OperatingTime::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function operatingSiteUser()
    {
        return $this->hasMany(OperatingSiteUser::class);
    }

    public function hasHoliday(CarbonInterface $date)
    {
        if (!$this->country || !$this->federal_state) {
            return false;
        }
        return HolidayService::isHoliday($this->country, $this->federal_state, $date);
    }
}
