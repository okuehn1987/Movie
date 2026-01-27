<?php

namespace App\Models\Traits;

use App\Enums\Status;
use App\Models\User;
use Carbon\CarbonInterface;

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

    public static function getCurrentEntries(User $user, bool $withOpenDisputes = false, CarbonInterface|null $start = null, CarbonInterface|null $end = null)
    {
        if ($withOpenDisputes) {
            $logs = (new (static::getLogModel()))
                ->whereIn('status', [Status::Accepted, Status::Created])
                ->where('user_id', $user->id)
                ->when($end, fn($q) => $q->where('start', '<=', $end))
                ->when($start, fn($q) => $q->where('end', '>=', $start))
                ->with(['currentAcceptedPatch', 'patches' => fn($query) => $query->where('status', Status::Created)])
                ->get()
                ->flatMap(fn($log) => [($log->currentAcceptedPatch ?? $log), ...$log->patches]);
        } else {
            $logs = (new (static::getLogModel()))
                ->where('status', Status::Accepted)
                ->where('user_id', $user->id)
                ->when($end, fn($q) => $q->where('start', '<=', $end))
                ->when($start, fn($q) => $q->where('end', '>=', $start))
                ->with('currentAcceptedPatch')
                ->get()
                ->map(fn($log) => $log->currentAcceptedPatch ?? $log);
        }

        return $logs;
    }
}
