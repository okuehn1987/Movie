<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    use _AllowSuperAdminAndOrganizationOwner;

    public function create(User $authUser): bool
    {
        return true;
    }

    public function update(User $authUser, Ticket $ticket): bool
    {
        return true;
    }

    public function account(User $authUser, Ticket $ticket): bool
    {
        return $authUser->hasPermissionOrDelegation(null, 'ticket_permission', 'write');
    }
    public function delete(User $authUser, Ticket $ticket): bool
    {
        return $authUser->id == $ticket->user_id || $authUser->hasPermissionOrDelegation(null, 'ticket_permission', 'write');
    }
}
