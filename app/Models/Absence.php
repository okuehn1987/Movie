<?php

namespace App\Models;

use App\Models\Traits\HasPatches;
use App\Models\Traits\IsAccountable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Absence extends Model
{
    use HasFactory, SoftDeletes;
    use ScopeInOrganization, HasPatches, IsAccountable;

    protected $guarded = [];

    private static function getPatchModel()
    {
        return AbsencePatch::class;
    }

    public static function boot()
    {
        parent::boot();
        self::saving(function (Absence $model) {
            //TODO: check if we need to split on save
            // //if the entry spans multiple days we need to split it into different entries
            // if ($model->end && !Carbon::parse($model->start)->isSameDay($model->end)) {
            //     // run backwards for easier mutation
            //     for ($day = Carbon::parse($model->end)->startOfDay(); !$day->isSameDay($model->start); $day->subDay()) {
            //         $model->replicate()->fill([
            //             'start' => $day->copy()->startOfDay(),
            //             'end' => min(Carbon::parse($model->end)->copy(), $day->copy()->endOfDay()),
            //         ])->save();
            //     }
            //     $model->end = Carbon::parse($model->start)->copy()->endOfDay();
            // }
            Shift::computeAffected($model);
        });
    }


    public function getHidden()
    {
        $user = request()->user();
        if ($user && $user->cannot('viewShow', [AbsenceType::class, $this->user])) return ['absence_type_id', 'absenceType'];
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

    public function getUsedDaysAttribute()
    {
        $usedDays = 0;
        for ($day = Carbon::parse($this->start)->startOfDay(); $day->lte(Carbon::parse($this->end)); $day->addDay()) {
            $currentWorkingWeek = $this->user->userWorkingWeekForDate($day);

            if (
                $currentWorkingWeek?->hasWorkDay($day) &&
                !$this->user->operatingSite->hasHoliday($day)
            ) {
                $usedDays++;
            };
        }

        return $usedDays;
    }
}
