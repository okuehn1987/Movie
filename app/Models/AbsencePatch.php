<?php

namespace App\Models;

use App\Models\Traits\HasLog;
use App\Models\Traits\IsAccountable;
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
