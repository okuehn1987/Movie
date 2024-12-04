<?php

namespace App\Policies;

use App\Models\AbsenceType;
use App\Models\User;
use Illuminate\Auth\Access\Response;

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
        return
            $user->id === $user->id ||
            $user->id === $user->supervisor_id ||
            $user->hasPermissionOrDelegation(null, 'absenceType_permission', 'write');
    }

    public function update(User $user, AbsenceType $absenceType): bool
    {
        return
            $user->id === $user->supervisor_id ||
            $user->hasPermissionOrDelegation($absenceType->user, 'absenceType_permission', 'write');
    }

    public function delete(User $user, AbsenceType $absenceType): bool
    {
        return
            $user->id === $user->supervisor_id ||
            $user->hasPermissionOrDelegation($absenceType->user, 'absenceType_permission', 'write');
    }
}
