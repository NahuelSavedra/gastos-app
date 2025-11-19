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
                    ->withCount([
                        'transactions as income_total' => function (Builder $query) {
                            $query->join('categories', 'transactions.category_id', '=', 'categories.id')
                                ->where('categories.type', 'income')
                                ->select(DB::raw('COALESCE(SUM(transactions.amount), 0)'));
                        },
                        'transactions as expense_total' => function (Builder $query) {
                            $query->join('categories', 'transactions.category_id', '=', 'categories.id')
                                ->where('categories.type', 'expense')
                                ->select(DB::raw('COALESCE(SUM(transactions.amount), 0)'));
                        },
                        'transactions as transactions_count' => function (Builder $query) {
                            $query->whereMonth('date', now()->month);
                        }
                    ])
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('🏦 Cuenta')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('income_total')
                    ->label('📈 Ingresos')
                    ->money('ARS', true)
                    ->color('success')
                    ->sortable(),

                Tables\Columns\TextColumn::make('expense_total')
                    ->label('📉 Gastos')
                    ->money('ARS', true)
                    ->color('danger')
                    ->sortable(),

                Tables\Columns\TextColumn::make('balance')
                    ->label('💰 Balance')
                    ->money('ARS', true)
                    ->getStateUsing(function (Account $record): float {
                        return ($record->income_total ?? 0) - ($record->expense_total ?? 0);
                    })
                    ->color(fn ($state): string => $state >= 0 ? 'success' : 'warning')
                    ->weight('bold')
                    ->sortable(),

                Tables\Columns\TextColumn::make('transactions_count')
                    ->label('📊 Movimientos')
                    ->suffix(' trans.')
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\IconColumn::make('status')
                    ->label('Estado')
                    ->boolean()
                    ->getStateUsing(function (Account $record): bool {
                        $balance = ($record->income_total ?? 0) - ($record->expense_total ?? 0);
                        return $balance >= 0;
                    })
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-exclamation-circle')
                    ->trueColor('success')
                    ->falseColor('warning'),
            ])
            ->defaultSort('balance', 'desc')
            ->paginated(false);
    }
}
