<?php

namespace App\Policies;

use App\Models\OperatingSite;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class OperatingSitePolicy
{
    use _AllowSuperAdminAndOrganizationOwner;

    public function viewIndex(User $user): bool
    {
        return true;
    }

    public function viewShow(User $user, OperatingSite $operatingSite): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionOrDelegation(null, 'operatingSite_permission', 'write');
    }

    public function update(User $user, OperatingSite $operatingSite): bool
    {
        return $user->hasPermissionOrDelegation(null, 'operatingSite_permission', 'write');
    }

    public function delete(User $user, OperatingSite $operatingSite): bool
    {
        return $user->hasPermissionOrDelegation(null, 'operatingSite_permission', 'write');
    }
}
