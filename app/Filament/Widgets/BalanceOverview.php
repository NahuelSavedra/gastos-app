<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BalanceOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $totalIncome = Transaction::where('type', 'income')->sum('amount');
        $totalExpense = Transaction::where('type', 'expense')->sum('amount');
        $balance = $totalIncome - $totalExpense;

        return [
            Stat::make('Ingresos', '$' . number_format($totalIncome, 2))
                ->description('Total de ingresos')
                ->color('success'),

            Stat::make('Gastos', '$' . number_format($totalExpense, 2))
                ->description('Total de gastos')
                ->color('danger'),

            Stat::make('Saldo Disponible', '$' . number_format($balance, 2))
                ->description('Ingresos - Gastos')
                ->color($balance >= 0 ? 'primary' : 'warning'),
        ];
    }
}
