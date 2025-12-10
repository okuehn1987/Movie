<?php

namespace App\Events;

use App\Models\Chat;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatMessageDelta implements ShouldBroadcast
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

    public function broadcastOn(): Channel
    {
        return new Channel("chat.{$this->chat->id}");
    }

    public function broadcastAs(): string
    {
        return 'ChatMessageDelta';
    }

    public function broadcastWith(): array
    {
        return [
            'msg' => $this->msg,
        ];
    }
}
