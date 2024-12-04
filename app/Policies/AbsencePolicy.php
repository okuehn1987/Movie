<?php

namespace App\Policies;

use App\Models\Absence;
use App\Models\User;

class AbsencePolicy
{
    use _AllowSuperAdminAndOrganizationOwner;

    public function viewIndex(User $user)
    {
        return true;
    }
    public function viewShow(User $authUser, Absence $absence): bool
    {
        return
            $authUser->id === $absence->user_id ||
            $authUser->supervisor_id === $absence->user_id ||
            $authUser->hasPermissionOrDelegation($absence->user, 'absence_permission', 'read');
    }

    public function create(User $authUser, User $user): bool
    {
        return
            $authUser->id === $user->id ||
            $authUser->supervisor_id === $user->id ||
            $authUser->hasPermissionOrDelegation($user, 'absence_permission', 'write');
    }

    public function update(User $user, Absence $absence): bool
    {
        return
            $absence->user->supervisor_id === $user->id ||
            $user->hasPermissionOrDelegation($absence->user, 'absence_permission', 'write');
    }

    public function delete(User $user, Absence $absence): bool
    {
        return
            $absence->user->supervisor_id === $user->id ||
            $user->hasPermissionOrDelegation($absence->user, 'absence_permission', 'write');
    }
}
