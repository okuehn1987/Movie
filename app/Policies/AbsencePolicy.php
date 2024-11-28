<?php

namespace App\Policies;

use App\Models\Absence;
use App\Models\User;

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

    public function viewShow(User $authUser, Absence $absence): bool
    {
        return
            $authUser->id === $absence->user_id ||
            $authUser->supervisor_id === $absence->user_id ||
            $authUser->hasPermission($absence->user, 'absence_permission', 'read') ||
            $authUser->isSubstitutionFor()->some(fn($substitution) => $substitution->hasPermission($absence->user, 'absence_permission', 'read'));
    }

    public function create(User $authUser, User $user): bool
    {
        return
            $authUser->id === $user->id ||
            $authUser->supervisor_id === $user->id ||
            $authUser->hasPermission($user, 'absence_permission', 'write') ||
            $authUser->isSubstitutionFor()->some(fn($substitution) => $substitution->hasPermission($user, 'absence_permission', 'write'));
    }

    public function update(User $user, Absence $absence): bool
    {
        return
            $absence->user->supervisor_id === $user->id ||
            $user->hasPermission($absence->user, 'absence_permission', 'write') ||
            $user->isSubstitutionFor()->some(fn($substitution) => $substitution->hasPermission($absence->user, 'absence_permission', 'write'));
    }

    public function delete(User $user, Absence $absence): bool
    {
        return
            $absence->user->supervisor_id === $user->id ||
            $user->hasPermission($absence->user, 'absence_permission', 'write') ||
            $user->isSubstitutionFor()->some(fn($substitution) => $substitution->hasPermission($absence->user, 'absence_permission', 'write'));
    }
}
