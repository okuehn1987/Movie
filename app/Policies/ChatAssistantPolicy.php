<?php

namespace App\Policies;

use App\Models\User;

class ChatAssistantPolicy
{
    use _AllowSuperAdminAndOrganizationOwner;
    /**
     * Create a new policy instance.
     */
    public function viewIndex(User $authUser): bool
    {
        return $authUser->hasPermissionOrDelegation(null, 'chatAssistant_permission', 'read');
    }

    public function update(User $authUser): bool
    {
        return $authUser->hasPermissionOrDelegation(null, 'chatAssistant_permission', 'write');
    }

    public function editChatFiles(User $authUser): bool
    {
        return $authUser->hasPermissionOrDelegation(null, 'chatFile_permission', 'write');
    }

    public function showChatFiles(User $authUser): bool
    {
        return $authUser->hasPermissionOrDelegation(null, 'chatFile_permission', 'read');
    }

    public function showPaymentInfo(User $authUser): bool
    {
        return $authUser->hasPermissionOrDelegation(null, 'isaPayment_permission', 'read');
    }
}
