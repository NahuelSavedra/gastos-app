<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Filament\Resources\TransactionResource\RelationManagers;
use App\Models\Category;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Finanzas';
    protected static ?string $modelLabel = 'Transaction';
    protected static ?string $pluralModelLabel = 'Transactions';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(2)->schema([
                    Forms\Components\TextInput::make('title')
                        ->label('Title')
                        ->maxLength(120),

                    Forms\Components\TextInput::make('amount')
                        ->label('Amount')
                        ->numeric()
                        ->minValue(0.01)
                        ->required()
                        ->rule('decimal:0,2'),

                    // El type se rellena y queda bloqueado (fuente de verdad: categoría)
                    Forms\Components\ToggleButtons::make('type')
                        ->label('Type')
                        ->options([
                            'income' => 'Income',
                            'expense' => 'Expense',
                        ])
                        ->inline()
                        ->disabled()   // no editable manualmente
                        ->dehydrated(),// se guarda su valor

                    Forms\Components\Select::make('category_id')
                        ->label('Category')
                        ->relationship('category', 'name')
                        ->searchable()
                        ->preload()
                        ->required()
                        // al cambiar de categoría, setear 'type' según la categoría elegida
                        ->live()
                        ->afterStateUpdated(function ($state, callable $set) {
                            $type = Category::query()->whereKey($state)->value('type');
                            if ($type) {
                                $set('type', $type);
                            }
                        }),

                    Forms\Components\Select::make('account_id')
                        ->label('Cuenta')
                        ->relationship('account', 'name')
                        ->required(),

                    Forms\Components\DatePicker::make('date')
                        ->label('Date')
                        ->displayFormat('d/m/Y')
                        ->format('Y-m-d')
                        ->default(now()) // default visible en el form
                        ->native(false)   // UI consistente con Filament
                        ->nullable(),     // opcional; si llega null, el modelo pone "hoy"
                ])->columns(2),

                Forms\Components\Textarea::make('description')
                    ->label('Description')
                    ->rows(3)
                    ->maxLength(1000)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn ($record) => $record->type === 'income' ? 'success' : 'danger')
                    ->label('Type'),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->money('ARS', true) // ajustá tu moneda
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrap()
                    ->limit(80),
            ])
            ->defaultSort('date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options(['income' => 'Income', 'expense' => 'Expense']),
                Tables\Filters\SelectFilter::make('category_id')
                    ->relationship('category', 'name')
                    ->label('Category'),
            ])
            ->paginated([10, 25, 50]);
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
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }
}
