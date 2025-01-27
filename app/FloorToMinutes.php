<?php

namespace App;

use Carbon\Carbon;

trait FloorToMinutes
{
    public static function boot()
    {
        parent::boot();
        self::saving(function ($model) {
            if ($model->isDirty('start')) {
                $latestWorkLog = $model->user->latestWorkLog;
                $lastEnd = $latestWorkLog ? Carbon::parse($latestWorkLog->end) : null;
                if ($lastEnd && $lastEnd->eq(Carbon::parse($model->start)->startOfMinute()->subMinute()))
                    $model->start = Carbon::parse($latestWorkLog->end)->startOfMinute()->format('Y-m-d H:i:s');
                else
                    $model->start = Carbon::parse($model->start)->startOfMinute()->format('Y-m-d H:i:s');
            }
            if ($model->isDirty('end')) {
                $model->end = $model->end ? Carbon::parse($model->end)->startOfMinute()->format('Y-m-d H:i:s') : null;
            }
        });
    }
}
