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
                $oldEnd = Carbon::parse($model->end)->copy();
                $model->end = Carbon::parse($model->start)->copy()->endOfDay();
                // run backwards for easier mutation
                for ($day = Carbon::parse($oldEnd)->startOfDay(); !$day->isSameDay($model->start); $day->subDay()) {
                    $model->replicate()->fill([
                        'start' => $day->copy()->startOfDay(),
                        'end' => min(Carbon::parse($oldEnd)->copy(), $day->copy()->endOfDay()),
                    ])->save();
                }
            }
            Shift::computeAffected($model);
        });
        self::deleting(function (WorkLog $model) {
            // dd(5);
            $patch = new WorkLogPatch([
                'start' => '1970-01-01 00:00:00',
                'end' => '1970-01-01 00:00:00',
                'user_id' => $model->user_id,
                'status' => 'accepted',
                'accepted_at' => now(),
                'is_home_office' => $model->is_home_office,
                'comment' => $model->comment,
                'type' => 'delete',
                'work_log_id' => $model->id,
            ]);

            Shift::computeAffected($patch);
            $patch->saveQuietly();
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
