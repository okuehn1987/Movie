<?php

namespace App\Policies;

use App\Models\SpecialWorkingHoursFactor;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SpecialWorkingHoursFactorPolicy
{
    use _AllowSuperAdminAndOrganizationOwner;

    public function viewIndex(User $user): bool
    {
        return $user->hasPermissionOrDelegation(null, 'specialWorkingHoursFactor_permission', 'read');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionOrDelegation(null, 'specialWorkingHoursFactor_permission', 'write');
    }

    public function update(User $user): bool
    {
        return $user->hasPermissionOrDelegation(null, 'specialWorkingHoursFactor_permission', 'write');
    }

    public function delete(User $user): bool
    {
        return $user->hasPermissionOrDelegation(null, 'specialWorkingHoursFactor_permission', 'write');
    }
}
