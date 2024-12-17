<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class WorkLogPolicy
{
    use _AllowSuperAdmin;

    public function viewIndex(User $user): bool
    {
        return
            $user->supervisees()->count() > 0 ||
            $user->hasPermissionOrDelegation(null, 'workLog_permission', 'read');
    }

    public function viewShow(User $authUser, User $user): bool
    {
        return
            $authUser->id === $user->id ||
            $user->supervisor_id === $authUser->id ||
            $user->hasPermissionOrDelegation($user, 'workLog_permission', 'read');
    }

    public function create(User $user): bool
    {
        return true;
    }
}
