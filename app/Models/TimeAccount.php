<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class TimeAccount extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function fromTransactions()
    {
        return $this->hasMany(TimeAccountTransaction::class, 'from_id');
    }

    public function toTransactions()
    {
        return $this->hasMany(TimeAccountTransaction::class, 'to_id');
    }
}
