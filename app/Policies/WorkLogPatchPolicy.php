<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WorkLogPatch;
use Illuminate\Auth\Access\Response;

class WorkLogPatchPolicy
{
    /** Authorize all actions for super-admins */
    public function before(User $authUser)
    {
        header('authorized-by-gate: ' . self::class);

        if (
            $authUser->role === 'super-admin' ||
            $authUser->organization->owner_id === $authUser->id
        ) return true;

        return null; // only if this is returned, the other methods are checked
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, WorkLogPatch $workLogPatch): bool
    {
        return $user->hasPermission($workLogPatch->user, 'workLogPatch_permission', 'write') || $workLogPatch->user->supervisor_id === $user->id;
    }

    public function delete(User $user, WorkLogPatch $workLogPatch): bool
    {
        return $user->hasPermission($workLogPatch->user, 'workLogPatch_permission', 'write') || $workLogPatch->user->supervisor_id === $user->id;
    }
}
