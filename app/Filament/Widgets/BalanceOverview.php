<?php

namespace App\Filament\Widgets;

use App\Models\Account;
use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BalanceOverview extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        // Obtener el mes seleccionado del filtro
        $selectedMonth = $this->filters['month'] ?? now()->format('Y-m');
        $date = Carbon::createFromFormat('Y-m', $selectedMonth);
        $month = $date->month;
        $year = $date->year;

        $isCurrentMonth = $date->isSameMonth(now());
        $monthLabel = ucfirst($date->translatedFormat('F Y'));

        $includedAccountIds = Account::where('include_in_totals', true)->pluck('id')->toArray();
        $excludedCategoryIds = $this->getExcludedCategories();

        // INGRESOS del mes seleccionado
        $totalIncome = Transaction::join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('categories.type', 'income')
            ->whereNotIn('transactions.category_id', $excludedCategoryIds)
            ->whereIn('transactions.account_id', $includedAccountIds)
            ->whereMonth('transactions.date', $month)
            ->whereYear('transactions.date', $year)
            ->sum('transactions.amount');

        // GASTOS del mes seleccionado
        $totalExpense = Transaction::join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('categories.type', 'expense')
            ->whereNotIn('transactions.category_id', $excludedCategoryIds)
            ->whereIn('transactions.account_id', $includedAccountIds)
            ->whereMonth('transactions.date', $month)
            ->whereYear('transactions.date', $year)
            ->sum('transactions.amount');

        $balance = $totalIncome - $totalExpense;

        // Calcular tendencias de los últimos 7 días del mes seleccionado
        $incomeChart = $this->getChartData('income', $excludedCategoryIds, $includedAccountIds, $date);
        $expenseChart = $this->getChartData('expense', $excludedCategoryIds, $includedAccountIds, $date);

        // Calcular mes anterior para comparación
        $previousDate = $date->copy()->subMonth();

        $previousMonthIncome = Transaction::join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('categories.type', 'income')
            ->whereNotIn('transactions.category_id', $excludedCategoryIds)
            ->whereIn('transactions.account_id', $includedAccountIds)
            ->whereMonth('transactions.date', $previousDate->month)
            ->whereYear('transactions.date', $previousDate->year)
            ->sum('transactions.amount');

        $previousMonthExpense = Transaction::join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('categories.type', 'expense')
            ->whereNotIn('transactions.category_id', $excludedCategoryIds)
            ->whereIn('transactions.account_id', $includedAccountIds)
            ->whereMonth('transactions.date', $previousDate->month)
            ->whereYear('transactions.date', $previousDate->year)
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
            Stat::make('💰 Ingresos', '$'.number_format($totalIncome, 2))
                ->description($this->getChangeDescription($incomeChange, 'income'))
                ->descriptionIcon($incomeChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color('success')
                ->chart($incomeChart),

            Stat::make('💸 Gastos', '$'.number_format($totalExpense, 2))
                ->description($this->getChangeDescription($expenseChange, 'expense'))
                ->descriptionIcon($expenseChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color('danger')
                ->chart($expenseChart),

            Stat::make('💳 Balance', '$'.number_format($balance, 2))
                ->description($this->getBalanceDescription($balance, $totalIncome, $savingsRate))
                ->descriptionIcon($balance >= 0 ? 'heroicon-m-check-circle' : 'heroicon-m-exclamation-triangle')
                ->color($balance >= 0 ? 'success' : 'warning')
                ->chart($this->getBalanceChart($incomeChart, $expenseChart)),

            Stat::make('📊 Cuentas Incluidas', count($includedAccountIds).' de '.Account::count())
                ->description($excludedInfo['message'])
                ->descriptionIcon('heroicon-m-information-circle')
                ->color('info'),
        ];
    }

    private function getExcludedCategories(): array
    {
        return \App\Models\Category::where('name', 'Transfer')
            ->pluck('id')
            ->toArray();
    }

    private function getExcludedAccountsInfo(): array
    {
        $excludedAccounts = Account::where('include_in_totals', false)->get();
        $excludedCount = $excludedAccounts->count();

        if ($excludedCount === 0) {
            return [
                'message' => '✅ Todas las cuentas incluidas',
            ];
        }

        $excludedNames = $excludedAccounts->pluck('name')->implode(', ');

        return [
            'message' => "🔍 Excluidas: {$excludedNames}",
        ];
    }

    private function getChartData(string $type, array $excludedCategories, array $includedAccounts, Carbon $date): array
    {
        $endOfMonth = $date->copy()->endOfMonth();

        // Si es el mes actual, mostrar hasta hoy
        if ($date->isSameMonth(now())) {
            $endOfMonth = now();
        }

        // Mostrar últimos 7 días del período
        $days = min(7, $endOfMonth->day);
        $startDay = $endOfMonth->copy()->subDays($days - 1);

        // OPTIMIZATION: Single query with GROUP BY date
        $results = Transaction::query()
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->select([
                \DB::raw('DATE(transactions.date) as transaction_date'),
                \DB::raw('SUM(transactions.amount) as total'),
            ])
            ->where('categories.type', $type)
            ->whereNotIn('transactions.category_id', $excludedCategories)
            ->whereIn('transactions.account_id', $includedAccounts)
            ->whereBetween('transactions.date', [$startDay->toDateString(), $endOfMonth->toDateString()])
            ->groupBy('transaction_date')
            ->orderBy('transaction_date')
            ->pluck('total', 'transaction_date');

        // Fill in missing dates with 0
        $data = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $dayDate = $endOfMonth->copy()->subDays($i)->toDateString();
            $data[] = (float) ($results[$dayDate] ?? 0);
        }

        return $data;
    }

    private function getChangeDescription(float $change, string $type): string
    {
        $absChange = abs($change);
        $formattedChange = number_format($absChange, 1);

        if ($change == 0) {
            return '➡️ Sin cambios vs mes anterior';
        }

        if ($type === 'income') {
            return $change > 0
                ? "↗️ +{$formattedChange}% vs mes anterior"
                : "↘️ -{$formattedChange}% vs mes anterior";
        }

        // expense
        return $change > 0
            ? "⚠️ +{$formattedChange}% más gastos"
            : "✅ -{$formattedChange}% menos gastos";
    }

    private function getBalanceDescription(float $balance, float $income, float $savingsRate): string
    {
        if ($balance >= 0) {
            if ($income > 0) {
                return '✅ Ahorraste '.number_format($savingsRate, 1).'% de tus ingresos';
            }

            return '✅ Balance positivo';
        }

        $deficit = abs($balance);

        return '⚠️ Déficit de $'.number_format($deficit, 2);
    }

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
