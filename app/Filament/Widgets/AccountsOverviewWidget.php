<?php

namespace App\Filament\Widgets;

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\Widget;

class AccountsOverviewWidget extends Widget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected static string $view = 'filament.widgets.accounts-overview';

    protected static ?string $pollingInterval = '30s';

    public function getViewData(): array
    {
        // Obtener el mes seleccionado del filtro
        $selectedMonth = $this->filters['month'] ?? now()->format('Y-m');
        $date = Carbon::createFromFormat('Y-m', $selectedMonth);
        $month = $date->month;
        $year = $date->year;

        $monthLabel = ucfirst($date->translatedFormat('F Y'));

        // Categoría de transferencias a excluir
        $excludedCategoryIds = Category::where('name', 'Transfer')->pluck('id')->toArray();

        $accounts = Account::all();
        $accountsData = [];

        foreach ($accounts as $account) {
            // Calcular balance actual (histórico completo)
            $income = Transaction::join('categories', 'transactions.category_id', '=', 'categories.id')
                ->where('transactions.account_id', $account->id)
                ->where('categories.type', 'income')
                ->sum('transactions.amount');

            $expense = Transaction::join('categories', 'transactions.category_id', '=', 'categories.id')
                ->where('transactions.account_id', $account->id)
                ->where('categories.type', 'expense')
                ->sum('transactions.amount');

            $currentBalance = $account->initial_balance + $income - $expense;

            // Balance del mes seleccionado (excluyendo transferencias)
            $monthIncome = Transaction::join('categories', 'transactions.category_id', '=', 'categories.id')
                ->where('transactions.account_id', $account->id)
                ->where('categories.type', 'income')
                ->whereNotIn('transactions.category_id', $excludedCategoryIds)
                ->whereMonth('transactions.date', $month)
                ->whereYear('transactions.date', $year)
                ->sum('transactions.amount');

            $monthExpense = Transaction::join('categories', 'transactions.category_id', '=', 'categories.id')
                ->where('transactions.account_id', $account->id)
                ->where('categories.type', 'expense')
                ->whereNotIn('transactions.category_id', $excludedCategoryIds)
                ->whereMonth('transactions.date', $month)
                ->whereYear('transactions.date', $year)
                ->sum('transactions.amount');

            $monthBalance = $monthIncome - $monthExpense;

            // Número de transacciones del mes seleccionado
            $transactionCount = Transaction::where('account_id', $account->id)
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->count();

            $accountsData[] = [
                'id' => $account->id,
                'name' => $account->name,
                'type' => $account->account_type ?? 'checking',
                'type_label' => $account->type_name ?? '🏦 Cuenta',
                'icon' => $account->account_icon ?? '🏦',
                'color' => $account->account_color ?? '#3B82F6',
                'initial_balance' => $account->initial_balance,
                'current_balance' => $currentBalance,
                'month_balance' => $monthBalance,
                'month_income' => $monthIncome,
                'month_expense' => $monthExpense,
                'transaction_count' => $transactionCount,
                'include_in_totals' => $account->include_in_totals ?? true,
            ];
        }

        return [
            'accounts' => $accountsData,
            'monthLabel' => $monthLabel,
        ];
    }
}
