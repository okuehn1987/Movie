<?php

namespace App\Models\Traits;


trait HasLog
{
    abstract static function getLogModel();
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Illuminate\Database\Eloquent\Model, static>
     */
    public function log()
    {
        return $this->belongsTo(static::getLogModel(), (new (static::getLogModel()))->getForeignKey());
    }
}
