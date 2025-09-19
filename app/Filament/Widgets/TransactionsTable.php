<?php
namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Widgets\TableWidget as BaseWidget;

class TransactionsTable extends BaseWidget
{
    protected static ?string $heading = '📋 Transacciones Recientes';
    protected static ?int $sort = 4; // ⭐ CUARTO LUGAR (último)
    protected int | string | array $columnSpan = 'full';
    protected static ?string $pollingInterval = '5m'; // Menos frecuente

    public function table(Table $table): Table
    {
        return $table
            ->query(Transaction::with(['account', 'category'])->latest())
            ->columns([
                TextColumn::make('created_at')
                    ->label('📅 Fecha')
                    ->date('d/M/Y')
                    ->sortable(),

                TextColumn::make('category.name')
                    ->label('🏷️ Categoría')
                    ->searchable(),

                TextColumn::make('description')
                    ->label('📝 Descripción')
                    ->limit(30)
                    ->searchable(),

                TextColumn::make('amount')
                    ->label('💵 Monto')
                    ->formatStateUsing(fn ($state, $record): string =>
                        ($record->type === 'expense' ? '-' : '+') .
                        '$' . number_format($state, 2)
                    )
                    ->color(fn ($record): string =>
                    $record->type === 'income' ? 'success' : 'danger'
                    )
                    ->sortable(),
            ])
            ->defaultPaginationPageOption(5) // Solo 5 transacciones por página
            ->striped()
            ->defaultSort('created_at', 'desc');
    }
}
