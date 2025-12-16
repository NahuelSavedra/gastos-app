<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AccountResource\Pages;
use App\Models\Account;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AccountResource extends Resource
{
    protected static ?string $model = Account::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Cuentas';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('🏦 Nombre de la cuenta')
                    ->required()
                    ->maxLength(100)
                    ->placeholder('Ej: Galicia, MercadoPago, Efectivo'),

                Forms\Components\TextInput::make('initial_balance')
                    ->label('💰 Saldo inicial')
                    ->numeric()
                    ->default(0)
                    ->prefix('$')
                    ->helperText('El balance inicial de esta cuenta al momento de crearla'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('🏦 Cuenta')
                    ->sortable()
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('initial_balance')
                    ->label('💰 Saldo inicial')
                    ->money('ARS')
                    ->sortable(),

                Tables\Columns\TextColumn::make('current_balance')
                    ->label('💳 Balance Actual')
                    ->getStateUsing(function (Account $record): float {
                        $income = \App\Models\Transaction::join('categories', 'transactions.category_id', '=', 'categories.id')
                            ->where('transactions.account_id', $record->id)
                            ->where('categories.type', 'income')
                            ->sum('transactions.amount');

                        $expense = \App\Models\Transaction::join('categories', 'transactions.category_id', '=', 'categories.id')
                            ->where('transactions.account_id', $record->id)
                            ->where('categories.type', 'expense')
                            ->sum('transactions.amount');

                        return $record->initial_balance + $income - $expense;
                    })
                    ->money('ARS')
                    ->color(fn ($state): string => $state >= 0 ? 'success' : 'danger')
                    ->weight('bold')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('📅 Creada')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Ver Detalles')
                    ->icon('heroicon-o-eye'),
                Tables\Actions\EditAction::make()
                    ->label('Editar'),
                Tables\Actions\DeleteAction::make()
                    ->label('Eliminar'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAccounts::route('/'),
            'create' => Pages\CreateAccount::route('/create'),
            'view' => Pages\ViewAccount::route('/{record}'),
            'edit' => Pages\EditAccount::route('/{record}/edit'),
        ];
    }
}
