<?php

namespace App\Filament\Widgets;

use App\Models\Account;
use App\Models\Transaction;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;

class AccountsOverviewWidget extends Widget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';
    protected static string $view = 'filament.widgets.accounts-overview';
    protected static ?string $pollingInterval = '30s';

    public function getViewData(): array
    {
        $accounts = Account::all();
        $accountsData = [];

        foreach ($accounts as $account) {
            // Calcular balance actual
            $income = Transaction::join('categories', 'transactions.category_id', '=', 'categories.id')
                ->where('transactions.account_id', $account->id)
                ->where('categories.type', 'income')
                ->sum('transactions.amount');

            $expense = Transaction::join('categories', 'transactions.category_id', '=', 'categories.id')
                ->where('transactions.account_id', $account->id)
                ->where('categories.type', 'expense')
                ->sum('transactions.amount');

            $currentBalance = $account->initial_balance + $income - $expense;

            // Balance del mes (excluyendo transferencias)
            $excludedCategoryIds = [4]; // Categoría de transferencias

            $monthIncome = Transaction::join('categories', 'transactions.category_id', '=', 'categories.id')
                ->where('transactions.account_id', $account->id)
                ->where('categories.type', 'income')
                ->whereNotIn('transactions.category_id', $excludedCategoryIds)
                ->whereMonth('transactions.date', now()->month)
                ->whereYear('transactions.date', now()->year)
                ->sum('transactions.amount');

            $monthExpense = Transaction::join('categories', 'transactions.category_id', '=', 'categories.id')
                ->where('transactions.account_id', $account->id)
                ->where('categories.type', 'expense')
                ->whereNotIn('transactions.category_id', $excludedCategoryIds)
                ->whereMonth('transactions.date', now()->month)
                ->whereYear('transactions.date', now()->year)
                ->sum('transactions.amount');

            $monthBalance = $monthIncome - $monthExpense;

            // Número de transacciones del mes
            $transactionCount = Transaction::where('account_id', $account->id)
                ->whereMonth('date', now()->month)
                ->whereYear('date', now()->year)
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
        ];
    }
}
