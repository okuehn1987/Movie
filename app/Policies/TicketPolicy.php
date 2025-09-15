<?php

namespace App\Policies;

use App\Models\User;

class TicketPolicy
{
    use _AllowSuperAdminAndOrganizationOwner;

    public function viewIndex(User $authUser): bool
    {
        return true;
    }

    public function create(User $authUser, User $user): bool
    {
        return $authUser->id == $user->id || $authUser->hasPermissionOrDelegation($user, 'ticket_permission', 'write');
    }

    public function update(User $authUser, User $user): bool
    {
        return $authUser->hasPermissionOrDelegation($user, 'ticket_permission', 'write');
    }

    public function delete(User $authUser, User $user): bool
    {
        return $authUser->hasPermissionOrDelegation($user, 'ticket_permission', 'write');
    }
}
