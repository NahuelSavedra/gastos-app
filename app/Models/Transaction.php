<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $fillable = [
        'title',
        'description',
        'amount',
        'category_id',
        'date',
        'account_id',
        'reference_id',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
    ];

    protected $appends = ['type']; // Para que siempre esté disponible

    // Automatically eager load in all queries
    protected $with = ['category'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Accessor para obtener el type desde la categoría
     */
    protected function type(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->category?->type ?? 'expense',
        );
    }

    protected static function booted(): void
    {
        static::creating(function (Transaction $tx) {
            if (blank($tx->date)) {
                $tx->date = now()->toDateString();
            }
        });
    }

    public function scopeIncome($query)
    {
        return $query->whereHas('category', function ($q) {
            $q->where('type', 'income');
        });
    }

    public function scopeExpense($query)
    {
        return $query->whereHas('category', function ($q) {
            $q->where('type', 'expense');
        });
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year);
    }

    public function isTransfer(): bool
    {
        return $this->category && $this->category->name === 'Transfer';
    }

    public function relatedTransfer()
    {
        if (! $this->reference_id) {
            return null;
        }

        return static::where('reference_id', $this->reference_id)
            ->where('id', '!=', $this->id)
            ->first();
    }

    public function scopeTransfers($query)
    {
        return $query->whereHas('category', function ($q) {
            $q->where('name', 'Transfer');
        });
    }

    /**
     * Scope for specific month and year
     */
    public function scopeForMonth($query, int $month, int $year)
    {
        return $query->whereMonth('date', $month)
            ->whereYear('date', $year);
    }

    /**
     * Scope to exclude transfers
     */
    public function scopeExcludeTransfers($query)
    {
        $transferCategoryId = Category::getTransferCategoryId();

        return $transferCategoryId
            ? $query->where('category_id', '!=', $transferCategoryId)
            : $query;
    }

    /**
     * Scope for date range
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope for specific account
     */
    public function scopeForAccount($query, int $accountId)
    {
        return $query->where('account_id', $accountId);
    }
}
