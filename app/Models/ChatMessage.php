<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class ChatMessage extends Model
{
    use HasFactory, SoftDeletes;
    use ScopeInOrganization;


    protected $fillable = [
        'msg',
        'role',
        'assistant_api_message_id'
    ];

    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }
}
