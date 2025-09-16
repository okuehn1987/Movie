<?php

namespace App\Models;

use App\Models\Traits\HasLog;
use App\Models\Traits\IsAccountable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AbsencePatch extends Model
{
    use HasFactory, SoftDeletes;
    use ScopeInOrganization, HasLog, IsAccountable;

    protected $guarded = [];

    private static function getLogModel()
    {
        return Absence::class;
    }

    public static function boot()
    {
        parent::boot();
        self::saving(function (AbsencePatch $model) {
            //TODO: check if we need to split on save
            Shift::computeAffected($model);
        });
    }

    public function getHidden()
    {
        $user = request()->user();
        if ($user && $user->cannot('viewShow', [AbsenceType::class, $user->usersInOrganization->find($this->user_id)])) return ['absence_type_id', 'absenceType'];
        return [];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function absenceType()
    {
        return $this->belongsTo(AbsenceType::class);
    }

    public function getDurationAttribute()
    {
        return (int)Carbon::parse($this->start)->diffInDays(Carbon::parse($this->end)) + 1;
    }

    public function usedDays(): Attribute
    {
        return Attribute::make(
            get: fn() => Absence::calculateUsedDays($this)
        );
    }
}
