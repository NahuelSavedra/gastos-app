<?php
namespace App\Services;

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class TransferService
{
    public function transfer(Account $from, Account $to, float $amount): void
    {
        DB::transaction(function () use ($from, $to, $amount) {
            $transferCategory = Category::firstOrCreate(['name' => 'Transfer']);

            // Salida
            Transaction::create([
                'title'       => 'Transfer to ' . $to->name,
                'amount'      => $amount,
                'type'        => 'expense',
                'category_id' => $transferCategory->id,
                'account_id'  => $from->id,
                'date'        => now(),
            ]);
            $from->decrement('balance', $amount);

            // Entrada
            Transaction::create([
                'title'       => 'Transfer from ' . $from->name,
                'amount'      => $amount,
                'type'        => 'income',
                'category_id' => $transferCategory->id,
                'account_id'  => $to->id,
                'date'        => now(),
            ]);
            $to->increment('balance', $amount);
        });
    }
}

