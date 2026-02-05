<?php

namespace App\Services\Import\CategoryMatcher;

use App\Models\Category;
use App\Models\ImportCategoryRule;
use Illuminate\Support\Collection;

class CategoryMatcherService
{
    private ?Collection $rules = null;

    /**
     * Find matching category for transaction data
     *
     * @param  array  $data  Normalized transaction data
     * @param  string|null  $source  Parser source identifier
     * @param  bool  $isExpense  Whether the transaction is an expense
     * @return Category|null Matched category or null
     */
    public function findCategory(array $data, ?string $source, bool $isExpense): ?Category
    {
        $rules = $this->getRules($source);

        foreach ($rules as $rule) {
            if ($rule->matches($data)) {
                $category = $rule->category;

                // Verify category type matches transaction type
                $expectedType = $isExpense ? 'expense' : 'income';
                if ($category->type === $expectedType) {
                    return $category;
                }
            }
        }

        return null;
    }

    /**
     * Get active rules for a source, ordered by priority
     */
    private function getRules(?string $source): Collection
    {
        if ($this->rules === null) {
            $this->rules = ImportCategoryRule::active()
                ->forSource($source)
                ->ordered()
                ->with('category')
                ->get();
        }

        return $this->rules;
    }

    /**
     * Clear cached rules (useful after rule changes)
     */
    public function clearCache(): void
    {
        $this->rules = null;
    }
}
