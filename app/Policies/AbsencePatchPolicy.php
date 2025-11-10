<?php

namespace App\Policies;

use App\Enums\Status;
use App\Models\AbsencePatch;
use App\Models\User;

class AbsencePatchPolicy
{
    use _AllowSuperAdmin;

    public function create(User $authUser, User $user): bool
    {
        return
            $authUser->is($user) ||
            $authUser->id === $user->supervisor_id ||
            $authUser->hasPermissionOrDelegation($user, 'absence_permission', 'write');
    }

    public function update(User $authUser, AbsencePatch $absencePatch): bool
    {
        $user = $absencePatch->user;
        return
            $authUser->id === $user->supervisor_id  ||
            !$user->supervisor_id && $authUser->is($user) ||
            $authUser->hasPermissionOrDelegation($user, 'absence_permission', 'write');
    }

    public function delete(User $authUser, AbsencePatch $absencePatch): bool
    {
        return $absencePatch->status === Status::Created && $authUser->is($absencePatch->user);
    }
}
