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
        $selectedMonth = $this->filters['month'] ?? now()->format('Y-m');
        $date = Carbon::createFromFormat('Y-m', $selectedMonth);
        $month = $date->month;
        $year = $date->year;
        $monthLabel = ucfirst($date->translatedFormat('F Y'));

        // Get transfer category IDs to exclude
        $excludedCategoryIds = Category::where('name', 'Transfer')->pluck('id')->toArray();
        $excludedCategoriesStr = implode(',', $excludedCategoryIds ?: [0]);

        // OPTIMIZATION: Single query with all aggregates
        $accounts = Account::query()
            ->select([
                'accounts.*',
                // Current balance (all time)
                \DB::raw('COALESCE(SUM(CASE WHEN categories.type = "income" THEN transactions.amount ELSE 0 END), 0) as total_income'),
                \DB::raw('COALESCE(SUM(CASE WHEN categories.type = "expense" THEN transactions.amount ELSE 0 END), 0) as total_expense'),
                // Month balance (excluding transfers)
                \DB::raw('COALESCE(SUM(CASE
                    WHEN categories.type = "income"
                    AND transactions.category_id NOT IN ('.$excludedCategoriesStr.')
                    AND strftime("%m", transactions.date) = "'.str_pad($month, 2, '0', STR_PAD_LEFT).'"
                    AND strftime("%Y", transactions.date) = "'.$year.'"
                    THEN transactions.amount ELSE 0 END), 0) as month_income'),
                \DB::raw('COALESCE(SUM(CASE
                    WHEN categories.type = "expense"
                    AND transactions.category_id NOT IN ('.$excludedCategoriesStr.')
                    AND strftime("%m", transactions.date) = "'.str_pad($month, 2, '0', STR_PAD_LEFT).'"
                    AND strftime("%Y", transactions.date) = "'.$year.'"
                    THEN transactions.amount ELSE 0 END), 0) as month_expense'),
                // Transaction count for month
                \DB::raw('COUNT(CASE
                    WHEN strftime("%m", transactions.date) = "'.str_pad($month, 2, '0', STR_PAD_LEFT).'"
                    AND strftime("%Y", transactions.date) = "'.$year.'"
                    THEN 1 END) as transaction_count'),
            ])
            ->leftJoin('transactions', 'accounts.id', '=', 'transactions.account_id')
            ->leftJoin('categories', 'transactions.category_id', '=', 'categories.id')
            ->groupBy('accounts.id', 'accounts.name', 'accounts.account_type', 'accounts.color',
                'accounts.icon', 'accounts.initial_balance', 'accounts.include_in_totals',
                'accounts.description', 'accounts.created_at', 'accounts.updated_at')
            ->get();

        $accountsData = $accounts->map(function ($account) {
            $currentBalance = $account->initial_balance + $account->total_income - $account->total_expense;
            $monthBalance = $account->month_income - $account->month_expense;

            return [
                'id' => $account->id,
                'name' => $account->name,
                'type' => $account->account_type ?? 'checking',
                'type_label' => $account->type_name ?? '🏦 Cuenta',
                'icon' => $account->account_icon ?? '🏦',
                'color' => $account->account_color ?? '#3B82F6',
                'initial_balance' => $account->initial_balance,
                'current_balance' => $currentBalance,
                'month_balance' => $monthBalance,
                'month_income' => $account->month_income,
                'month_expense' => $account->month_expense,
                'transaction_count' => $account->transaction_count,
                'include_in_totals' => $account->include_in_totals ?? true,
            ];
        })->toArray();

        return [
            'accounts' => $accountsData,
            'monthLabel' => $monthLabel,
        ];
    }
}
