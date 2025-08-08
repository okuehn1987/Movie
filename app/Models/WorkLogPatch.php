<?php

namespace App\Models;

use App\Models\Traits\HasDuration;
use App\Models\Traits\HasLog;
use App\Models\Traits\IsAccountable;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkLogPatch extends Model
{
    use HasFactory, SoftDeletes;
    use ScopeInOrganization, HasLog, IsAccountable, HasDuration;

    protected $guarded = [];

    protected $casts = ['is_home_office' => 'boolean'];

    private static function getLogModel()
    {
        return WorkLog::class;
    }

    public static function boot()
    {
        parent::boot();
        self::saving(function (WorkLogPatch $model) {
            //if the entry spans multiple days we need to split it into different entries
            // dd($model);
            if ($model->status != 'accepted' || !$model->accepted_at) return;
            if ($model->end && !Carbon::parse($model->start)->isSameDay($model->end)) {
                if (Carbon::parse($model->start)->gt($model->end)) throw new Exception("start can't be after end");
                $end = Carbon::parse($model->end)->copy();
                $model->end = Carbon::parse($model->start)->copy()->endOfDay();
                Shift::computeAffected($model);


                for ($day = Carbon::parse($model->start)->startOfDay()->addDay(); $day->lte($end); $day->addDay()) {
                    $log = WorkLog::createQuietly([
                        ...$model->log->toArray(),
                        'start' => max(Carbon::parse($model->start)->copy(), $day->copy()->startOfDay()),
                        'end' => min(Carbon::parse($end)->copy(), $day->copy()->endOfDay()),
                        'id' => null
                    ]);

                    $model->replicate()->fill([
                        'start' => max(Carbon::parse($model->start)->copy(), $day->copy()->startOfDay()),
                        'end' => min(Carbon::parse($end)->copy(), $day->copy()->endOfDay()),
                        'work_log_id' => $log->id,
                    ])->save();
                }
            } else {
                Shift::computeAffected($model);
            }
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
