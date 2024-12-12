<?php

namespace App\Policies;

use App\Models\Group;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class GroupPolicy
{
    use _AllowSuperAdminAndOrganizationOwner;

    public function viewIndex(User $user): bool
    {
        return true;
    }

    public function viewShow(User $user, Group $group): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionOrDelegation(null, 'group_permission', 'write');
    }

    public function update(User $user, Group $group): bool
    {
        return $user->hasPermissionOrDelegation(null, 'group_permission', 'write');
    }

    public function delete(User $user, Group $group): bool
    {
        return $user->hasPermissionOrDelegation(null, 'group_permission', 'write');
    }
}
