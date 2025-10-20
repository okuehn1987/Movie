<?php

namespace App\Models\Traits;

use App\Enums\Status;
use Illuminate\Database\Eloquent\Builder;

trait HasPatches
{
    abstract static function getPatchModel();
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<Illuminate\Database\Eloquent\Model, static>
     */
    public function patches()
    {
        return $this->hasMany(static::getPatchModel());
    }

    public function currentAcceptedPatch()
    {
        return $this->patches()->one()->OfMany(['accepted_at' => 'max'], fn(Builder $q) => $q->where('status', Status::Accepted));
    }

    public function latestPatch()
    {
        return $this->patches()->one()->OfMany(['created_at' => 'max']);
    }
}
