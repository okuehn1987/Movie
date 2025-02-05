<?php

use App\Models\TimeAccount;
use App\Models\TimeAccountTransaction;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        TimeAccount::all()->each(function (TimeAccount $timeAccount) {
            $timeAccount->balance = round($timeAccount->balance * 3600);
            $timeAccount->balance_limit = round($timeAccount->balance_limit * 3600);
            $timeAccount->saveQuietly();
        });

        Schema::table('time_account_transactions', function (Blueprint $table) {
            $table->float('amount')->change();
        });

        TimeAccountTransaction::all()->each(function (TimeAccountTransaction $timeAccountTransaction) {
            $timeAccountTransaction->amount = round($timeAccountTransaction->amount * 3600);
            $timeAccountTransaction->save();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seconds', function (Blueprint $table) {
            //
        });
    }
};
