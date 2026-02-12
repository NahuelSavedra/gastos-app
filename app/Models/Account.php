<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'account_type',
        'color',
        'icon',
        'description',
        'initial_balance',
        'include_in_totals',
    ];

    protected $casts = [
        'initial_balance' => 'decimal:2',
        'include_in_totals' => 'boolean',
    ];

    /**
     * Tipos de cuenta disponibles
     */
    public const ACCOUNT_TYPES = [
        'checking' => [
            'label' => '🏦 Cuenta Corriente',
            'icon' => '🏦',
            'color' => '#3B82F6', // Azul
            'description' => 'Cuenta bancaria para uso diario',
        ],
        'savings' => [
            'label' => '💰 Cuenta de Ahorro',
            'icon' => '💰',
            'color' => '#10B981', // Verde
            'description' => 'Cuenta para guardar dinero',
        ],
        'cash' => [
            'label' => '💵 Efectivo',
            'icon' => '💵',
            'color' => '#F59E0B', // Amarillo/Naranja
            'description' => 'Dinero en efectivo',
        ],
        'credit_card' => [
            'label' => '💳 Tarjeta de Crédito',
            'icon' => '💳',
            'color' => '#EF4444', // Rojo
            'description' => 'Tarjeta de crédito',
        ],
        'investment' => [
            'label' => '📈 Inversión',
            'icon' => '📈',
            'color' => '#8B5CF6', // Púrpura
            'description' => 'Cuenta de inversiones',
        ],
        'wallet' => [
            'label' => '👛 Billetera Digital',
            'icon' => '👛',
            'color' => '#06B6D4', // Cyan
            'description' => 'Billetera virtual (MercadoPago, etc)',
        ],
    ];

    /**
     * Relación con transacciones
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Obtener el balance actual calculado (con caché)
     */
    public function getCurrentBalanceAttribute(): float
    {
        $cacheKey = "account_balance_{$this->id}";

        return Cache::remember($cacheKey, 300, function () { // 5 minute cache
            $income = $this->transactions()
                ->join('categories', 'transactions.category_id', '=', 'categories.id')
                ->where('categories.type', 'income')
                ->sum('transactions.amount');

            $expense = $this->transactions()
                ->join('categories', 'transactions.category_id', '=', 'categories.id')
                ->where('categories.type', 'expense')
                ->sum('transactions.amount');

            return $this->initial_balance + $income - $expense;
        });
    }

    /**
     * Clear balance cache for this account
     */
    public function clearBalanceCache(): void
    {
        Cache::forget("account_balance_{$this->id}");
    }

    /**
     * Obtener configuración del tipo de cuenta
     */
    public function getTypeConfigAttribute(): array
    {
        return self::ACCOUNT_TYPES[$this->account_type] ?? self::ACCOUNT_TYPES['checking'];
    }

    /**
     * Obtener el icono (personalizado o del tipo)
     */
    public function getAccountIconAttribute(): string
    {
        return $this->icon ?? $this->type_config['icon'];
    }

    /**
     * Obtener el color (personalizado o del tipo)
     */
    public function getAccountColorAttribute(): string
    {
        return $this->color ?? $this->type_config['color'];
    }

    /**
     * Obtener label del tipo
     */
    public function getTypeNameAttribute(): string
    {
        return $this->type_config['label'];
    }

    /**
     * Scope para cuentas que se incluyen en totales
     */
    public function scopeIncludedInTotals($query)
    {
        return $query->where('include_in_totals', true);
    }

    /**
     * Scope para filtrar por tipo
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('account_type', $type);
    }
}
