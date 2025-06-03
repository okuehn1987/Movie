<?php

namespace App\Policies;

use App\Models\User;

class AbsenceTypePolicy
{
    use _AllowSuperAdminAndOrganizationOwner;

    public function viewIndex(User $user): bool
    {
        return
            $user->hasPermissionOrDelegation(null, 'absenceType_permission', 'read');
    }

    public function viewShow(User $authUser, User $user): bool
    {
        return
            $authUser->id === $user->id ||
            $authUser->id === $user->supervisor_id ||
            $authUser->hasPermissionOrDelegation($user, 'absenceType_permission', 'read');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionOrDelegation(null, 'absenceType_permission', 'write');
    }

    public function update(User $user): bool
    {
        return $user->hasPermissionOrDelegation(null, 'absenceType_permission', 'write');
    }

    public function delete(User $user): bool
    {
        return $user->hasPermissionOrDelegation(null, 'absenceType_permission', 'write');
    }
}
