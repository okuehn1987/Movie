<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChatFile extends Model
{
    use HasFactory, SoftDeletes;
    use ScopeInOrganization;

    protected $fillable = [
        'name',
        'file_name',
        'chat_assistant_id',
        'organization_id'
    ];

    public function chatAssistant()
    {
        return $this->belongsTo(ChatAssistant::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
