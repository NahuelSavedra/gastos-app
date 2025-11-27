<?php

namespace App\Providers;

use App\Models\Account;
use App\Models\Category;
use App\Observers\AccountObserver;
use App\Observers\CategoryObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Category::observe(CategoryObserver::class);
        Account::observe(AccountObserver::class);
    }
}
