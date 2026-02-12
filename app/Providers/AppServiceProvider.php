<?php

namespace App\Providers;

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Observers\AccountObserver;
use App\Observers\CategoryObserver;
use App\Observers\TransactionObserver;
use App\Services\Import\CategoryMatcher\CategoryMatcherService;
use App\Services\Import\TransactionImportService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(CategoryMatcherService::class);
        $this->app->singleton(TransactionImportService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Category::observe(CategoryObserver::class);
        Account::observe(AccountObserver::class);
        Transaction::observe(TransactionObserver::class);
    }
}
