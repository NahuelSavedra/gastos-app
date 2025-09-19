<?php

namespace App\Filament\Widgets;

use App\Models\Account;
use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BalanceOverview extends BaseWidget
{
    protected static ?int $sort = 1; // ⭐ PRIMER LUGAR
    protected int | string | array $columnSpan = 'full'; // Ocupa toda la fila
    protected static ?string $pollingInterval = '30s'; // Actualiza cada 30 segundos

    protected function getStats(): array
    {
        $totalIncome = Transaction::where('type', 'income')->sum('amount');
        $totalExpense = Transaction::where('type', 'expense')->sum('amount');
        $balance = $totalIncome - $totalExpense;

        $currentMonthIncome = Transaction::where('type', 'income')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');

        $currentMonthExpense = Transaction::where('type', 'expense')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');

        return [
            Stat::make('💰 Ingresos Totales', '$' . number_format($totalIncome, 2))
                ->description('Este mes: $' . number_format($currentMonthIncome, 2))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->chart([7, 2, 10, 3, 15, 4, 17]),

            Stat::make('💸 Gastos Totales', '$' . number_format($totalExpense, 2))
                ->description('Este mes: $' . number_format($currentMonthExpense, 2))
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger')
                ->chart([17, 16, 14, 15, 14, 13, 12]),

            Stat::make('💳 Saldo General', '$' . number_format($balance, 2))
                ->description($balance >= 0 ? '✅ Situación favorable' : '⚠️ Revisar gastos')
                ->color($balance >= 0 ? 'success' : 'warning')
                ->chart($balance >= 0 ? [1, 3, 5, 7, 9, 11, 13] : [13, 11, 9, 7, 5, 3, 1]),
        ];
    }

}
