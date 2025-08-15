<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class WorkLogPolicy
{
    use _AllowSuperAdmin;

    public function viewIndex(User $authUser): bool
    {
        return
            $authUser->supervisees->count() > 0 ||
            $authUser->hasPermissionOrDelegation(null, 'workLog_permission', 'read');
    }

    public function viewShow(User $authUser, User $user): bool
    {
        return
            $authUser->id === $user->id ||
            $authUser->id === $user->supervisor_id ||
            $authUser->hasPermissionOrDelegation($user, 'workLog_permission', 'read');
    }

    public function create(User $authUser, User $user): bool
    {
        return $authUser->is($user);
    }

    public function delete(User $authUser, User $user): bool
    {
        return
            $authUser->is($user) ||
            $authUser->id === $user->supervisor_id ||
            $authUser->hasPermissionOrDelegation($user, 'workLog_permission', 'write');
    }
}
