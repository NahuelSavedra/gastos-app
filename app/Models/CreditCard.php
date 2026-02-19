<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class CreditCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'name',
        'last_four',
        'credit_limit',
        'closing_day',
        'due_day',
        'is_active',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'is_active' => 'boolean',
        'closing_day' => 'integer',
        'due_day' => 'integer',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function installmentPurchases(): HasMany
    {
        return $this->hasMany(InstallmentPurchase::class);
    }

    /**
     * Deuda total: suma de montos restantes de compras activas
     */
    public function getTotalDebtAttribute(): float
    {
        return Cache::remember("credit_card_debt_{$this->id}", 300, function () {
            return $this->installmentPurchases()
                ->active()
                ->get()
                ->sum(fn ($p) => $p->remaining_amount);
        });
    }

    /**
     * Pago mensual estimado: suma de cuotas mensuales de compras activas
     */
    public function getMonthlyPaymentAttribute(): float
    {
        return Cache::remember("credit_card_monthly_{$this->id}", 300, function () {
            return $this->installmentPurchases()
                ->active()
                ->sum('installment_amount');
        });
    }

    /**
     * Crédito disponible
     */
    public function getAvailableCreditAttribute(): float
    {
        return $this->credit_limit - $this->total_debt;
    }

    /**
     * Limpiar caches de esta tarjeta
     */
    public function clearCaches(): void
    {
        Cache::forget("credit_card_debt_{$this->id}");
        Cache::forget("credit_card_monthly_{$this->id}");
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
