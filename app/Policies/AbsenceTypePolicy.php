<?php

namespace App\Policies;

use App\Models\AbsenceType;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AbsenceTypePolicy
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

    public function create(User $user): bool
    {
        return $user->hasPermission(null, 'absenceType_permission', 'write');
    }

    public function update(User $user, AbsenceType $absenceType): bool
    {
        return $user->hasPermission($absenceType->user, 'absenceType_permission', 'write');
    }

    public function delete(User $user, AbsenceType $absenceType): bool
    {
        return $user->hasPermission($absenceType->user, 'absenceType_permission', 'write');
    }
}
