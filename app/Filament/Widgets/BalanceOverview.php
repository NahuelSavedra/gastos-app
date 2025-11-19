<?php

namespace App\Filament\Widgets;

use App\Models\Account;
use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class BalanceOverview extends BaseWidget
{
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        // IDs de categorías y cuentas a excluir (ajusta según tus necesidades)
        $excludedCategoryIds = [4]; // Transferencias u otras
        $excludedAccountIds = [3, 5]; // Cuentas que no quieres contar

        // Obtener totales del mes actual usando JOIN con categories
        $currentMonth = now()->month;
        $currentYear = now()->year;

        // INGRESOS del mes actual
        $totalIncome = Transaction::join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('categories.type', 'income')
            ->whereNotIn('transactions.category_id', $excludedCategoryIds)
            ->whereNotIn('transactions.account_id', $excludedAccountIds)
            ->whereMonth('transactions.date', $currentMonth)
            ->whereYear('transactions.date', $currentYear)
            ->sum('transactions.amount');

        // GASTOS del mes actual
        $totalExpense = Transaction::join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('categories.type', 'expense')
            ->whereNotIn('transactions.category_id', $excludedCategoryIds)
            ->whereNotIn('transactions.account_id', $excludedAccountIds)
            ->whereMonth('transactions.date', $currentMonth)
            ->whereYear('transactions.date', $currentYear)
            ->sum('transactions.amount');

        $balance = $totalIncome - $totalExpense;

        // Calcular tendencias de los últimos 7 días
        $incomeChart = $this->getLast7DaysData('income', $excludedCategoryIds, $excludedAccountIds);
        $expenseChart = $this->getLast7DaysData('expense', $excludedCategoryIds, $excludedAccountIds);

        // Calcular mes anterior para comparación
        $previousMonth = now()->subMonth();

        $previousMonthIncome = Transaction::join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('categories.type', 'income')
            ->whereNotIn('transactions.category_id', $excludedCategoryIds)
            ->whereNotIn('transactions.account_id', $excludedAccountIds)
            ->whereMonth('transactions.date', $previousMonth->month)
            ->whereYear('transactions.date', $previousMonth->year)
            ->sum('transactions.amount');

        $previousMonthExpense = Transaction::join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('categories.type', 'expense')
            ->whereNotIn('transactions.category_id', $excludedCategoryIds)
            ->whereNotIn('transactions.account_id', $excludedAccountIds)
            ->whereMonth('transactions.date', $previousMonth->month)
            ->whereYear('transactions.date', $previousMonth->year)
            ->sum('transactions.amount');

        // Calcular variaciones porcentuales
        $incomeChange = $previousMonthIncome > 0
            ? (($totalIncome - $previousMonthIncome) / $previousMonthIncome) * 100
            : 0;

        $expenseChange = $previousMonthExpense > 0
            ? (($totalExpense - $previousMonthExpense) / $previousMonthExpense) * 100
            : 0;

        return [
            Stat::make('💰 Ingresos Totales', '$' . number_format($totalIncome, 2))
                ->description($this->getChangeDescription($incomeChange, 'income'))
                ->descriptionIcon($incomeChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color('success')
                ->chart($incomeChart),

            Stat::make('💸 Gastos Totales', '$' . number_format($totalExpense, 2))
                ->description($this->getChangeDescription($expenseChange, 'expense'))
                ->descriptionIcon($expenseChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color('danger')
                ->chart($expenseChart),

            Stat::make('💳 Saldo del Mes', '$' . number_format($balance, 2))
                ->description($this->getBalanceDescription($balance, $totalIncome, $totalExpense))
                ->descriptionIcon($balance >= 0 ? 'heroicon-m-check-circle' : 'heroicon-m-exclamation-triangle')
                ->color($balance >= 0 ? 'success' : 'warning')
                ->chart($this->getBalanceChart($incomeChart, $expenseChart)),
        ];
    }

    /**
     * Obtener datos de los últimos 7 días para el gráfico
     */
    private function getLast7DaysData(string $type, array $excludedCategories, array $excludedAccounts): array
    {
        $data = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();

            $amount = Transaction::join('categories', 'transactions.category_id', '=', 'categories.id')
                ->where('categories.type', $type)
                ->whereNotIn('transactions.category_id', $excludedCategories)
                ->whereNotIn('transactions.account_id', $excludedAccounts)
                ->whereDate('transactions.date', $date)
                ->sum('transactions.amount');

            $data[] = (float) $amount;
        }

        return $data;
    }

    /**
     * Generar descripción del cambio porcentual
     */
    private function getChangeDescription(float $change, string $type): string
    {
        $absChange = abs($change);
        $formattedChange = number_format($absChange, 1);

        if ($type === 'income') {
            if ($change > 0) {
                return "↗️ +{$formattedChange}% vs mes anterior";
            } elseif ($change < 0) {
                return "↘️ -{$formattedChange}% vs mes anterior";
            }
        } else {
            if ($change > 0) {
                return "⚠️ +{$formattedChange}% vs mes anterior";
            } elseif ($change < 0) {
                return "✅ -{$formattedChange}% vs mes anterior";
            }
        }

        return "➡️ Sin cambios vs mes anterior";
    }

    /**
     * Generar descripción del saldo
     */
    private function getBalanceDescription(float $balance, float $income, float $expense): string
    {
        if ($balance >= 0) {
            $percentage = $income > 0 ? ($balance / $income) * 100 : 0;
            return "✅ Ahorro: " . number_format($percentage, 1) . "% de tus ingresos";
        } else {
            $deficit = abs($balance);
            return "⚠️ Déficit de $" . number_format($deficit, 2);
        }
    }

    /**
     * Generar gráfico de balance (diferencia entre ingresos y gastos)
     */
    private function getBalanceChart(array $incomeData, array $expenseData): array
    {
        $balanceData = [];

        foreach ($incomeData as $index => $income) {
            $expense = $expenseData[$index] ?? 0;
            $balanceData[] = $income - $expense;
        }

        return $balanceData;
    }
}
