<?php

// 📁 app/Filament/Widgets/AccountsOverviewWidget.php
namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\Account;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

class AccountsOverviewWidget extends BaseWidget
{
    protected static ?string $heading = '🏦 Resumen de Cuentas';
    protected static ?int $sort = 2; // ⭐ SEGUNDO LUGAR
    protected int | string | array $columnSpan = 'full';
    protected static ?string $pollingInterval = '60s';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Account::withSum([
                    'transactions as total_income' => function (Builder $query) {
                        $query->where('type', 'income');
                    }
                ], 'amount')
                    ->withSum([
                        'transactions as total_expense' => function (Builder $query) {
                            $query->where('type', 'expense');
                        }
                    ], 'amount')
                    ->withCount('transactions as transactions_count')
            )
            ->columns([
                TextColumn::make('name')
                    ->label('🏷️ Cuenta')
                    ->formatStateUsing(fn (string $state, Account $record): string =>
                        $this->getAccountIcon($record->type ?? 'default') . ' ' . $state
                    )
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('total_income')
                    ->label('📈 Ingresos')
                    ->formatStateUsing(fn ($state): string =>
                        '$' . number_format($state ?? 0, 2)
                    )
                    ->color('success')
                    ->sortable(),

                TextColumn::make('total_expense')
                    ->label('📉 Gastos')
                    ->formatStateUsing(fn ($state): string =>
                        '$' . number_format($state ?? 0, 2)
                    )
                    ->color('danger')
                    ->sortable(),

                TextColumn::make('balance')
                    ->label('💰 Balance')
                    ->getStateUsing(fn (Account $record): float =>
                        ($record->total_income ?? 0) - ($record->total_expense ?? 0)
                    )
                    ->formatStateUsing(fn ($state): string =>
                        '$' . number_format($state, 2)
                    )
                    ->color(fn ($state): string => match (true) {
                        $state > 0 => 'success',
                        $state == 0 => 'gray',
                        default => 'danger',
                    })
                    ->weight('bold')
                    ->sortable(),

                TextColumn::make('transactions_count')
                    ->label('🔢 Movimientos')
                    ->suffix(' trans.')
                    ->color('info')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('edit')
                    ->label('✏️')
                    ->tooltip('Editar cuenta')
                    ->icon('heroicon-o-pencil')
                    ->url(fn (Account $record): string =>
                    route('filament.app.resources.accounts.edit', $record)
                    ),

                Tables\Actions\Action::make('view_transactions')
                    ->label('👁️')
                    ->tooltip('Ver movimientos')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Account $record): string =>
                    route('filament.app.resources.transactions.index', [
                        'tableFilters' => ['account' => ['value' => $record->id]]
                    ])
                    ),
            ])
            ->defaultSort('balance', 'desc')
            ->striped()
            ->paginated(false);
    }

    private function getAccountIcon($type): string
    {
        return match($type) {
            'checking' => '🏦',
            'savings' => '💎',
            'credit_card' => '💳',
            'cash' => '💵',
            'investment' => '📈',
            default => '💰'
        };
    }
}
