<?php

namespace App\Policies;

use App\Models\SpecialWorkingHoursFactor;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SpecialWorkingHoursFactorPolicy
{
    /** Authorize all actions for super-admins */
    public function before(User $user)
    {
        header('authorized-by-gate: ' . self::class);

        if (
            $user->role === 'super-admin' ||
            $user->organization->owner_id === $user->id
        ) return true;

        return null; // only if this is returned, the other methods are checked
    }

    public function view(User $user, SpecialWorkingHoursFactor $specialWorkingHoursFactor): bool
    {
        return false; // maybe make swhf accesable for users?
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function delete(User $user, SpecialWorkingHoursFactor $specialWorkingHoursFactor): bool
    {
        return false;
    }
}
