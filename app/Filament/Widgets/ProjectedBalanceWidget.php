<?php

namespace App\Filament\Widgets;

use App\Models\Account;
use App\Models\Transaction;
use App\Models\TransactionTemplate;
use Carbon\Carbon;
use Filament\Widgets\Widget;

class ProjectedBalanceWidget extends Widget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $pollingInterval = '30s';

    protected static string $view = 'filament.widgets.projected-balance';

    public function getViewData(): array
    {
        $currentBalance = $this->getCurrentBalance();
        $pendingData = $this->getPendingRecurringExpenses();

        // Solo restar gastos pendientes, no sumar ingresos
        $projectedBalance = $currentBalance - $pendingData['totalExpenses'];

        $accountsCount = Account::where('include_in_totals', true)->count();

        return [
            'currentBalance' => $currentBalance,
            'projectedBalance' => $projectedBalance,
            'accountsCount' => $accountsCount,
            'pendingExpenses' => $pendingData['templates'],
            'totalPendingExpenses' => $pendingData['totalExpenses'],
            'hasEstimated' => $pendingData['hasEstimated'],
            'unknownTemplates' => $pendingData['unknownTemplates'],
        ];
    }

    private function getCurrentBalance(): float
    {
        $accounts = Account::where('include_in_totals', true)->get();

        return $accounts->sum(function ($account) {
            $income = Transaction::join('categories', 'transactions.category_id', '=', 'categories.id')
                ->where('transactions.account_id', $account->id)
                ->where('categories.type', 'income')
                ->sum('transactions.amount');

            $expense = Transaction::join('categories', 'transactions.category_id', '=', 'categories.id')
                ->where('transactions.account_id', $account->id)
                ->where('categories.type', 'expense')
                ->sum('transactions.amount');

            return $account->initial_balance + $income - $expense;
        });
    }

    private function getPendingRecurringExpenses(): array
    {
        // Solo obtener templates de gastos (no ingresos)
        $pendingTemplates = TransactionTemplate::active()
            ->recurring()
            ->pendingThisMonth()
            ->with(['category', 'account'])
            ->whereHas('category', function ($query) {
                $query->where('type', 'expense');
            })
            ->get();

        $totalExpenses = 0;
        $templates = [];
        $hasEstimated = false;
        $unknownTemplates = [];

        foreach ($pendingTemplates as $template) {
            $amount = $template->amount;
            $isEstimated = false;

            // Si el amount es null, intentar estimar
            if ($amount === null) {
                $estimatedAmount = $this->estimateAmount($template);

                if ($estimatedAmount !== null) {
                    $amount = $estimatedAmount;
                    $isEstimated = true;
                    $hasEstimated = true;
                } else {
                    // No se pudo estimar, agregar a lista de desconocidos
                    $unknownTemplates[] = $template->name;

                    continue;
                }
            }

            $totalExpenses += $amount;

            $templates[] = [
                'name' => $template->name,
                'amount' => $amount,
                'isEstimated' => $isEstimated,
                'category' => $template->category->name,
                'account' => $template->account->name,
            ];
        }

        return [
            'totalExpenses' => $totalExpenses,
            'templates' => $templates,
            'hasEstimated' => $hasEstimated,
            'unknownTemplates' => $unknownTemplates,
        ];
    }

    private function estimateAmount(TransactionTemplate $template): ?float
    {
        $previousMonth = Carbon::now()->subMonth();

        // Estrategia: Buscar transacción del mes anterior con la misma categoría y cuenta
        $previousTransaction = Transaction::where('category_id', $template->category_id)
            ->where('account_id', $template->account_id)
            ->whereMonth('date', $previousMonth->month)
            ->whereYear('date', $previousMonth->year)
            ->first();

        if ($previousTransaction) {
            return $previousTransaction->amount;
        }

        // Fallback: Buscar cualquier transacción anterior de esa categoría/cuenta
        $lastTransaction = Transaction::where('category_id', $template->category_id)
            ->where('account_id', $template->account_id)
            ->latest('date')
            ->first();

        if ($lastTransaction) {
            return $lastTransaction->amount;
        }

        // No se pudo estimar
        return null;
    }
}
