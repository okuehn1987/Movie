<?php

namespace App\Policies;

use App\Models\TimeAccount;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TimeAccountPolicy
{
    use _AllowSuperAdminAndOrganizationOwner;

    public function viewIndex(User $authUser, User $user): bool
    {
        return
            $authUser->id == $user->id ||
            $authUser->hasPermissionOrDelegation($user, 'timeAccount_permission', 'read');
    }

    public function create(User $authUser, User $user): bool
    {
        return $authUser->hasPermissionOrDelegation($user, 'timeAccount_permission', 'write');
    }

    public function update(User $authUser, User $user): bool
    {
        return $authUser->hasPermissionOrDelegation($user, 'timeAccount_permission', 'write');
    }

    public function delete(User $authUser, User $user): bool
    {
        return $authUser->hasPermissionOrDelegation($user, 'timeAccount_permission', 'write');
    }
}
