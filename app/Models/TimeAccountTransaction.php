<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TimeAccountTransaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function from()
    {
        return $this->belongsTo(TimeAccount::class, 'from_id');
    }

    public function to()
    {
        return $this->belongsTo(TimeAccount::class, 'to_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'modified_by');
    }
}
