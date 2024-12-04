<?php

namespace App\Policies;

use App\Models\TimeAccountSetting;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TimeAccountSettingPolicy
{
    use _AllowSuperAdminAndOrganizationOwner;

    public function viewIndex(User $user): bool
    {
        return
            $user->hasPermissionOrDelegation(null, 'timeAccountSetting_permission', 'read');
    }

    public function create(User $user): bool
    {
        return
            $user->hasPermissionOrDelegation(null, 'timeAccountSetting_permission', 'write');
    }

    public function update(User $user, TimeAccountSetting $timeAccountSetting): bool
    {
        return
            $user->hasPermissionOrDelegation(null, 'timeAccountSetting_permission', 'write');
    }

    public function delete(User $user, TimeAccountSetting $timeAccountSetting): bool
    {
        return
            $user->hasPermissionOrDelegation(null, 'timeAccountSetting_permission', 'write');
    }
}
