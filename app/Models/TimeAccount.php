<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\ErrorSolutions\Contracts\BaseSolution;
use Spatie\ErrorSolutions\Contracts\ProvidesSolution;
use Spatie\ErrorSolutions\Contracts\Solution;




class TimeAccount extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public static function boot()
    {
        parent::boot();
        self::updating(function ($model) {
            if ($model->isDirty('balance')) {
                throw new class('Balance is not allowed to be updated directly.') extends Exception implements ProvidesSolution
                {
                    public function getSolution(): Solution
                    {
                        return BaseSolution::create()
                            ->setSolutionDescription("Use `TimeAccount::addBalance` or `TimeAccount::transferBalanceFromTo` instead.");
                    }
                };
            }
        });
    }

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

    /**
     * Method for changing the balance of an account.
     * Provide `$from` and `$to` to transfer balance from one account to another.
     * To add balance to just one account use `null` as `$from` account.
     * To remove balance from just one account use `null` as `$to` account.
     */
    public static function transferBalanceFromTo(float $amount, string $description, TimeAccount|null $from, TimeAccount|null $to)
    {
        if ($amount == 0)
            return; // dont create a transction at all
        if ($amount < 0)
            throw new Exception('Amount must be positive.');
        if (!$from && !$to)
            throw new Exception('Either from or to account must be provided.');
        if ($from?->id === $to?->id)
            throw new Exception('Cannot transfer balance to the same account.');

        DB::transaction(function () use ($amount, $description, $from, $to) {
            $from?->forceFill([
                'balance' => DB::raw("balance - ($amount)")
            ])->saveQuietly();

            $to?->forceFill([
                'balance' => DB::raw("balance + ($amount)")
            ])->saveQuietly();

            TimeAccountTransaction::create([
                'from_id' => $from?->id ?? null,
                'to_id' => $to?->id ?? null,
                'amount' => $amount,
                'description' => $description,
                'modified_by' => Auth::id(),
            ]);
        });
    }

    public function addBalance(float $balance, string $description)
    {
        if ($balance > 0) {
            self::transferBalanceFromTo(abs($balance), $description, null, $this);
        } else {
            self::transferBalanceFromTo(abs($balance), $description,  $this, null);
        }
    }
}
