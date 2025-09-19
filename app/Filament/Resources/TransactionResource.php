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
                // Card principal con mejor organización visual
                Forms\Components\Card::make()
                    ->schema([
                        // Selector de tipo de transacción mejorado
                        Forms\Components\Select::make('transaction_type')
                            ->label('💰 Tipo de Operación')
                            ->options([
                                'income' => '📈 Ingreso',
                                'expense' => '📉 Gasto',
                                'transfer' => '🔄 Transferencia entre cuentas',
                            ])
                            ->default('expense')
                            ->required()
                            ->live() // Actualiza campos en tiempo real
                            ->afterStateUpdated(function (Set $set, $state) {
                                // Limpiar campos cuando cambia el tipo
                                if ($state === 'transfer') {
                                    $set('type', null);
                                    $set('category_id', null);
                                } else {
                                    $set('type', $state);
                                    $set('from_account_id', null);
                                    $set('to_account_id', null);
                                }
                            }),
                    ])
                    ->columnSpan('full'),

                // Card para campos básicos
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                // Título dinámico según el tipo
                                Forms\Components\TextInput::make('title')
                                    ->label(fn (Get $get): string => match ($get('transaction_type')) {
                                        'transfer' => '🏷️ Concepto de Transferencia',
                                        'income' => '🏷️ Concepto del Ingreso',
                                        'expense' => '🏷️ Concepto del Gasto',
                                        default => '🏷️ Título',
                                    })
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder(fn (Get $get): string => match ($get('transaction_type')) {
                                        'transfer' => 'ej: Ahorro mensual, Pago de tarjeta...',
                                        'income' => 'ej: Salario, Freelance, Venta...',
                                        'expense' => 'ej: Supermercado, Gasolina, Cena...',
                                        default => 'Descripción breve...',
                                    }),

                                // Monto con formato de moneda
                                Forms\Components\TextInput::make('amount')
                                    ->label('💵 Monto')
                                    ->required()
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0.01)
                                    ->step(0.01)
                                    ->placeholder('0.00'),
                            ]),

                        // Fecha con valor por defecto
                        Forms\Components\DatePicker::make('date')
                            ->label('📅 Fecha')
                            ->required()
                            ->default(now())
                            ->native(false),
                    ])
                    ->columnSpan('full'),

                // Card condicional para transacciones normales (ingreso/gasto)
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                // Cuenta (para ingresos y gastos)
                                Forms\Components\Select::make('account_id')
                                    ->label('🏦 Cuenta')
                                    ->options(Account::pluck('name', 'id'))
                                    ->required()
                                    ->searchable()
                                    ->preload(),

                                // Categoría (para ingresos y gastos)
                                Forms\Components\Select::make('category_id')
                                    ->label('🏷️ Categoría')
                                    ->options(Category::pluck('name', 'id'))
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('name')
                                            ->label('Nombre de la categoría')
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\ColorPicker::make('color')
                                            ->label('Color')
                                            ->default('#3B82F6'),
                                    ]),
                            ]),
                    ])
                    ->visible(fn (Get $get): bool =>
                    in_array($get('transaction_type'), ['income', 'expense'])
                    )
                    ->columnSpan('full'),

                // Card condicional para transferencias
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Section::make('🔄 Configuración de Transferencia')
                            ->description('Selecciona las cuentas origen y destino para la transferencia')
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        // Cuenta origen
                                        Forms\Components\Select::make('from_account_id')
                                            ->label('📤 Cuenta Origen')
                                            ->options(Account::pluck('name', 'id'))
                                            ->required()
                                            ->searchable()
                                            ->preload()
                                            ->live()
                                            ->afterStateUpdated(function (Set $set, $state, Get $get) {
                                                // Evitar que origen y destino sean iguales
                                                if ($state === $get('to_account_id')) {
                                                    $set('to_account_id', null);
                                                }
                                            }),

                                        // Cuenta destino
                                        Forms\Components\Select::make('to_account_id')
                                            ->label('📥 Cuenta Destino')
                                            ->options(fn (Get $get) =>
                                            Account::where('id', '!=', $get('from_account_id'))
                                                ->pluck('name', 'id')
                                            )
                                            ->required()
                                            ->searchable()
                                            ->preload()
                                            ->live(),
                                    ]),

                                // Vista previa de la transferencia
                                Forms\Components\Placeholder::make('transfer_preview')
                                    ->label('📋 Resumen de Transferencia')
                                    ->content(function (Get $get): string {
                                        $fromAccount = $get('from_account_id') ?
                                            Account::find($get('from_account_id'))?->name : 'Seleccionar';
                                        $toAccount = $get('to_account_id') ?
                                            Account::find($get('to_account_id'))?->name : 'Seleccionar';
                                        $amount = $get('amount') ? '$' . number_format($get('amount'), 2) : '$0.00';

                                        return "💸 {$fromAccount} → 💰 {$toAccount} | Monto: {$amount}";
                                    })
                                    ->visible(fn (Get $get): bool =>
                                        $get('from_account_id') && $get('to_account_id') && $get('amount')
                                    ),
                            ])
                    ])
                    ->visible(fn (Get $get): bool => $get('transaction_type') === 'transfer')
                    ->columnSpan('full'),

                // Descripción opcional
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->label('📝 Descripción (Opcional)')
                            ->maxLength(500)
                            ->rows(3)
                            ->placeholder('Detalles adicionales sobre esta transacción...'),
                    ])
                    ->columnSpan('full'),

                // Campos ocultos para el procesamiento
                Forms\Components\Hidden::make('type'),
            ]);
    }

    // Sobrescribir el método de creación para manejar transferencias
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
