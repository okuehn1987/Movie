<?php

namespace App\Policies;

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
}
