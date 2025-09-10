<?php

namespace App\Policies;

use App\Models\User;

class TicketPolicy
{
    use _AllowSuperAdminAndOrganizationOwner;

    //FIXME: Permissions
    public function viewIndex(User $authUser): bool
    {
        return true;
    }

    public function create(User $authUser, User $user): bool
    {
        return $authUser->id == $user->id || true;
    }

    public function update(User $authUser, User $user): bool
    {
        return $authUser->id == $user->id || true;
    }

    public function delete(User $authUser, User $user): bool
    {
        return true;
    }
}
