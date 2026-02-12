<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionTemplate extends Model
{
    protected $fillable = [
        'name',
        'title',
        'amount',
        'category_id',
        'account_id',
        'description',
        'is_recurring',
        'recurrence_type',
        'recurrence_day',
        'auto_create',
        'last_generated_at',
        'is_active',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_recurring' => 'boolean',
        'auto_create' => 'boolean',
        'is_active' => 'boolean',
        'last_generated_at' => 'date',
    ];

    protected $with = ['category', 'account'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Crear una transacción desde este template
     */
    public function createTransaction(array $overrides = []): Transaction
    {
        $data = array_merge([
            'title' => $this->title ?? $this->name,
            'amount' => $this->amount,
            'category_id' => $this->category_id,
            'account_id' => $this->account_id,
            'description' => $this->description,
            'date' => now(),
        ], $overrides);

        return Transaction::create($data);
    }

    public function isUsedThisMonth(): bool
    {
        if (! $this->is_recurring) {
            return false;
        }

        return $this->last_generated_at
            && $this->last_generated_at->month === now()->month
            && $this->last_generated_at->year === now()->year;
    }

    public function scopePendingThisMonth($query)
    {
        return $query->where(function ($q) {
            $q->where('is_recurring', false)
                ->orWhere(function ($sq) {
                    $sq->where('is_recurring', true)
                        ->where(function ($ssq) {
                            $ssq->whereNull('last_generated_at')
                                ->orWhere(function ($dateCheck) {
                                    $dateCheck->whereMonth('last_generated_at', '!=', now()->month)
                                        ->orWhereYear('last_generated_at', '!=', now()->year);
                                });
                        });
                });
        });
    }

    /**
     * Verificar si debe generarse automáticamente hoy
     */
    public function shouldGenerateToday(): bool
    {
        if (! $this->is_recurring || ! $this->auto_create || ! $this->is_active) {
            return false;
        }

        // Si ya se generó este mes, no generar de nuevo
        if ($this->last_generated_at && $this->last_generated_at->isSameMonth(now())) {
            return false;
        }

        // Verificar según el tipo de recurrencia
        return match ($this->recurrence_type) {
            'monthly' => now()->day == $this->recurrence_day,
            'weekly' => now()->dayOfWeek == $this->recurrence_day,
            'yearly' => now()->format('m-d') == $this->recurrence_day,
            default => false,
        };
    }

    /**
     * Scope para templates activos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para templates recurrentes
     */
    public function scopeRecurring($query)
    {
        return $query->where('is_recurring', true);
    }

    /**
     * Scope para auto-creables
     */
    public function scopeAutoCreate($query)
    {
        return $query->where('auto_create', true);
    }
}
