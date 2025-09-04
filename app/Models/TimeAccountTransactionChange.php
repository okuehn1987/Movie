<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimeAccountTransactionChange extends Model
{
    protected $guarded = [];

    public function timeAccountTransaction()
    {
        return $this->belongsTo(TimeAccountTransaction::class);
    }

    public static function createFor(TimeAccountTransaction|null $transaction, $date): null | TimeAccountTransactionChange
    {
        if (!$transaction) return null;
        return self::create([
            'time_account_transaction_id' => $transaction->id,
            'date' => $date,
            'amount' => ($transaction->from_id && !$transaction->to_id) ? $transaction->amount * -1 : $transaction->amount,
        ]);
    }
}
