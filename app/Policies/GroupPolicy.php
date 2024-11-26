<?php

namespace App\Policies;

use App\Models\Group;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class GroupPolicy
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
        return $user->hasPermission(null, 'group_permission', 'read');
    }

    public function viewShow(User $user, Group $group): bool
    {
        return $user->hasPermission(null, 'group_permission', 'read');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(null, 'group_permission', 'write');
    }

    public function update(User $user, Group $group): bool
    {
        return $user->hasPermission(null, 'group_permission', 'write');
    }

    public function delete(User $user, Group $group): bool
    {
        return $user->hasPermission(null, 'group_permission', 'write');
    }
}
