<?php

namespace App\Policies;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class OrganizationPolicy
{
    /** Authorize all actions for super-admins */
    public function before(User $user)
    {
        header('authorized-by-gate: ' . self::class);

        if ($user->role === 'super-admin') return true;

        return null; // only if this is returned, the other methods are checked
    }

    public function viewIndex(User $user): bool
    {
        return false;
    }

    public function viewShow(User $user, Organization $organization): bool
    {
        return $user->organization->id === $organization->id;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Organization $organization): bool
    {
        return $user->id === $organization->owner->user_id;
    }

    public function delete(User $user, Organization $organization): bool
    {
        return false;
    }
}
