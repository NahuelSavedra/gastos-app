<?php

namespace App\Filament\Widgets;

use App\Models\Account;
use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BalanceOverview extends BaseWidget
{
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $includedAccountIds = Account::where('include_in_totals', true)->pluck('id')->toArray();

        // Categorías a excluir (Transferencias)
        $excludedCategoryIds = $this->getExcludedCategories();

        // Obtener totales del mes actual
        $currentMonth = now()->month;
        $currentYear = now()->year;

        // INGRESOS del mes actual
        $totalIncome = Transaction::join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('categories.type', 'income')
            ->whereNotIn('transactions.category_id', $excludedCategoryIds)
            ->whereIn('transactions.account_id', $includedAccountIds)
            ->whereMonth('transactions.date', $currentMonth)
            ->whereYear('transactions.date', $currentYear)
            ->sum('transactions.amount');

        // GASTOS del mes actual
        $totalExpense = Transaction::join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('categories.type', 'expense')
            ->whereNotIn('transactions.category_id', $excludedCategoryIds)
            ->whereIn('transactions.account_id', $includedAccountIds)
            ->whereMonth('transactions.date', $currentMonth)
            ->whereYear('transactions.date', $currentYear)
            ->sum('transactions.amount');

        $balance = $totalIncome - $totalExpense;

        // Calcular tendencias de los últimos 7 días
        $incomeChart = $this->getLast7DaysData('income', $excludedCategoryIds, $includedAccountIds);
        $expenseChart = $this->getLast7DaysData('expense', $excludedCategoryIds, $includedAccountIds);

        // Calcular mes anterior para comparación
        $previousMonth = now()->subMonth();

        $previousMonthIncome = Transaction::join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('categories.type', 'income')
            ->whereNotIn('transactions.category_id', $excludedCategoryIds)
            ->whereIn('transactions.account_id', $includedAccountIds)
            ->whereMonth('transactions.date', $previousMonth->month)
            ->whereYear('transactions.date', $previousMonth->year)
            ->sum('transactions.amount');

        $previousMonthExpense = Transaction::join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('categories.type', 'expense')
            ->whereNotIn('transactions.category_id', $excludedCategoryIds)
            ->whereIn('transactions.account_id', $includedAccountIds)
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

        // Calcular porcentaje de ahorro
        $savingsRate = $totalIncome > 0 ? (($balance / $totalIncome) * 100) : 0;

        // Obtener información de cuentas excluidas
        $excludedInfo = $this->getExcludedAccountsInfo();

        return [
            Stat::make('💰 Ingresos del Mes', '$' . number_format($totalIncome, 2))
                ->description($this->getChangeDescription($incomeChange, 'income'))
                ->descriptionIcon($incomeChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color('success')
                ->chart($incomeChart)
                ->extraAttributes([
                    'class' => 'relative',
                ]),

            Stat::make('💸 Gastos del Mes', '$' . number_format($totalExpense, 2))
                ->description($this->getChangeDescription($expenseChange, 'expense'))
                ->descriptionIcon($expenseChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color('danger')
                ->chart($expenseChart),

            Stat::make('💳 Balance del Mes', '$' . number_format($balance, 2))
                ->description($this->getBalanceDescription($balance, $totalIncome, $totalExpense, $savingsRate))
                ->descriptionIcon($balance >= 0 ? 'heroicon-m-check-circle' : 'heroicon-m-exclamation-triangle')
                ->color($balance >= 0 ? 'success' : 'warning')
                ->chart($this->getBalanceChart($incomeChart, $expenseChart)),

            // ✅ NUEVO: Card de información sobre cuentas excluidas
            Stat::make('📊 Cuentas Incluidas', count($includedAccountIds) . ' de ' . Account::count())
                ->description($excludedInfo['message'])
                ->descriptionIcon('heroicon-m-information-circle')
                ->color('info')
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                    'title' => $excludedInfo['tooltip'],
                ]),
        ];
    }

    /**
     * Obtener categorías a excluir (Transferencias)
     */
    private function getExcludedCategories(): array
    {
        return \App\Models\Category::where('name', 'Transfer')
            ->pluck('id')
            ->toArray();
    }

    /**
     * Obtener información sobre cuentas excluidas
     */
    private function getExcludedAccountsInfo(): array
    {
        $excludedAccounts = Account::where('include_in_totals', false)->get();
        $excludedCount = $excludedAccounts->count();

        if ($excludedCount === 0) {
            return [
                'message' => '✅ Todas las cuentas incluidas',
                'tooltip' => 'Todos tus movimientos están siendo contabilizados',
            ];
        }

        $excludedNames = $excludedAccounts->pluck('name')->toArray();
        $namesList = implode(', ', $excludedNames);

        return [
            'message' => "🔍 Excluidas: {$namesList}",
            'tooltip' => "Estas cuentas no afectan los totales: {$namesList}",
        ];
    }

    /**
     * Obtener datos de los últimos 7 días para el gráfico
     */
    private function getLast7DaysData(string $type, array $excludedCategories, array $includedAccounts): array
    {
        $data = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();

            $amount = Transaction::join('categories', 'transactions.category_id', '=', 'categories.id')
                ->where('categories.type', $type)
                ->whereNotIn('transactions.category_id', $excludedCategories)
                ->whereIn('transactions.account_id', $includedAccounts)
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

        if ($change == 0) {
            return "➡️ Sin cambios vs mes anterior";
        }

        if ($type === 'income') {
            if ($change > 0) {
                return "↗️ +{$formattedChange}% vs mes anterior";
            } else {
                return "↘️ -{$formattedChange}% vs mes anterior";
            }
        } else { // expense
            if ($change > 0) {
                return "⚠️ +{$formattedChange}% más gastos vs mes anterior";
            } else {
                return "✅ -{$formattedChange}% menos gastos vs mes anterior";
            }
        }
    }

    /**
     * Generar descripción del saldo con tasa de ahorro
     */
    private function getBalanceDescription(float $balance, float $income, float $expense, float $savingsRate): string
    {
        if ($balance >= 0) {
            if ($income > 0) {
                return "✅ Ahorraste " . number_format($savingsRate, 1) . "% de tus ingresos";
            } else {
                return "✅ Balance positivo";
            }
        } else {
            $deficit = abs($balance);
            $deficitPercent = $income > 0 ? ($deficit / $income) * 100 : 0;
            return "⚠️ Déficit de $" . number_format($deficit, 2) . " (" . number_format($deficitPercent, 1) . "% de ingresos)";
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
