<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class ChatMessage extends Model
{
    use HasFactory, SoftDeletes;
    use ScopeInOrganization;


    protected $fillable = [
        'msg',
        'role',
        'chat_id',
        'assistant_api_message_id',
        'open_ai_tokens_used',
    ];

    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }

    public function trackOpenAiTokensUsed(float $number): void
    {
        $this->forceFill([
            'open_ai_tokens_used' => DB::raw('open_ai_tokens_used + ' . $number),
        ])->save();
    }
}
