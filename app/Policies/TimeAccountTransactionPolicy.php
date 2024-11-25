<?php

namespace App\Policies;

use App\Models\TimeAccountTransaction;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TimeAccountTransactionPolicy
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

    public function create(User $user): bool
    {
        return false;
    }
}
