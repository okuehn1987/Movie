<?php

namespace App\Events;

use App\Models\Chat;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class ChatMessageDelta implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    private string $msg;
    private Chat $chat;
    public function __construct(Chat $chat, string $msg)
    {
        $this->chat = $chat;
        $this->msg = $msg;
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel("chat.{$this->chat->id}")];
    }

    public function broadcastWith(): array
    {
        return [
            'msg' => $this->msg,
        ];
    }
}
