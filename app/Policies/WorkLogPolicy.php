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

    public function viewShow(User $authUser, User $user): bool
    {
        return $user->hasPermission($user, 'workLog_permission', 'read') || $user->supervisor_id === $authUser->id || $authUser->id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }
}
