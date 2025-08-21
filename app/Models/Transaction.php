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
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
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
}

