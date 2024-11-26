<?php

namespace App\Policies;

use App\Models\OperatingSite;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class OperatingSitePolicy
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
        return $user->hasPermission(null, 'operatingSite_permission', 'read');
    }

    public function viewShow(User $user, OperatingSite $operatingSite): bool
    {
        return $user->hasPermission(null, 'operatingSite_permission', 'read') && $user->operatingSite_id === $operatingSite->id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(null, 'operatingSite_permission', 'write');
    }

    public function update(User $user, OperatingSite $operatingSite): bool
    {
        return $user->hasPermission(null, 'operatingSite_permission', 'write');
    }

    public function delete(User $user, OperatingSite $operatingSite): bool
    {
        return $user->hasPermission(null, 'operatingSite_permission', 'write');
    }
}
