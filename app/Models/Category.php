<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Category extends Model
{
    protected $fillable = ['name', 'type'];

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get the transfer category (cached)
     */
    public static function getTransferCategory(): ?Category
    {
        return Cache::remember('category_transfer', 3600, function () {
            return self::where('name', 'Transfer')->first();
        });
    }

    /**
     * Get transfer category ID (cached)
     */
    public static function getTransferCategoryId(): ?int
    {
        return Cache::remember('category_transfer_id', 3600, function () {
            return self::where('name', 'Transfer')->value('id');
        });
    }
}
