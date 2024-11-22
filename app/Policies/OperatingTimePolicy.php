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
            $user->organization->owner->id === Organization::getCurrent()->id
        ) return true;

        return null; // only if this is returned, the other methods are checked
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, OperatingTime $operatingTime): bool
    {
        return false;
    }
}
