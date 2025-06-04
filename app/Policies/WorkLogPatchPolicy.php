<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WorkLogPatch;
use Illuminate\Auth\Access\Response;

class WorkLogPatchPolicy
{
    use _AllowSuperAdmin;

    public function create(User $authUser, User $user): bool
    {
        return $authUser->is($user);
    }

    public function update(User $authUser, User $user): bool
    {
        return
            $user->supervisor_id === $authUser->id ||
            $authUser->hasPermissionOrDelegation($user, 'workLogPatch_permission', 'write');
    }

    public function delete(User $authUser, User $user): bool
    {
        return
            $authUser->id === $user->id ||
            $authUser->hasPermissionOrDelegation($user, 'workLogPatch_permission', 'write');
    }
}
