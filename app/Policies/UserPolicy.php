<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
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

    public function viewIndex(User $authUser): bool
    {
        return $authUser->hasPermission(null, 'user_permission', 'read');
    }

    public function viewShow(User $authUser, User $user): bool
    {
        return $authUser->hasPermission($user, 'user_permission', 'read') || $authUser->id === $user->id;
    }

    public function create(User $authUser): bool
    {
        return $authUser->hasPermission(null, 'user_permission', 'write');
    }

    public function update(User $authUser, User $user): bool
    {
        return $authUser->hasPermission($user, 'user_permission', 'write');
    }

    public function delete(User $authUser, User $user): bool
    {
        return $authUser->hasPermission($user, 'user_permission', 'write');
    }

    public function publicAuth(User $authUser)
    {
        return true;
    }
}
