<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WorkLog;
use App\Models\WorkLogPatch;
use Illuminate\Auth\Access\Response;

class WorkLogPolicy
{
    /** Authorize all actions for super-admins */
    public function before(User $user)
    {
        header('authorized-by-gate: ' . self::class);

        if (
            $user->role === 'super-admin'
        ) return true;

        return null; // only if this is returned, the other methods are checked
    }

    public function viewShow(User $user, WorkLog $workLog): bool
    {
        return $user->id === $workLog->user_id || $workLog->user->supervisor_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function createPatch(User $user, WorkLog $workLog): bool
    {
        return $user->id === $workLog->user_id || $workLog->user->supervisor_id === $user->id;
    }

    public function updatePatch(User $user, WorkLog $workLog, WorkLogPatch $workLogPatch)
    {
        return $workLogPatch->user->supervisor_id === $user->id;
    }
}
