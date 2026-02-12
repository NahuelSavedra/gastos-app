<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\TableWidget as BaseWidget;

class TransactionsTable extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $pollingInterval = '30s';

    public function getTableHeading(): string
    {
        $selectedMonth = $this->filters['month'] ?? now()->format('Y-m');
        $date = Carbon::createFromFormat('Y-m', $selectedMonth);
        $monthLabel = ucfirst($date->translatedFormat('F Y'));

        return "📋 Transacciones de {$monthLabel}";
    }

    public function table(Table $table): Table
    {
        $selectedMonth = $this->filters['month'] ?? now()->format('Y-m');
        $date = Carbon::createFromFormat('Y-m', $selectedMonth);

        return $table
            ->query(
                Transaction::with(['account', 'category'])
                    ->whereMonth('date', $date->month)
                    ->whereYear('date', $date->year)
                    ->latest('date')
            )
            ->columns([
                TextColumn::make('date')
                    ->label('📅 Fecha')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('title')
                    ->label('📝 Descripción')
                    ->limit(40)
                    ->searchable()
                    ->tooltip(fn ($record) => $record->description),

                TextColumn::make('category.name')
                    ->label('🏷️ Categoría')
                    ->badge()
                    ->color(fn ($record): string => $record->category?->type === 'income' ? 'success' : 'danger'),

                TextColumn::make('account.name')
                    ->label('🏦 Cuenta')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('amount')
                    ->label('💵 Monto')
                    ->formatStateUsing(fn ($state, $record): string => ($record->category?->type === 'expense' ? '-' : '+').
                        '$'.number_format($state, 2)
                    )
                    ->color(fn ($record): string => $record->category?->type === 'income' ? 'success' : 'danger')
                    ->sortable()
                    ->alignEnd(),
            ])
            ->defaultPaginationPageOption(10)
            ->striped()
            ->defaultSort('date', 'desc');
    }
}
