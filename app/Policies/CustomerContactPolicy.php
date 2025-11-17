<?php

namespace App\Policies;

use App\Models\User;

class CustomerContactPolicy
{
    use _AllowSuperAdminAndOrganizationOwner;

    public function viewIndex(User $authUser): bool
    {
        return true;
    }

    public function create(User $authUser): bool
    {
        return $authUser->hasPermissionOrDelegation(null, 'customerContact_permission', 'write');
    }

    public function update(User $authUser): bool
    {
        return $authUser->hasPermissionOrDelegation(null, 'customerContact_permission', 'write');
    }

    public function delete(User $authUser): bool
    {
        return $authUser->hasPermissionOrDelegation(null, 'customerContact_permission', 'write');
    }
}
