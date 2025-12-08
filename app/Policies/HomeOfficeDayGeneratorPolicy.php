<?php

namespace App\Policies;

use App\Models\HomeOfficeDayGenerator;
use App\Models\User;

class HomeOfficeDayGeneratorPolicy
{
    use _AllowSuperAdmin;
    /**
     * Create a new policy instance.
     */
    public function delete(User $authUser, HomeOfficeDayGenerator $homeOfficeDayGenerator): bool
    {
        return  $authUser->id === $homeOfficeDayGenerator->user->supervisor_id ||
            ($authUser->is($homeOfficeDayGenerator->user) && !$authUser->supervisor_id) ||
            $authUser->hasPermissionOrDelegation($homeOfficeDayGenerator->user, 'home_office_permission', 'write');
    }
}
