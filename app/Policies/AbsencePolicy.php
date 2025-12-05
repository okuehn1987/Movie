<?php

namespace App\Policies;

use App\Enums\Status;
use App\Models\Absence;
use App\Models\HomeOfficeDay;
use App\Models\User;

class AbsencePolicy
{
    use _AllowSuperAdmin;

    public function viewIndex(User $authUser, User $user)
    {
        return
            $authUser->is($user) ||
            $authUser->id === $user->supervisor_id ||
            $authUser->hasPermissionOrDelegation($user, 'absence_permission', 'read');
    }
    public function viewShow(User $authUser, User $user): bool
    {
        return
            $authUser->is($user) ||
            $authUser->id === $user->supervisor_id ||
            ($authUser->group_id !== null && $authUser->group_id === $user->group_id) ||
            $authUser->hasPermissionOrDelegation($user, 'absence_permission', 'read');
    }

    public function create(User $authUser, User $user): bool
    {
        return
            $authUser->is($user) ||
            $authUser->id === $user->supervisor_id ||
            $authUser->hasPermissionOrDelegation($user, 'absence_permission', 'write');
    }

    public function update(User $authUser, User $user): bool
    {
        return
            $authUser->id === $user->supervisor_id  ||
            !$user->supervisor_id && $authUser->is($user) ||
            $authUser->hasPermissionOrDelegation($user, 'absence_permission', 'write');
    }

    public function deleteDispute(User $authUser, Absence $absence): bool
    {
        return $absence->status === Status::Created && $authUser->is($absence->user);
    }
    public function deleteHomeOfficeDispute(User $authUser, HomeOfficeDay $homeOfficeDay): bool
    {
        return $homeOfficeDay->status === Status::Created && $authUser->is($homeOfficeDay->user);
    }

    public function delete(User $authUser, Absence $absence): bool
    {
        return  $authUser->id === $absence->user->supervisor_id ||
            ($authUser->is($absence->user) && !$authUser->supervisor_id) ||
            $authUser->hasPermissionOrDelegation($absence->user, 'absence_permission', 'write');
    }

    public function deleteHomeOffice(User $authUser, HomeOfficeDay $homeOfficeDay): bool
    {

        return  $authUser->id === $homeOfficeDay->user->supervisor_id ||
            ($authUser->is($homeOfficeDay->user) && !$authUser->supervisor_id) ||
            $authUser->hasPermissionOrDelegation($homeOfficeDay->user, 'absence_permission', 'write');
    }

    public function deleteRequestable(User $authUser, Absence $absence)
    {
        return $authUser->is($absence->user) ||
            $authUser->id === $absence->user->supervisor_id ||
            $authUser->hasPermissionOrDelegation($absence->user, 'absence_permission', 'write');
    }

    public function deleteHomeOfficeRequestable(User $authUser, HomeOfficeDay $homeOfficeDay)
    {
        return $authUser->is($homeOfficeDay->user) ||
            $authUser->id === $homeOfficeDay->user->supervisor_id ||
            $authUser->hasPermissionOrDelegation($homeOfficeDay->user, 'absence_permission', 'write');
    }
}
