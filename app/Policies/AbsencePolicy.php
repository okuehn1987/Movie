<?php

namespace App\Policies;

use App\Models\Absence;
use App\Models\User;

class AbsencePolicy
{
    use _AllowSuperAdminAndOrganizationOwner;

    public function viewIndex(User $authUser, User $user)
    {
        return
            $authUser->id === $user->id ||
            $authUser->id === $user->supervisor_id ||
            $authUser->hasPermissionOrDelegation($user, 'absence_permission', 'read');
    }
    public function viewShow(User $authUser, User $user): bool
    {
        return
            $authUser->id === $user->id ||
            $authUser->id === $user->supervisor_id ||
            $authUser->group_id === $user->group_id ||
            $authUser->hasPermissionOrDelegation($user, 'absence_permission', 'read');
    }

    public function create(User $authUser, User $user): bool
    {
        return
            $authUser->id === $user->id ||
            $authUser->id === $user->supervisor_id ||
            $authUser->hasPermissionOrDelegation($user, 'absence_permission', 'write');
    }

    public function update(User $user, Absence $absence): bool
    {
        return
            $user->id == $absence->user->id ||
            $user->id === $absence->user->supervisor_id  ||
            $user->hasPermissionOrDelegation($absence->user, 'absence_permission', 'write');
    }

    public function delete(User $user, Absence $absence): bool
    {
        return
            $user->id === $absence->user->supervisor_id ||
            $user->hasPermissionOrDelegation($absence->user, 'absence_permission', 'write');
    }
}
