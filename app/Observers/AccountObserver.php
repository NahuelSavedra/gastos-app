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
        // Limpiar cache principal
        Cache::forget('accounts_select');

        // Limpiar caches con exclude (si los usas)
        $accounts = Account::pluck('id');
        foreach ($accounts as $id) {
            Cache::forget("accounts_select_exclude_{$id}");
        }
    }
}
