<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages\CreateTransaction;
use App\Filament\Resources\TransactionResource\Pages;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\Account;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Tables\Table;
use Filament\Actions\Action;

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
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                // Concepto
                                Forms\Components\TextInput::make('title')
                                    ->label('🏷️ Concepto')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('ej: Supermercado, Gasolina, Salario...'),

                                // Monto
                                Forms\Components\TextInput::make('amount')
                                    ->label('💵 Monto')
                                    ->required()
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0.01)
                                    ->step(0.01)
                                    ->placeholder('0.00'),
                            ]),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                // Categoría (define automáticamente si es ingreso/gasto/transferencia)
                                Forms\Components\Select::make('category_id')
                                    ->label('🏷️ Categoría')
                                    ->options(function () {
                                        return Category::orderBy('type')
                                            ->orderBy('name')
                                            ->get()
                                            ->mapWithKeys(function ($category) {
                                                $icon = match($category->type) {
                                                    'income' => '📈',
                                                    'expense' => '📉',
                                                    default => '🔄'
                                                };
                                                return [$category->id => "{$icon} {$category->name}"];
                                            });
                                    })
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, $state) {
                                        $category = Category::find($state);
                                        // Si es transferencia, mostramos los campos adicionales
                                        if ($category && $category->name === 'Transferencia') {
                                            $set('is_transfer', true);
                                        } else {
                                            $set('is_transfer', false);
                                            $set('to_account_id', null);
                                        }
                                    })
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('name')
                                            ->label('Nombre de la categoría')
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\Select::make('type')
                                            ->label('Tipo')
                                            ->options([
                                                'income' => '📈 Ingreso',
                                                'expense' => '📉 Gasto',
                                            ])
                                            ->required()
                                            ->default('expense'),
                                    ])
                                    ->createOptionUsing(function (array $data) {
                                        return Category::create($data)->id;
                                    }),

                                // Cuenta origen (siempre visible)
                                Forms\Components\Select::make('account_id')
                                    ->label(fn (Get $get) => $get('is_transfer') ? '📤 Cuenta Origen' : '🏦 Cuenta')
                                    ->options(Account::pluck('name', 'id'))
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->live(),
                            ]),

                        // Cuenta destino (solo para transferencias)
                        Forms\Components\Select::make('to_account_id')
                            ->label('📥 Cuenta Destino')
                            ->options(fn (Get $get) =>
                            Account::where('id', '!=', $get('account_id'))
                                ->pluck('name', 'id')
                            )
                            ->required(fn (Get $get) => $get('is_transfer'))
                            ->searchable()
                            ->preload()
                            ->visible(fn (Get $get) => $get('is_transfer'))
                            ->helperText('Cuenta a la que se transferirá el dinero'),

                        Forms\Components\DatePicker::make('date')
                            ->label('📅 Fecha')
                            ->required()
                            ->default(now())
                            ->native(false)
                            ->closeOnDateSelection(),

                        // Indicador visual del tipo
                        Forms\Components\Placeholder::make('type_indicator')
                            ->label('📊 Tipo de Transacción')
                            ->content(function (Get $get): string {
                                if (!$get('category_id')) {
                                    return '⚪ Selecciona una categoría';
                                }

                                $category = Category::find($get('category_id'));

                                if (!$category) {
                                    return '⚪ Categoría no encontrada';
                                }

                                if ($category->name === 'Transferencia') {
                                    return '🔄 Esta será una TRANSFERENCIA entre cuentas';
                                }

                                return $category->type === 'income'
                                    ? '📈 Esta será un INGRESO'
                                    : '📉 Esta será un GASTO';
                            })
                            ->visible(fn (Get $get): bool => filled($get('category_id'))),

                        // Vista previa de transferencia
                        Forms\Components\Placeholder::make('transfer_preview')
                            ->label('📋 Resumen')
                            ->content(function (Get $get): string {
                                $fromAccount = $get('account_id') ?
                                    Account::find($get('account_id'))?->name : 'Seleccionar';
                                $toAccount = $get('to_account_id') ?
                                    Account::find($get('to_account_id'))?->name : 'Seleccionar';
                                $amount = $get('amount') ? '$' . number_format($get('amount'), 2) : '$0.00';

                                return "💸 {$fromAccount} → 💰 {$toAccount} | Monto: {$amount}";
                            })
                            ->visible(fn (Get $get): bool =>
                                $get('is_transfer') && $get('account_id') && $get('to_account_id') && $get('amount')
                            ),

                        // Descripción opcional
                        Forms\Components\Textarea::make('description')
                            ->label('📝 Descripción (Opcional)')
                            ->maxLength(500)
                            ->rows(3)
                            ->placeholder('Detalles adicionales sobre esta transacción...'),

                        // Campo oculto para detectar si es transferencia
                        Forms\Components\Hidden::make('is_transfer')
                            ->default(false),
                    ])
                    ->columnSpan('full'),
            ]);
    }

    public static function create(): CreateTransaction
    {
        return new CreateTransaction();
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
                    ->label('Type')
                    ->formatStateUsing(fn ($state) => $state === 'income' ? 'Ingreso' : 'Gasto'),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->money('ARS', true) // ajustá tu moneda
                    ->sortable(),

                Tables\Columns\TextColumn::make('account.name')
                    ->label('Account')
                    ->sortable()
                    ->searchable(),
            ])
            ->defaultSort('date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options(['income' => 'Income', 'expense' => 'Expense']),
                Tables\Filters\SelectFilter::make('category_id')
                    ->relationship('category', 'name')
                    ->label('Category'),
                Tables\Filters\SelectFilter::make('account_id')
                    ->relationship('account', 'name')
                    ->label('Account'),
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

    protected function getHeaderActions(): array
    {
        return [
            $this->quickExpenseAction('Pagar Alquiler', 1, 'heroicon-o-home'),
            $this->quickExpenseAction('Pagar Internet', 2, 'heroicon-o-wifi'),
            $this->quickExpenseAction('Pagar Comida', 3, 'heroicon-o-shopping-cart'),
        ];
    }

    protected function quickExpenseAction(string $label, int $categoryId, string $icon): Action
    {
        return Action::make(Str::slug($label))
            ->label($label)
            ->icon($icon)
            ->color('danger')
            ->form([
                Forms\Components\TextInput::make('amount')
                    ->label('Monto')
                    ->numeric()
                    ->required(),
            ])
            ->action(fn ($data) => Transaction::create([
                'type' => 'expense',
                'category_id' => $categoryId,
                'amount' => $data['amount'],
                'created_at' => now(),
            ]));
    }

}
