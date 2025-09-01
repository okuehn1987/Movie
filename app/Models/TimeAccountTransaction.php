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
        $timeAccounts = $user->timeAccounts()->withTrashed()->get()->pluck('id');
        return $query->where(
            fn($q) => $q
                ->whereIn('from_id', $timeAccounts)
                ->orWhereIn('to_id', $timeAccounts)
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

    public function changes()
    {
        return $this->hasMany(TimeAccountTransactionChange::class);
    }

    public function revert()
    {
        $newTransaction = TimeAccount::transferBalanceFromTo(
            $this->amount,
            'Storniert: ' . $this->description . ' vom ' . $this->created_at->format('d.m.Y'),
            $this->to,
            $this->from
        );

        if ($newTransaction) {
            $this->changes()->delete();
            $newTransaction->changes()->delete();
        }
    }
}
