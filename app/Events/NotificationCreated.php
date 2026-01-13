<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class NotificationCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */

    private int $userId;
    private int $unreadCount;

    public function __construct(User $user)
    {
        $this->userId = $user->id;
        $this->unreadCount = $user->unreadNotifications()->count() + 1;
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel("notification.{$this->userId}")];
    }

    public function broadcastWith(): array
    {
        return ['unreadCount' => $this->unreadCount];
    }
}
