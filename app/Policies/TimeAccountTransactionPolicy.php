<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class TimeAccountTransactionPolicy
{
    use _AllowSuperAdminAndOrganizationOwner;

    public function viewIndex(User $authUser, User $user): bool
    {
        return
            $authUser->id == $user->id ||
            $authUser->id == $user->supervisor_id ||
            $authUser->hasPermissionOrDelegation($user, 'timeAccountTransaction', 'read');
    }

    public function create(User $authUser, User $user): bool
    {
        return $authUser->hasPermissionOrDelegation($user, 'timeAccountTransaction', 'write');
    }
}
