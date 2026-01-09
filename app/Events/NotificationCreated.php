<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class NotificationCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */

    public int $userId;
    public int $unreadCount;

    public function __construct( User $user)
    {
        $this->userId = $user->id;
        $this->unreadCount = $user->unreadNotifications()->count();
    }

    public function broadcastOn(): array   
    {
        return [new Channel("notification.{$this->userId}")];
    }
    
    public function broadcastAs()
    {
        return 'NotificationCreated';
    }

}
