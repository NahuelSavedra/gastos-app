<?php

namespace App\Observers;

use App\Models\Category;
use Illuminate\Support\Facades\Cache;

class CategoryObserver
{
    public function created(Category $category): void
    {
        $this->clearCache();
    }

    public function updated(Category $category): void
    {
        $this->clearCache();
        Cache::forget("category_{$category->id}");
    }

    public function deleted(Category $category): void
    {
        $this->clearCache();
        Cache::forget("category_{$category->id}");
    }

    protected function clearCache(): void
    {
        Cache::forget('categories_select');
        Cache::forget('transfer_income_category');
    }
}
