<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstallmentPurchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'credit_card_id',
        'category_id',
        'title',
        'store',
        'total_amount',
        'installment_amount',
        'installments_count',
        'paid_installments',
        'first_payment_date',
        'notes',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'installment_amount' => 'decimal:2',
        'installments_count' => 'integer',
        'paid_installments' => 'integer',
        'first_payment_date' => 'date',
    ];

    public function creditCard(): BelongsTo
    {
        return $this->belongsTo(CreditCard::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Cuotas restantes por pagar
     */
    public function getRemainingInstallmentsAttribute(): int
    {
        return max(0, $this->installments_count - $this->paid_installments);
    }

    /**
     * Monto restante a pagar
     */
    public function getRemainingAmountAttribute(): float
    {
        return $this->remaining_installments * (float) $this->installment_amount;
    }

    /**
     * Fecha del próximo pago
     */
    public function getNextPaymentDateAttribute(): ?Carbon
    {
        if ($this->is_completed) {
            return null;
        }

        return $this->first_payment_date->copy()->addMonths($this->paid_installments);
    }

    /**
     * Porcentaje de progreso (0–100)
     */
    public function getProgressPercentageAttribute(): float
    {
        if ($this->installments_count === 0) {
            return 100;
        }

        return ($this->paid_installments / $this->installments_count) * 100;
    }

    /**
     * Si la compra está completamente pagada
     */
    public function getIsCompletedAttribute(): bool
    {
        return $this->paid_installments >= $this->installments_count;
    }

    /**
     * Scope: solo compras activas (no completadas)
     */
    public function scopeActive($query)
    {
        return $query->whereColumn('paid_installments', '<', 'installments_count');
    }

    /**
     * Scope: compras cuya última cuota vence en el mes actual
     */
    public function scopeCompletingThisMonth($query)
    {
        return $query->whereRaw(
            "date(first_payment_date, '+' || (installments_count - 1) || ' months') >= ? AND date(first_payment_date, '+' || (installments_count - 1) || ' months') <= ?",
            [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()]
        );
    }
}
