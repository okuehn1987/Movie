<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    use _AllowSuperAdminAndOrganizationOwner;

    public function viewIndex(User $authUser): bool
    {
        return $authUser->hasPermissionOrDelegation(null, 'user_permission', 'read');
    }

    public function viewShow(User $authUser, User $user): bool
    {
        return
            $authUser->id === $user->id ||
            $authUser->id === $user->supervisor_id ||
            $authUser->hasPermissionOrDelegation($user, 'user_permission', 'read');
    }

    public function create(User $authUser): bool
    {
        return $authUser->hasPermissionOrDelegation(null, 'user_permission', 'write');
    }

    public function update(User $authUser, User $user): bool
    {
        return $authUser->hasPermissionOrDelegation($user, 'user_permission', 'write');
    }

    public function delete(User $authUser, User $user): bool
    {
        return
            $authUser->hasPermissionOrDelegation($user, 'user_permission', 'write');
    }

    public function publicAuth(User $authUser)
    {
        return true;
    }
}
