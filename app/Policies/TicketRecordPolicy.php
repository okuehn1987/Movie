<?php

namespace App\Policies;

use App\Models\TicketRecord;
use App\Models\User;

class TicketRecordPolicy
{
    use _AllowSuperAdminAndOrganizationOwner;

    public function update(User $authUser, TicketRecord $ticketRecord)
    {
        return $authUser->id === $ticketRecord->user_id || $authUser->hasPermissionOrDelegation($ticketRecord->user, 'ticket_permission', 'write');
    }
}
