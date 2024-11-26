<?php

namespace App\Policies;

use App\Models\Absence;
use App\Models\OrganizationUser;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AbsencePolicy
{
    /** Authorize all actions for super-admins */
    public function before(User $user)
    {
        header('authorized-by-gate: ' . self::class);

        if (
            $user->role === 'super-admin' ||
            $user->organization->owner_id === $user->id
        ) return true;

        return null; // only if this is returned, the other methods are checked
    }

    public function create(User $authUser, User $user): bool
    {
        return
            $authUser->hasPermission($user, 'absence_permission', 'write') ||
            $authUser->id === $user->id ||
            $authUser->supervisor_id === $user->id;
    }

    public function update(User $user, Absence $absence): bool
    {
        return
            $user->hasPermission($absence->user, 'absence_permission', 'write') ||
            $absence->user->supervisor_id === $user->id;
    }

    public function delete(User $user, Absence $absence): bool
    {
        return
            $user->hasPermission($absence->user, 'absence_permission', 'write') ||
            $absence->user->supervisor_id === $user->id;
    }
}
