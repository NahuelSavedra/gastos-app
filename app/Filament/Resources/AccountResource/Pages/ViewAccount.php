<?php

namespace App\Filament\Resources\AccountResource\Pages;

use App\Filament\Resources\AccountResource;
use App\Models\Transaction;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\DB;

class ViewAccount extends ViewRecord
{
    protected static string $resource = AccountResource::class;

    protected static string $view = 'filament.resources.account-resource.pages.view-account';

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('✏️ Editar Cuenta'),
            Actions\Action::make('new_transaction')
                ->label('➕ Nueva Transacción')
                ->icon('heroicon-o-plus-circle')
                ->color('success')
                ->url(fn () => route('filament.app.resources.transactions.create', [
                    'account_id' => $this->record->id
                ])),
        ];
    }

    /**
     * Obtener datos para la vista
     */
    public function getViewData(): array
    {
        $account = $this->record;
        $excludedCategoryIds = [4]; // Excluir Transferencias del cálculo

        return [
            'account' => $account,
            'currentBalance' => $this->getCurrentBalance($account),
            'monthBalance' => $this->getMonthBalance($account, $excludedCategoryIds),
            'monthStats' => $this->getMonthStats($account, $excludedCategoryIds),
            'last7Days' => $this->getLast7DaysData($account, $excludedCategoryIds),
            'categoryBreakdown' => $this->getCategoryBreakdown($account),
            'recentTransactions' => $this->getRecentTransactions($account),
            'monthlyTrend' => $this->getMonthlyTrend($account, $excludedCategoryIds),
            'topCategories' => $this->getTopCategories($account),
            'transfersSummary' => $this->getTransfersSummary($account),
        ];
    }

    /**
     * Balance actual de la cuenta (suma de todas las transacciones)
     */
    private function getCurrentBalance($account): float
    {
        $income = Transaction::join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('transactions.account_id', $account->id)
            ->where('categories.type', 'income')
            ->sum('transactions.amount');

        $expense = Transaction::join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('transactions.account_id', $account->id)
            ->where('categories.type', 'expense')
            ->sum('transactions.amount');

        return $account->initial_balance + $income - $expense;
    }

    /**
     * Balance del mes actual (excluyendo transferencias)
     */
    private function getMonthBalance($account, array $excludedCategories): float
    {
        $income = Transaction::join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('transactions.account_id', $account->id)
            ->where('categories.type', 'income')
            ->whereNotIn('transactions.category_id', $excludedCategories)
            ->whereMonth('transactions.date', now()->month)
            ->whereYear('transactions.date', now()->year)
            ->sum('transactions.amount');

        $expense = Transaction::join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('transactions.account_id', $account->id)
            ->where('categories.type', 'expense')
            ->whereNotIn('transactions.category_id', $excludedCategories)
            ->whereMonth('transactions.date', now()->month)
            ->whereYear('transactions.date', now()->year)
            ->sum('transactions.amount');

        return $income - $expense;
    }

    /**
     * Estadísticas del mes actual
     */
    private function getMonthStats($account, array $excludedCategories): array
    {
        $currentMonth = now();
        $previousMonth = now()->subMonth();

        // Mes actual
        $currentIncome = Transaction::join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('transactions.account_id', $account->id)
            ->where('categories.type', 'income')
            ->whereNotIn('transactions.category_id', $excludedCategories)
            ->whereMonth('transactions.date', $currentMonth->month)
            ->whereYear('transactions.date', $currentMonth->year)
            ->sum('transactions.amount');

        $currentExpense = Transaction::join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('transactions.account_id', $account->id)
            ->where('categories.type', 'expense')
            ->whereNotIn('transactions.category_id', $excludedCategories)
            ->whereMonth('transactions.date', $currentMonth->month)
            ->whereYear('transactions.date', $currentMonth->year)
            ->sum('transactions.amount');

        // Mes anterior
        $previousIncome = Transaction::join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('transactions.account_id', $account->id)
            ->where('categories.type', 'income')
            ->whereNotIn('transactions.category_id', $excludedCategories)
            ->whereMonth('transactions.date', $previousMonth->month)
            ->whereYear('transactions.date', $previousMonth->year)
            ->sum('transactions.amount');

        $previousExpense = Transaction::join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('transactions.account_id', $account->id)
            ->where('categories.type', 'expense')
            ->whereNotIn('transactions.category_id', $excludedCategories)
            ->whereMonth('transactions.date', $previousMonth->month)
            ->whereYear('transactions.date', $previousMonth->year)
            ->sum('transactions.amount');

        // Calcular variaciones
        $incomeChange = $previousIncome > 0
            ? (($currentIncome - $previousIncome) / $previousIncome) * 100
            : 0;

        $expenseChange = $previousExpense > 0
            ? (($currentExpense - $previousExpense) / $previousExpense) * 100
            : 0;

        // Cantidad de transacciones
        $transactionCount = Transaction::where('account_id', $account->id)
            ->whereMonth('date', $currentMonth->month)
            ->whereYear('date', $currentMonth->year)
            ->count();

        return [
            'income' => $currentIncome,
            'expense' => $currentExpense,
            'income_change' => $incomeChange,
            'expense_change' => $expenseChange,
            'transaction_count' => $transactionCount,
        ];
    }

    /**
     * Evolución de últimos 7 días
     */
    private function getLast7DaysData($account, array $excludedCategories): array
    {
        $data = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);

            $income = Transaction::join('categories', 'transactions.category_id', '=', 'categories.id')
                ->where('transactions.account_id', $account->id)
                ->where('categories.type', 'income')
                ->whereNotIn('transactions.category_id', $excludedCategories)
                ->whereDate('transactions.date', $date->toDateString())
                ->sum('transactions.amount');

            $expense = Transaction::join('categories', 'transactions.category_id', '=', 'categories.id')
                ->where('transactions.account_id', $account->id)
                ->where('categories.type', 'expense')
                ->whereNotIn('transactions.category_id', $excludedCategories)
                ->whereDate('transactions.date', $date->toDateString())
                ->sum('transactions.amount');

            $data[] = [
                'date' => $date->format('d/m'),
                'day' => $date->format('D'),
                'income' => $income,
                'expense' => $expense,
                'balance' => $income - $expense,
            ];
        }

        return $data;
    }

    /**
     * Desglose por categorías (mes actual)
     */
    private function getCategoryBreakdown($account): array
    {
        $expenses = Transaction::select('categories.name', 'categories.type', DB::raw('SUM(transactions.amount) as total'))
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('transactions.account_id', $account->id)
            ->where('categories.type', 'expense')
            ->whereMonth('transactions.date', now()->month)
            ->whereYear('transactions.date', now()->year)
            ->groupBy('categories.id', 'categories.name', 'categories.type')
            ->orderByDesc('total')
            ->get();

        $income = Transaction::select('categories.name', 'categories.type', DB::raw('SUM(transactions.amount) as total'))
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('transactions.account_id', $account->id)
            ->where('categories.type', 'income')
            ->whereMonth('transactions.date', now()->month)
            ->whereYear('transactions.date', now()->year)
            ->groupBy('categories.id', 'categories.name', 'categories.type')
            ->orderByDesc('total')
            ->get();

        $totalExpense = $expenses->sum('total');
        $totalIncome = $income->sum('total');

        return [
            'expenses' => $expenses->map(function ($item) use ($totalExpense) {
                return [
                    'name' => $item->name,
                    'amount' => $item->total,
                    'percentage' => $totalExpense > 0 ? ($item->total / $totalExpense) * 100 : 0,
                ];
            }),
            'income' => $income->map(function ($item) use ($totalIncome) {
                return [
                    'name' => $item->name,
                    'amount' => $item->total,
                    'percentage' => $totalIncome > 0 ? ($item->total / $totalIncome) * 100 : 0,
                ];
            }),
        ];
    }

    /**
     * Transacciones recientes (últimas 15)
     */
    private function getRecentTransactions($account)
    {
        return Transaction::with(['category', 'account'])
            ->where('account_id', $account->id)
            ->latest('date')
            ->latest('created_at')
            ->take(15)
            ->get();
    }

    /**
     * Tendencia mensual (últimos 6 meses)
     */
    private function getMonthlyTrend($account, array $excludedCategories): array
    {
        $data = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);

            $income = Transaction::join('categories', 'transactions.category_id', '=', 'categories.id')
                ->where('transactions.account_id', $account->id)
                ->where('categories.type', 'income')
                ->whereNotIn('transactions.category_id', $excludedCategories)
                ->whereMonth('transactions.date', $date->month)
                ->whereYear('transactions.date', $date->year)
                ->sum('transactions.amount');

            $expense = Transaction::join('categories', 'transactions.category_id', '=', 'categories.id')
                ->where('transactions.account_id', $account->id)
                ->where('categories.type', 'expense')
                ->whereNotIn('transactions.category_id', $excludedCategories)
                ->whereMonth('transactions.date', $date->month)
                ->whereYear('transactions.date', $date->year)
                ->sum('transactions.amount');

            $data[] = [
                'month' => $date->format('M Y'),
                'income' => $income,
                'expense' => $expense,
                'balance' => $income - $expense,
            ];
        }

        return $data;
    }

    /**
     * Top 5 categorías de gasto
     */
    private function getTopCategories($account): array
    {
        return Transaction::select('categories.name', DB::raw('SUM(transactions.amount) as total'), DB::raw('COUNT(*) as count'))
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('transactions.account_id', $account->id)
            ->where('categories.type', 'expense')
            ->whereMonth('transactions.date', now()->month)
            ->whereYear('transactions.date', now()->year)
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total')
            ->take(5)
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->name,
                    'amount' => $item->total,
                    'count' => $item->count,
                ];
            })
            ->toArray();
    }

    /**
     * Resumen de transferencias (entrantes y salientes)
     */
    private function getTransfersSummary($account): array
    {
        $transferCategory = \App\Models\Category::where('name', 'Transferencia')->first();

        if (!$transferCategory) {
            return [
                'incoming' => 0,
                'outgoing' => 0,
                'count_incoming' => 0,
                'count_outgoing' => 0,
            ];
        }

        $incoming = Transaction::where('account_id', $account->id)
            ->where('category_id', $transferCategory->id)
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->where(function ($query) {
                $query->whereHas('category', function ($q) {
                    $q->where('type', 'income');
                });
            })
            ->sum('amount');

        $outgoing = Transaction::where('account_id', $account->id)
            ->where('category_id', $transferCategory->id)
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->where(function ($query) {
                $query->whereHas('category', function ($q) {
                    $q->where('type', 'expense');
                });
            })
            ->sum('amount');

        $countIncoming = Transaction::where('account_id', $account->id)
            ->where('category_id', $transferCategory->id)
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->whereHas('category', function ($q) {
                $q->where('type', 'income');
            })
            ->count();

        $countOutgoing = Transaction::where('account_id', $account->id)
            ->where('category_id', $transferCategory->id)
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->whereHas('category', function ($q) {
                $q->where('type', 'expense');
            })
            ->count();

        return [
            'incoming' => $incoming,
            'outgoing' => $outgoing,
            'count_incoming' => $countIncoming,
            'count_outgoing' => $countOutgoing,
            'net' => $incoming - $outgoing,
        ];
    }
}
