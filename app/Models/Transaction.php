<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $fillable = [
        'title',
        'description',
        'amount',
        'type',
        'category_id',
        'date',
        'account_id',
        'reference_id',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    protected static function booted(): void
    {
        // Al crear, si no hay fecha => hoy; si no hay type pero hay categoría => tomar de la categoría
        static::creating(function (Transaction $tx) {
            if (blank($tx->date)) {
                $tx->date = now()->toDateString();
            }

            if (filled($tx->category_id) && blank($tx->type)) {
                $tx->type = optional($tx->category()->first())->type;
            }
        });

        // Al actualizar, si cambió la categoría y no se forzó type, sincronizar
        static::saving(function (Transaction $tx) {
            if (blank($tx->date)) {
                $tx->date = now()->toDateString();
            }

            if ($tx->isDirty('category_id') && blank($tx->getDirty()['type'] ?? null)) {
                $tx->type = optional($tx->category()->first())->type;
            }
        });
    }

    public function scopeIncome($query)
    {
        return $query->where('type', 'income');
    }

    public function scopeExpense($query)
    {
        return $query->where('type', 'expense');
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year);
    }
    /**
     * Verificar si es una transferencia
     */
    public function isTransfer(): bool
    {
        return $this->category && $this->category->name === 'Transfer';
    }

    /**
     * Obtener la transacción relacionada (para transferencias)
     */
    public function relatedTransfer()
    {
        if (!$this->reference_id) {
            return null;
        }

        return static::where('reference_id', $this->reference_id)
            ->where('id', '!=', $this->id)
            ->first();
    }

    /**
     * Scope para transferencias
     */
    public function scopeTransfers($query)
    {
        return $query->whereHas('category', function ($q) {
            $q->where('name', 'Transfer');
        });
    }
}

