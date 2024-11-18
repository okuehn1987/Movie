<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class TimeAccount extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    //TODO: add balance_truncation_day to timeaccount instead of organization 

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

    public function timeAccountSetting()
    {
        return $this->belongsTo(TimeAccountSetting::class);
    }

    public function updateBalance(float $balance, string $description, ?bool $is_system_generated = false)
    {
        DB::transaction(function () use ($balance, $description, $is_system_generated) {
            $this->forceFill([
                'balance' => DB::raw("balance + ($balance)")
            ])->save();

            TimeAccountTransaction::create([
                'to_id' => $this->id,
                'is_system_generated' => $is_system_generated,
                'amount' => $balance,
                'description' => $description
            ]);
        });
    }
}
