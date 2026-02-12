<?php

namespace App\Observers;

use App\Models\Transaction;
use Illuminate\Support\Facades\Cache;

class TransactionObserver
{
    public function created(Transaction $transaction): void
    {
        $this->clearRelatedCaches($transaction);
    }

    public function updated(Transaction $transaction): void
    {
        $this->clearRelatedCaches($transaction);

        // If account changed, clear old account too
        if ($transaction->isDirty('account_id')) {
            $oldAccountId = $transaction->getOriginal('account_id');
            if ($oldAccountId) {
                Cache::forget("account_balance_{$oldAccountId}");
            }
        }
    }

    public function deleted(Transaction $transaction): void
    {
        $this->clearRelatedCaches($transaction);
    }

    protected function clearRelatedCaches(Transaction $transaction): void
    {
        // Clear account balance cache
        if ($transaction->account_id) {
            Cache::forget("account_balance_{$transaction->account_id}");
        }

        // Clear widget caches
        Cache::forget('expense_categories_chart_'.now()->format('Y-m'));
        $txMonth = $transaction->date->format('Y-m');
        if ($txMonth !== now()->format('Y-m')) {
            Cache::forget("expense_categories_chart_{$txMonth}");
        }

        Cache::forget('categories_select');
    }
}
