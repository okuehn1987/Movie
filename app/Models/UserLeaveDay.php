<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLeaveDay extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'leave_days',
        'type',
        'active_since',
    ];

    public function user()
    {
        return $this->belongsTo(User::class());
    }
}
