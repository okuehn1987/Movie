<?php

namespace App\Policies;

use App\Models\OperatingTime;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class OperatingTimePolicy
{
    use _AllowSuperAdminAndOrganizationOwner;

    public function viewIndex(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionOrDelegation(null, 'operatingTime_permission', 'write');
    }

    public function delete(User $user): bool
    {
        return $user->hasPermissionOrDelegation(null, 'operatingTime_permission', 'write');
    }
}
