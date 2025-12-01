<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;

class CustomerPolicy
{
    use _AllowSuperAdminAndOrganizationOwner;

    /**
     * Create a new policy instance.
     */
    public function viewIndex(User $user): bool
    {
        return true;
    }

    public function viewShow(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionOrDelegation(null, 'customer_permission', 'write');
    }

    public function update(User $user): bool
    {
        return $user->hasPermissionOrDelegation(null, 'customer_permission', 'write');
    }

    public function delete(User $user): bool
    {
        return $user->hasPermissionOrDelegation(null, 'customer_permission', 'write');
    }
}
