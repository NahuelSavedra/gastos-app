<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ClearTransactionCache extends Command
{
    protected $signature = 'transactions:clear-cache';
    protected $description = 'Limpia el cache de categorías y cuentas';

    public function handle()
    {
        $keys = [
            'categories_select',
            'transfer_income_category',
        ];

        // Limpiar cuentas con exclude
        $accountKeys = Cache::get('account_cache_keys', []);
        $keys = array_merge($keys, $accountKeys);

        foreach ($keys as $key) {
            Cache::forget($key);
        }

        $this->info('Cache limpiado exitosamente');
    }
}
