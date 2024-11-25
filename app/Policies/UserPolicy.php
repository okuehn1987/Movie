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
        return $authUser->user_administration;
    }

    public function viewShow(User $authUser, User $user): bool
    {
        return
            $authUser->id === $user->supervisor_id ||
            $authUser->id === $user->id ||
            $authUser->user_administration;
    }

    public function create(User $authUser): bool
    {
        return $authUser->user_administration;
    }

    public function update(User $authUser, User $user): bool
    {
        return
            $authUser->id === $user->supervisor_id ||
            $authUser->user_administration;
    }

    public function delete(User $authUser, User $user): bool
    {
        return $authUser->user_administration;
    }

    public function publicAuth(User $authUser)
    {
        return true;
    }
}
