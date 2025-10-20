<?php

namespace App\Policies;

use App\Models\User;

trait _AllowSuperAdminAndOrganizationOwner
{
    public function before(User $user)
    {
        header('authorized-by-gate: ' . self::class);

        if (
            $user->role === 'super-admin' ||
            $user->loadMissing('organization')->organization->owner_id === $user->id
        ) return true;

        return null;
    }
}
