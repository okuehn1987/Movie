<?php

namespace App\Policies;

use App\Models\TimeAccountSetting;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TimeAccountSettingPolicy
{
    /** Authorize all actions for super-admins */
    public function before(User $authUser)
    {
        header('authorized-by-gate: ' . self::class);

        if (
            $authUser->role === 'super-admin' ||
            $authUser->organization->owner_id === $authUser->id
        ) return true;

        return null; // only if this is returned, the other methods are checked
    }

    public function viewIndex(User $user): bool
    {
        return $user->hasPermission(null, 'timeAccountSetting_permission', 'read');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(null, 'timeAccountSetting_permission', 'write');
    }

    public function update(User $user, TimeAccountSetting $timeAccountSetting): bool
    {
        return $user->hasPermission(null, 'timeAccountSetting_permission', 'write');
    }

    public function delete(User $user, TimeAccountSetting $timeAccountSetting): bool
    {
        return $user->hasPermission(null, 'timeAccountSetting_permission', 'write');
    }
}
