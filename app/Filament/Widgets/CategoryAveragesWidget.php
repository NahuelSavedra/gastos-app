<?php

namespace App\Filament\Widgets;

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;

class CategoryAveragesWidget extends Widget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    protected static string $view = 'filament.widgets.category-averages';

    public function getViewData(): array
    {
        $selectedMonth = $this->filters['month'] ?? now()->format('Y-m');
        $date = Carbon::createFromFormat('Y-m', $selectedMonth);
        $previousDate = $date->copy()->subMonth();

        $includedAccountIds = Account::where('include_in_totals', true)->pluck('id')->toArray();
        $excludedCategoryIds = Category::where('name', 'Transfer')->pluck('id')->toArray();

        // Verificar que existe el mes anterior
        $hasPreviousData = Transaction::whereIn('account_id', $includedAccountIds)
            ->whereNotIn('category_id', $excludedCategoryIds)
            ->whereMonth('date', $previousDate->month)
            ->whereYear('date', $previousDate->year)
            ->exists();

        if (! $hasPreviousData) {
            return [
                'categories' => [],
                'monthLabel' => ucfirst($date->translatedFormat('F Y')),
                'previousMonthLabel' => ucfirst($previousDate->translatedFormat('F Y')),
                'insufficientData' => true,
            ];
        }

        // Gastos del mes anterior
        $previousData = Transaction::select('categories.id as category_id', 'categories.name', DB::raw('SUM(transactions.amount) as total'))
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->whereIn('transactions.account_id', $includedAccountIds)
            ->where('categories.type', 'expense')
            ->whereNotIn('transactions.category_id', $excludedCategoryIds)
            ->whereMonth('transactions.date', $previousDate->month)
            ->whereYear('transactions.date', $previousDate->year)
            ->groupBy('categories.id', 'categories.name')
            ->get()
            ->keyBy('category_id');

        // Gastos del mes seleccionado
        $currentData = Transaction::select('categories.id as category_id', 'categories.name', DB::raw('SUM(transactions.amount) as total'))
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->whereIn('transactions.account_id', $includedAccountIds)
            ->where('categories.type', 'expense')
            ->whereNotIn('transactions.category_id', $excludedCategoryIds)
            ->whereMonth('transactions.date', $date->month)
            ->whereYear('transactions.date', $date->year)
            ->groupBy('categories.id', 'categories.name')
            ->get()
            ->keyBy('category_id');

        // Combinar datos y calcular métricas
        $categories = collect();

        // Obtener todas las categorías (del mes anterior y actual)
        $allCategoryIds = $previousData->keys()->merge($currentData->keys())->unique();

        foreach ($allCategoryIds as $categoryId) {
            $previous = $previousData->get($categoryId)?->total ?? 0;
            $current = $currentData->get($categoryId)?->total ?? 0;
            $name = $previousData->get($categoryId)?->name ?? $currentData->get($categoryId)?->name;

            $percentage = $previous > 0 ? ($current / $previous) * 100 : ($current > 0 ? 999 : 0);

            $status = 'neutral';
            $color = 'yellow';

            if ($current == 0 && $previous > 0) {
                $status = 'on_track';
                $color = 'green';
            } elseif ($percentage < 95) {
                $status = 'on_track';
                $color = 'green';
            } elseif ($percentage >= 110) {
                $status = 'over_budget';
                $color = 'red';
            }

            $categories->push([
                'name' => $name,
                'previous' => $previous,
                'current' => $current,
                'percentage' => $percentage == 999 ? null : round($percentage, 1),
                'status' => $status,
                'color' => $color,
            ]);
        }

        // Ordenar por mayor gasto actual y limitar a top 10
        $categories = $categories->sortByDesc('current')->take(10)->values();

        return [
            'categories' => $categories,
            'monthLabel' => ucfirst($date->translatedFormat('F Y')),
            'previousMonthLabel' => ucfirst($previousDate->translatedFormat('F Y')),
            'insufficientData' => false,
        ];
    }
}
