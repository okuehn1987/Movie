<?php

namespace App\Policies;

use App\Models\User;

trait _AllowSuperAdmin
{
    public function before(User $user)
    {
        header('authorized-by-gate: ' . self::class);

        if ($user->role === 'super-admin') return true;

        return null;
    }
}
