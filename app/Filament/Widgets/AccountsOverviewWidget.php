<?php

namespace App\Filament\Widgets;

use App\Models\Account;
use App\Models\Transaction;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class AccountsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $heading = '🏦 Resumen de Cuentas';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Account::query()
                    ->select('accounts.*')
                    ->addSelect([
                        // Saldo actual real (basado en todas las transacciones)
                        'real_balance' => Transaction::selectRaw('
                            COALESCE(
                                SUM(
                                    CASE
                                        WHEN categories.type = "income" THEN transactions.amount
                                        WHEN categories.type = "expense" THEN -transactions.amount
                                        ELSE 0
                                    END
                                ), 0
                            )
                        ')
                            ->join('categories', 'transactions.category_id', '=', 'categories.id')
                            ->whereColumn('transactions.account_id', 'accounts.id'),

                        // Gastos del mes actual
                        'current_month_expenses' => Transaction::selectRaw('COALESCE(SUM(transactions.amount), 0)')
                            ->join('categories', 'transactions.category_id', '=', 'categories.id')
                            ->whereColumn('transactions.account_id', 'accounts.id')
                            ->where('categories.type', 'expense')
                            ->whereYear('transactions.date', now()->year)
                            ->whereMonth('transactions.date', now()->month),

                        // Ingresos del mes actual
                        'current_month_income' => Transaction::selectRaw('COALESCE(SUM(transactions.amount), 0)')
                            ->join('categories', 'transactions.category_id', '=', 'categories.id')
                            ->whereColumn('transactions.account_id', 'accounts.id')
                            ->where('categories.type', 'income')
                            ->whereYear('transactions.date', now()->year)
                            ->whereMonth('transactions.date', now()->month),

                        // Última transacción
                        'last_transaction_date' => Transaction::select('date')
                            ->whereColumn('transactions.account_id', 'accounts.id')
                            ->orderBy('date', 'desc')
                            ->limit(1),

                        // Promedio de gastos diarios este mes
                        'daily_average' => Transaction::selectRaw('
                            COALESCE(
                                SUM(transactions.amount) / ' . now()->day . ', 0
                            )
                        ')
                            ->join('categories', 'transactions.category_id', '=', 'categories.id')
                            ->whereColumn('transactions.account_id', 'accounts.id')
                            ->where('categories.type', 'expense')
                            ->whereYear('transactions.date', now()->year)
                            ->whereMonth('transactions.date', now()->month),
                    ])
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('🏦 Cuenta')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (Account $record): string =>
                    $record->last_transaction_date
                        ? '📅 Última actividad: ' . \Carbon\Carbon::parse($record->last_transaction_date)->diffForHumans()
                        : '⚪ Sin movimientos'
                    ),

                Tables\Columns\TextColumn::make('real_balance')
                    ->label('💰 Saldo Real')
                    ->money('ARS', true)
                    ->color(fn ($state): string => match(true) {
                        $state > 50000 => 'success',
                        $state > 10000 => 'info',
                        $state > 0 => 'warning',
                        default => 'danger'
                    })
                    ->weight('bold')
                    ->sortable()
                    ->size('lg'),

                Tables\Columns\TextColumn::make('current_month_expenses')
                    ->label('📉 Gastos del Mes')
                    ->money('ARS', true)
                    ->color('danger')
                    ->description(fn (Account $record): string =>
                        '📊 Promedio diario: $' . number_format($record->daily_average ?? 0, 2)
                    )
                    ->sortable(),

                Tables\Columns\TextColumn::make('current_month_income')
                    ->label('📈 Ingresos del Mes')
                    ->money('ARS', true)
                    ->color('success')
                    ->sortable(),

                Tables\Columns\TextColumn::make('month_balance')
                    ->label('💵 Balance Mensual')
                    ->getStateUsing(function (Account $record): float {
                        return ($record->current_month_income ?? 0) - ($record->current_month_expenses ?? 0);
                    })
                    ->money('ARS', true)
                    ->color(fn ($state): string => $state >= 0 ? 'success' : 'danger')
                    ->weight('semibold')
                    ->description(fn (Account $record, $state): string =>
                    $state >= 0
                        ? '✅ Superávit este mes'
                        : '⚠️ Déficit este mes'
                    )
                    ->sortable(),

                Tables\Columns\TextColumn::make('projection')
                    ->label('📊 Proyección')
                    ->getStateUsing(function (Account $record): string {
                        $daysInMonth = now()->daysInMonth;
                        $currentDay = now()->day;
                        $remainingDays = $daysInMonth - $currentDay;

                        if ($remainingDays <= 0) {
                            return '$0';
                        }

                        $dailyAverage = $record->daily_average ?? 0;
                        $projection = $dailyAverage * $remainingDays;

                        return '$' . number_format($projection, 2);
                    })
                    ->description(fn (): string =>
                        '📅 Faltan ' . (now()->daysInMonth - now()->day) . ' días'
                    )
                    ->color('info'),
            ])
            ->defaultSort('real_balance', 'desc')
            ->paginated(false);
    }
}
