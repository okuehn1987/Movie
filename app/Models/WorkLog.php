<?php

namespace App\Models;

use App\Models\Traits\HasDuration;
use App\Models\Traits\HasPatches;
use App\Models\Traits\IsAccountable;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkLog extends Model
{
    use HasFactory, SoftDeletes;
    use ScopeInOrganization, HasPatches, IsAccountable, HasDuration;

    private static function getPatchModel()
    {
        return WorkLogPatch::class;
    }

    protected $guarded = [];

    protected $casts = ['is_home_office' => 'boolean'];

    public static function boot()
    {
        parent::boot();
        self::saving(function (WorkLog $model) {
            //if the entry spans multiple days we need to split it into different entries
            if ($model->end && !Carbon::parse($model->start)->isSameDay($model->end)) {
                if (Carbon::parse($model->start)->gt($model->end)) throw new Exception("start can't be after end");
                // run backwards for easier mutation
                for ($day = Carbon::parse($model->end)->startOfDay(); !$day->isSameDay($model->start); $day->subDay()) {
                    $model->replicate()->fill([
                        'start' => $day->copy()->startOfDay(),
                        'end' => min(Carbon::parse($model->end)->copy(), $day->copy()->endOfDay()),
                    ])->save();
                }
                $model->end = Carbon::parse($model->start)->copy()->endOfDay();
            }
            Shift::computeAffected($model);
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }
}
