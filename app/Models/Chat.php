<?php

namespace App\Models;

use App\Exceptions\OpenAIServerException;
use App\Services\OpenAIService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\SoftDeletes;


class Chat extends Model
{
    use HasFactory, SoftDeletes;
    use ScopeInOrganization;


    protected $fillable = [
        "last_response_id",
        "chat_assistant_id",
        "user_id",
    ];

    public function chatAssistant()
    {
        return $this->belongsTo(ChatAssistant::class);
    }

    public function chatMessages()
    {
        return $this->hasMany(ChatMessage::class);
    }

    public function latestChatMessage()
    {
        return $this->hasOne(ChatMessage::class)->latestOfMany();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sendUserMessage(String $msg, bool $retryLastRun)
    {
        if (!$retryLastRun && trim($msg) !== '') {
            $this->chatMessages()->create([
                'msg' => $msg,
                'role' => 'user',
                'assistant_api_message_id' => null,
            ]);
        }
        OpenAIService::generateAIResponse($this, $msg, $retryLastRun);
    }


    public function trackOpenAiTokensUsed(float $number)
    {
        $this->forceFill([
            'open_ai_tokens_used' => DB::raw('open_ai_tokens_used + ' . $number),
        ])->save();
    }

    public function getDurationAttribute()
    {
        $chatMessages = $this->chatMessages()->latest()->get(["id", "created_at"]);

        $latestMessage = $chatMessages->first();
        $firstMessage = $chatMessages->last();

        if ($firstMessage) {
            return ceil($firstMessage->created_at->diffInSeconds($latestMessage->created_at) / 60);
        }
        return 0;
    }
}
