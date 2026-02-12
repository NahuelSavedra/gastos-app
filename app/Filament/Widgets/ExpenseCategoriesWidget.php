<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ExpenseCategoriesWidget extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $maxHeight = '300px';

    public function getHeading(): string
    {
        $selectedMonth = $this->filters['month'] ?? now()->format('Y-m');
        $date = Carbon::createFromFormat('Y-m', $selectedMonth);
        $monthLabel = ucfirst($date->translatedFormat('F Y'));

        return "📊 Gastos por Categoría - {$monthLabel}";
    }

    protected function getData(): array
    {
        $selectedMonth = $this->filters['month'] ?? now()->format('Y-m');
        $date = Carbon::createFromFormat('Y-m', $selectedMonth);

        // OPTIMIZATION: Cache chart data for 5 minutes
        $cacheKey = "expense_categories_chart_{$selectedMonth}";

        $data = Cache::remember($cacheKey, 300, function () use ($date) {
            return Transaction::join('categories', 'transactions.category_id', '=', 'categories.id')
                ->where('categories.type', 'expense')
                ->where('categories.name', '!=', 'Transfer')
                ->whereMonth('transactions.date', $date->month)
                ->whereYear('transactions.date', $date->year)
                ->select('categories.name', DB::raw('SUM(transactions.amount) as total'))
                ->groupBy('categories.id', 'categories.name')
                ->orderByDesc('total')
                ->limit(10)
                ->get();
        });

        return [
            'datasets' => [
                [
                    'label' => 'Gastos',
                    'data' => $data->pluck('total')->toArray(),
                    'backgroundColor' => [
                        '#ef4444', '#f59e0b', '#10b981', '#3b82f6', '#6366f1',
                        '#8b5cf6', '#ec4899', '#14b8a6', '#f97316', '#84cc16',
                    ],
                ],
            ],
            'labels' => $data->pluck('name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
        ];
    }
}
