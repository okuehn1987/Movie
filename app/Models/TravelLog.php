<?php

namespace App\Models;

use App\Models\Traits\HasDuration;
use App\Models\Traits\HasPatches;
use App\Models\Traits\IsAccountable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TravelLog extends Model
{
    use HasFactory, SoftDeletes;
    use ScopeInOrganization, HasPatches, IsAccountable, HasDuration;

    protected $guarded = [];

    private static function getPatchModel()
    {
        return TravelLogPatch::class;
    }

    public static function boot()
    {
        parent::boot();
        self::saving(function (TravelLog $model) {
            //if the entry spans multiple days we need to split it into different entries
            if ($model->end && !Carbon::parse($model->start)->isSameDay($model->end)) {
                $end = Carbon::parse($model->end)->copy();
                $model->end = Carbon::parse($model->start)->copy()->endOfDay();
                for ($day = Carbon::parse($model->start)->startOfDay()->addDay(); $day->lte($end); $day->addDay()) {
                    $model->replicate()->fill([
                        'start' => max(Carbon::parse($model->start)->copy(), $day->copy()->startOfDay()),
                        'end' => min(Carbon::parse($end)->copy(), $day->copy()->endOfDay()),
                    ])->save();
                }
            }
            Shift::computeAffected($model);
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function startLocation()
    {
        return $this->hasOne(TravelLogAddress::class);
    }
    public function endLocation()
    {
        return $this->hasOne(TravelLogAddress::class);
    }
}
