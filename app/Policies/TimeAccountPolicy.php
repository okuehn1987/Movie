<?php

namespace App\Policies;

use App\Models\TimeAccount;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TimeAccountPolicy
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

    public function create(User $authUser, User $user): bool
    {
        return $authUser->id === $user->supervisor_id;
    }

    public function update(User $user, TimeAccount $timeAccount): bool
    {
        return $user->id === $timeAccount->user->supervisor_id;
    }

    public function delete(User $user, TimeAccount $timeAccount): bool
    {
        return $user->id === $timeAccount->user->supervisor_id;
    }
}
