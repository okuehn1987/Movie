<?php

namespace App\Policies;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class OrganizationPolicy
{
    use _AllowSuperAdmin;

    public function viewShow(User $user, Organization $organization): bool
    {
        return $user->hasPermissionOrDelegation(null, 'organization_permission', 'read');
    }

    public function create(User $user): bool
    {
        return false; // never should anyone besides mbd be able to create an org 
    }

    public function update(User $user, Organization $organization): bool
    {
        return $user->hasPermissionOrDelegation(null, 'organization_permission', 'write');
    }

    public function delete(User $user, Organization $organization): bool
    {
        return false; // never should anyone besides mbd be able to delete an org 
    }
}
