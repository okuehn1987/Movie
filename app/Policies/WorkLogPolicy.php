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

    public function viewIndex(User $user): bool
    {
        return
            $user->supervisees()->count() > 0 ||
            $user->hasPermission(null, 'workLog_permission', 'read') ||
            $user->isSubstitutionFor()->some(fn($substitution) => $substitution->hasPermission(null, 'workLog_permission', 'read'));
    }

    public function viewShow(User $authUser, User $user): bool
    {
        return
            $authUser->id === $user->id ||
            $user->supervisor_id === $authUser->id ||
            $user->hasPermission($user, 'workLog_permission', 'read') ||
            $authUser->isSubstitutionFor()->some(fn($substitution) => $substitution->hasPermission($user, 'workLog_permission', 'read'));
    }

    public function create(User $user): bool
    {
        return true;
    }
}
