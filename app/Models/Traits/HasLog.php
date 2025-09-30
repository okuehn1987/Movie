<?php

namespace App\Models\Traits;

use App\Models\User;

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

    public static function getCurrentEntries(User $user)
    {
        $logs = (new (static::getLogModel()))
            ->inOrganization()
            ->where('status', 'accepted')
            ->where('user_id', $user->id)
            ->with('currentAcceptedPatch')
            ->get();
        return $logs->map(fn($log) => $log->currentAcceptedPatch ?? $log);
    }
}
