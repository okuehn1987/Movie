<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class TimeAccountTransaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public static function scopeForUser(Builder $query, User $user)
    {
        return $query->where(
            fn($q) => $q
                ->whereIn('from_id', $user->timeAccounts()->withTrashed()->select('id'))
                ->orWhereIn('to_id', $user->timeAccounts()->withTrashed()->select('id'))
        );
    }

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

    public function revert()
    {
        TimeAccount::transferBalanceFromTo(
            $this->amount,
            'Revert: ' . $this->description . ' vom ' . $this->created_at->format('d.m.Y'),
            $this->to,
            $this->from
        );
    }
}
