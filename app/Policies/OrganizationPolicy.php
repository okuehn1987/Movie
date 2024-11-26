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
        return $user->hasPermission(null, 'organization_permission', 'read');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(null, 'organization_permission', 'write');
    }

    public function update(User $user, Organization $organization): bool
    {
        return $user->hasPermission(null, 'organization_permission', 'write');
    }

    public function delete(User $user, Organization $organization): bool
    {
        return false; // never should anyone besides mbd be able to delete a org 
    }
}
