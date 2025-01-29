<?php

namespace App;

use Carbon\Carbon;

trait FloorToMinutes
{
    public static function boot()
    {
        parent::boot();
        self::saving(function ($model) {
            $model->start = Carbon::parse($model->start)->startOfMinute()->format('Y-m-d H:i:s');
            $model->end = $model->end !== null ? Carbon::parse($model->end)->startOfMinute()->format('Y-m-d H:i:s') : null;
        });
    }
}
