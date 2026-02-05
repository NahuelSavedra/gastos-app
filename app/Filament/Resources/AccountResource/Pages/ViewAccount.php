<?php

namespace App\Filament\Resources\AccountResource\Pages;

use App\Filament\Resources\AccountResource;
use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;

class ViewAccount extends ViewRecord
{
    protected static string $resource = AccountResource::class;

    protected static string $view = 'filament.resources.account-resource.pages.view-account';

    #[Url]
    public ?string $month = null;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        if (! $this->month) {
            $this->month = now()->format('Y-m');
        }
    }

    public function getSelectedDate(): Carbon
    {
        return Carbon::createFromFormat('Y-m', $this->month ?? now()->format('Y-m'));
    }

    public function getMonthOptions(): array
    {
        $months = [];
        $currentDate = Carbon::now();

        for ($i = 0; $i < 12; $i++) {
            $date = $currentDate->copy()->subMonths($i);
            $key = $date->format('Y-m');
            $label = $date->translatedFormat('F Y');
            $months[$key] = ucfirst($label);
        }

        return $months;
    }

    public function updatedMonth(): void
    {
        // Livewire will automatically re-render
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('Editar Cuenta'),
            Actions\Action::make('new_transaction')
                ->label('Nueva Transacción')
                ->icon('heroicon-o-plus-circle')
                ->color('success')
                ->url(fn () => route('filament.app.resources.transactions.create', [
                    'account_id' => $this->record->id,
                ])),
        ];
    }

    public function getViewData(): array
    {
        $account = $this->record;
        $selectedDate = $this->getSelectedDate();
        $excludedCategoryIds = Category::where('name', 'Transfer')->pluck('id')->toArray();

        return [
            'account' => $account,
            'selectedMonth' => $this->month,
            'monthLabel' => ucfirst($selectedDate->translatedFormat('F Y')),
            'monthOptions' => $this->getMonthOptions(),
            'currentBalance' => $this->getCurrentBalance($account),
            'monthBalance' => $this->getMonthBalance($account, $excludedCategoryIds, $selectedDate),
            'monthStats' => $this->getMonthStats($account, $excludedCategoryIds, $selectedDate),
            'last7Days' => $this->getLast7DaysData($account, $excludedCategoryIds, $selectedDate),
            'categoryBreakdown' => $this->getCategoryBreakdown($account, $selectedDate),
            'recentTransactions' => $this->getRecentTransactions($account, $selectedDate),
            'monthlyTrend' => $this->getMonthlyTrend($account, $excludedCategoryIds),
            'topCategories' => $this->getTopCategories($account, $selectedDate),
            'transfersSummary' => $this->getTransfersSummary($account, $selectedDate),
        ];
    }

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

    private function getMonthBalance($account, array $excludedCategories, Carbon $date): float
    {
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

        return $income - $expense;
    }

    private function getMonthStats($account, array $excludedCategories, Carbon $selectedDate): array
    {
        $previousDate = $selectedDate->copy()->subMonth();

        // Mes seleccionado
        $currentIncome = Transaction::join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('transactions.account_id', $account->id)
            ->where('categories.type', 'income')
            ->whereNotIn('transactions.category_id', $excludedCategories)
            ->whereMonth('transactions.date', $selectedDate->month)
            ->whereYear('transactions.date', $selectedDate->year)
            ->sum('transactions.amount');

        $currentExpense = Transaction::join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('transactions.account_id', $account->id)
            ->where('categories.type', 'expense')
            ->whereNotIn('transactions.category_id', $excludedCategories)
            ->whereMonth('transactions.date', $selectedDate->month)
            ->whereYear('transactions.date', $selectedDate->year)
            ->sum('transactions.amount');

        // Mes anterior
        $previousIncome = Transaction::join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('transactions.account_id', $account->id)
            ->where('categories.type', 'income')
            ->whereNotIn('transactions.category_id', $excludedCategories)
            ->whereMonth('transactions.date', $previousDate->month)
            ->whereYear('transactions.date', $previousDate->year)
            ->sum('transactions.amount');

        $previousExpense = Transaction::join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('transactions.account_id', $account->id)
            ->where('categories.type', 'expense')
            ->whereNotIn('transactions.category_id', $excludedCategories)
            ->whereMonth('transactions.date', $previousDate->month)
            ->whereYear('transactions.date', $previousDate->year)
            ->sum('transactions.amount');

        $incomeChange = $previousIncome > 0
            ? (($currentIncome - $previousIncome) / $previousIncome) * 100
            : 0;

        $expenseChange = $previousExpense > 0
            ? (($currentExpense - $previousExpense) / $previousExpense) * 100
            : 0;

        $transactionCount = Transaction::where('account_id', $account->id)
            ->whereMonth('date', $selectedDate->month)
            ->whereYear('date', $selectedDate->year)
            ->count();

        return [
            'income' => $currentIncome,
            'expense' => $currentExpense,
            'income_change' => $incomeChange,
            'expense_change' => $expenseChange,
            'transaction_count' => $transactionCount,
        ];
    }

    private function getLast7DaysData($account, array $excludedCategories, Carbon $selectedDate): array
    {
        $data = [];
        $endOfMonth = $selectedDate->copy()->endOfMonth();

        if ($selectedDate->isSameMonth(now())) {
            $endOfMonth = now();
        }

        $days = min(7, $endOfMonth->day);

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = $endOfMonth->copy()->subDays($i);

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
                'day' => $date->translatedFormat('D'),
                'income' => $income,
                'expense' => $expense,
                'balance' => $income - $expense,
            ];
        }

        return $data;
    }

    private function getCategoryBreakdown($account, Carbon $selectedDate): array
    {
        $expenses = Transaction::select('categories.name', 'categories.type', DB::raw('SUM(transactions.amount) as total'))
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('transactions.account_id', $account->id)
            ->where('categories.type', 'expense')
            ->where('categories.name', '!=', 'Transfer')
            ->whereMonth('transactions.date', $selectedDate->month)
            ->whereYear('transactions.date', $selectedDate->year)
            ->groupBy('categories.id', 'categories.name', 'categories.type')
            ->orderByDesc('total')
            ->get();

        $income = Transaction::select('categories.name', 'categories.type', DB::raw('SUM(transactions.amount) as total'))
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('transactions.account_id', $account->id)
            ->where('categories.type', 'income')
            ->where('categories.name', '!=', 'Transfer')
            ->whereMonth('transactions.date', $selectedDate->month)
            ->whereYear('transactions.date', $selectedDate->year)
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

    private function getRecentTransactions($account, Carbon $selectedDate)
    {
        return Transaction::with(['category', 'account'])
            ->where('account_id', $account->id)
            ->whereMonth('date', $selectedDate->month)
            ->whereYear('date', $selectedDate->year)
            ->latest('date')
            ->latest('created_at')
            ->take(20)
            ->get();
    }

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
                'month' => ucfirst($date->translatedFormat('M Y')),
                'income' => $income,
                'expense' => $expense,
                'balance' => $income - $expense,
            ];
        }

        return $data;
    }

    private function getTopCategories($account, Carbon $selectedDate): array
    {
        return Transaction::select('categories.name', DB::raw('SUM(transactions.amount) as total'), DB::raw('COUNT(*) as count'))
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('transactions.account_id', $account->id)
            ->where('categories.type', 'expense')
            ->where('categories.name', '!=', 'Transfer')
            ->whereMonth('transactions.date', $selectedDate->month)
            ->whereYear('transactions.date', $selectedDate->year)
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total')
            ->take(5)
            ->get()
            ->map(fn ($item) => [
                'name' => $item->name,
                'amount' => $item->total,
                'count' => $item->count,
            ])
            ->toArray();
    }

    private function getTransfersSummary($account, Carbon $selectedDate): array
    {
        $transferCategory = Category::where('name', 'Transfer')->first();

        if (! $transferCategory) {
            return [
                'incoming' => 0,
                'outgoing' => 0,
                'count_incoming' => 0,
                'count_outgoing' => 0,
                'net' => 0,
            ];
        }

        // Transferencias recibidas (income)
        $incoming = Transaction::join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('transactions.account_id', $account->id)
            ->where('transactions.category_id', $transferCategory->id)
            ->where('categories.type', 'income')
            ->whereMonth('transactions.date', $selectedDate->month)
            ->whereYear('transactions.date', $selectedDate->year)
            ->sum('transactions.amount');

        $countIncoming = Transaction::join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('transactions.account_id', $account->id)
            ->where('transactions.category_id', $transferCategory->id)
            ->where('categories.type', 'income')
            ->whereMonth('transactions.date', $selectedDate->month)
            ->whereYear('transactions.date', $selectedDate->year)
            ->count();

        // Transferencias enviadas (expense)
        $outgoing = Transaction::join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('transactions.account_id', $account->id)
            ->where('transactions.category_id', $transferCategory->id)
            ->where('categories.type', 'expense')
            ->whereMonth('transactions.date', $selectedDate->month)
            ->whereYear('transactions.date', $selectedDate->year)
            ->sum('transactions.amount');

        $countOutgoing = Transaction::join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('transactions.account_id', $account->id)
            ->where('transactions.category_id', $transferCategory->id)
            ->where('categories.type', 'expense')
            ->whereMonth('transactions.date', $selectedDate->month)
            ->whereYear('transactions.date', $selectedDate->year)
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
