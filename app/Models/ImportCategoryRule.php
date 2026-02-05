<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportCategoryRule extends Model
{
    protected $fillable = [
        'name',
        'source',
        'field',
        'operator',
        'value',
        'category_id',
        'priority',
        'is_active',
    ];

    protected $casts = [
        'priority' => 'integer',
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForSource($query, ?string $source)
    {
        return $query->where(function ($q) use ($source) {
            $q->whereNull('source')
                ->orWhere('source', $source);
        });
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('priority', 'desc');
    }

    public function matches(array $data): bool
    {
        $fieldValue = $data[$this->field] ?? null;

        if ($fieldValue === null) {
            return false;
        }

        $fieldValue = strtolower((string) $fieldValue);
        $ruleValue = strtolower($this->value);

        return match ($this->operator) {
            'equals' => $fieldValue === $ruleValue,
            'contains' => str_contains($fieldValue, $ruleValue),
            'starts_with' => str_starts_with($fieldValue, $ruleValue),
            'ends_with' => str_ends_with($fieldValue, $ruleValue),
            'not_equals' => $fieldValue !== $ruleValue,
            default => false,
        };
    }

    public static function getOperators(): array
    {
        return [
            'equals' => 'Igual a',
            'contains' => 'Contiene',
            'starts_with' => 'Empieza con',
            'ends_with' => 'Termina con',
            'not_equals' => 'No es igual a',
        ];
    }

    public static function getAvailableFields(): array
    {
        return [
            'transaction_type' => 'Tipo de Transacción',
            'recipient' => 'Destinatario/Origen',
            'title' => 'Título',
            'description' => 'Descripción completa',
        ];
    }
}
