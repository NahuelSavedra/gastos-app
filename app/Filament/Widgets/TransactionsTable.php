<?php
namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;

class TransactionsTable extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->query(Transaction::query()->latest('date'))
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Categoría'),

                Tables\Columns\TextColumn::make('description')
                    ->label('Descripción'),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Monto')
                    ->money('ARS') // o tu moneda
                    ->color(fn ($record) => $record->type === 'income' ? 'success' : 'danger'),
            ])
            ->paginated([10, 25, 50])
            ->defaultSort('date', 'desc');
    }
}
