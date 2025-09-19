<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class ExpenseCategoriesWidget extends ChartWidget
{
    protected static ?string $heading = '📊 Gastos por Categoría (Este Mes)';
    protected static ?int $sort = 3; // ⭐ TERCER LUGAR
    protected int | string | array $columnSpan = [
        'md' => 2,
        'xl' => 1,
    ];
    protected static ?string $maxHeight = '300px'; // Altura controlada
    protected static ?string $pollingInterval = '2m'; // Actualiza cada 2 minutos

    protected function getData(): array
    {
        $expensesByCategory = Transaction::with('category')
            ->where('type', 'expense')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->get()
            ->groupBy('category.name')
            ->map(function ($transactions) {
                return $transactions->sum('amount');
            })
            ->sortDesc()
            ->take(6);

        return [
            'datasets' => [
                [
                    'data' => $expensesByCategory->values()->toArray(),
                    'backgroundColor' => [
                        '#FF6384', '#36A2EB', '#FFCE56',
                        '#4BC0C0', '#9966FF', '#FF9F40'
                    ],
                ],
            ],
            'labels' => $expensesByCategory->keys()->toArray(),
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
            'maintainAspectRatio' => false,
        ];
    }
}
