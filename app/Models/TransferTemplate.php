<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransferTemplate extends Model
{
    protected $fillable = [
        'name',
        'from_account_id',
        'to_account_id',
        'default_amount',
        'icon',
        'color',
        'order',
        'is_active',
    ];

    protected $casts = [
        'default_amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function fromAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'from_account_id');
    }

    public function toAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'to_account_id');
    }

    // Scope para templates activos ordenados
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('order');
    }
}
