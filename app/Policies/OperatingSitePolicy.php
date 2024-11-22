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

    /**
     * Determine whether the user can view any models.
     */
    public function viewIndex(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function viewShow(User $user, OperatingSite $operatingSite): bool
    {
        return $user->operatingSite_id === $operatingSite->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, OperatingSite $operatingSite): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, OperatingSite $operatingSite): bool
    {
        return false;
    }
}
