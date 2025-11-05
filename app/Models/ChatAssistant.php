<?php

namespace App\Models;

use App\Services\OpenAIService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class ChatAssistant extends Model
{
    use HasFactory, SoftDeletes;
    use ScopeInOrganization;

    protected $fillable = [
        'organization_id',
        'vector_store_id',
        'monthly_cost_limit',
    ];

    public function scopeUsable(Builder $query)
    {
        $query->whereNotNull('vector_store_id');
    }

    public function chats()
    {
        return $this->hasMany(Chat::class);
    }

    public function chatMessages()
    {
        return $this->hasManyThrough(ChatMessage::class, Chat::class);
    }

    public function chatFiles()
    {
        return $this->hasMany(ChatFile::class);
    }

    public function organization()
    {
        return $this->hasOne(Organization::class);
    }

    public function getTotalTokensForCurrentMonth()
    {
        return DB::table('chats')
            ->select(DB::raw('(SUM(open_ai_tokens_used)) as tokens'))
            ->where('chat_assistant_id', $this->id)
            ->whereMonth('created_at', date('m'))
            ->whereYear('created_at', date('Y'))
            ->first()->tokens;
    }
}
