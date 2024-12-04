<?php

namespace App\Policies;

use App\Models\TimeAccountTransaction;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TimeAccountTransactionPolicy
{
    use _AllowSuperAdminAndOrganizationOwner;
    public function viewIndex(User $authUser, User $user): bool
    {
        return
            $authUser->hasPermissionOrDelegation($user, 'timeAccountTransaction', 'read');;
    }

    public function create(User $authUser, User $user): bool
    {
        return
            $authUser->hasPermissionOrDelegation($user, 'timeAccountTransaction', 'write');
    }
}
