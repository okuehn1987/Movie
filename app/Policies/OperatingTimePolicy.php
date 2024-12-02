<?php

namespace App\Policies;

use App\Models\OperatingTime;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class OperatingTimePolicy
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

    public function viewIndex(User $user): bool
    {
        return
            $user->hasPermission(null, 'operatingTime_permission', 'read') ||
            $user->isSubstitutionFor()->some(fn($substitution) => $substitution->hasPermission(null, 'operatingTime_permission', 'read'));
    }


    public function create(User $user): bool
    {
        return
            $user->hasPermission(null, 'operatingTime_permission', 'write') ||
            $user->isSubstitutionFor()->some(fn($substitution) => $substitution->hasPermission(null, 'operatingTime_permission', 'write'));
    }

    public function delete(User $user): bool
    {
        return
            $user->hasPermission(null, 'operatingTime_permission', 'write') ||
            $user->isSubstitutionFor()->some(fn($substitution) => $substitution->hasPermission(null, 'operatingTime_permission', 'write'));
    }
}
