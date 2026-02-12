<?php

namespace App\Observers;

use App\Models\Account;
use Illuminate\Support\Facades\Cache;

class AccountObserver
{
    public function created(Account $account): void
    {
        $this->clearCache();
    }

    public function updated(Account $account): void
    {
        $this->clearCache();
    }

    public function deleted(Account $account): void
    {
        $this->clearCache();
    }

    protected function clearCache(): void
    {
        // Clear main account select cache
        Cache::forget('accounts_select');

        // Clear all account-specific caches (reasonable upper limit)
        for ($id = 1; $id <= 1000; $id++) {
            Cache::forget("accounts_select_exclude_{$id}");
        }

        // Clear all account balance caches
        foreach (Account::pluck('id') as $accountId) {
            Cache::forget("account_balance_{$accountId}");
        }
    }
}
