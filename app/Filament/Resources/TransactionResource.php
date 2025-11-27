<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Filament\Resources\TransactionResource\Pages\CreateTransaction;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\TransactionTemplate;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Cache;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Transacciones';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Grid::make(3)
                ->schema([
                    // Botones rápidos de monto (UX: entrada rápida)
                    Forms\Components\Group::make([
                        Forms\Components\Placeholder::make('quick_amounts')
                            ->label('Montos Rápidos')
                            ->content(fn() => view('filament.forms.quick-amounts')),
                    ])->columnSpan(3),

                    Forms\Components\Select::make('category_id')
                        ->label('Categoría')
                        ->options(fn() => self::getCachedCategories())
                        ->searchable()
                        ->required()
                        ->live()
                        ->afterStateUpdated(function ($state, Forms\Set $set) {
                            $category = Category::find($state);
                            $set('is_transfer', $category?->name === 'Transferencia');
                        })
                        ->columnSpan(2),

                    Forms\Components\TextInput::make('amount')
                        ->label('Monto')
                        ->numeric()
                        ->required()
                        ->prefix('$')
                        ->inputMode('decimal')
                        ->step(0.01)
                        ->minValue(0.01)
                        ->live(debounce: 300)
                        // UX: Autoformateo mientras escribes
                        ->formatStateUsing(fn($state) => $state ? number_format($state, 2, '.', '') : null)
                        ->extraInputAttributes([
                            'class' => 'text-right text-lg font-semibold',
                        ])
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('title')
                        ->label('Título')
                        ->maxLength(255)
                        ->columnSpan(3)
                        // UX: Placeholder dinámico según categoría
                        ->placeholder(fn(Forms\Get $get) =>
                        self::getDynamicPlaceholder($get('category_id'))
                        ),

                    Forms\Components\Select::make('account_id')
                        ->label('Cuenta Origen')
                        ->options(fn() => self::getCachedAccounts())
                        ->searchable()
                        ->required()
                        ->live()
                        ->columnSpan(fn(Forms\Get $get) => $get('is_transfer') ? 1 : 2)
                        // UX: Mostrar balance actual
                        ->getOptionLabelFromRecordUsing(fn(Account $record) =>
                        "{$record->name} (\${$record->balance})"
                        ),

                    Forms\Components\Select::make('to_account_id')
                        ->label('Cuenta Destino')
                        ->options(fn(Forms\Get $get) => self::getCachedAccounts(
                            exclude: $get('account_id')
                        ))
                        ->searchable()
                        ->required(fn(Forms\Get $get) => $get('is_transfer') === true)
                        ->visible(fn(Forms\Get $get) => $get('is_transfer') === true)
                        ->columnSpan(1)
                        ->getOptionLabelFromRecordUsing(fn(Account $record) =>
                        "{$record->name} (\${$record->balance})"
                        ),

                    Forms\Components\DatePicker::make('date')
                        ->label('Fecha')
                        ->required()
                        ->default(now())
                        ->native(false)
                        ->displayFormat('d/m/Y')
                        ->maxDate(now())
                        ->columnSpan(1),

                    Forms\Components\Textarea::make('description')
                        ->label('Descripción')
                        ->maxLength(65535)
                        ->rows(3)
                        ->columnSpan(3),

                    // Campo oculto para detectar transferencias
                    Forms\Components\Hidden::make('is_transfer')
                        ->default(false),
                ]),
        ]);
    }

    protected static function getCachedCategories(): array
    {
        return Cache::remember('categories_select', 3600, function () {
            return Category::orderBy('type')
                ->orderBy('name')
                ->pluck('name', 'id')
                ->toArray();
        });
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


    protected static function getCachedAccounts(?int $exclude = null): array
    {
        $cacheKey = $exclude ? "accounts_select_exclude_{$exclude}" : 'accounts_select';

        return Cache::remember($cacheKey, 3600, function () use ($exclude) {
            $query = Account::orderBy('name');

            if ($exclude) {
                $query->where('id', '!=', $exclude);
            }

            return $query->pluck('name', 'id')->toArray();
        });
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    protected static function getDynamicPlaceholder(?int $categoryId): string
    {
        if (!$categoryId) {
            return 'Ej: Compra supermercado';
        }

        $category = Category::find($categoryId);

        $placeholders = [
            'Alquiler' => 'Alquiler mensual',
            'Supermercado' => 'Compra en supermercado',
            'Restaurante' => 'Cena en restaurante',
            'Transporte' => 'Viaje en transporte',
            'Salario' => 'Pago de salario',
            'Servicios' => 'Pago de servicio',
        ];

        return $placeholders[$category?->name] ?? "Gasto en {$category?->name}";
    }
    public static function getHeaderActions(): array
    {
        return [
            Action::make('use_template')
                ->label('📋 Usar Template')
                ->icon('heroicon-o-document-duplicate')
                ->color('info')
                ->modalHeading('Seleccionar Template')
                ->modalDescription('Elige un template para crear una transacción rápidamente')
                ->form([
                    Forms\Components\Select::make('template_id')
                        ->label('Template')
                        ->options(TransactionTemplate::active()->pluck('name', 'id'))
                        ->required()
                        ->searchable()
                        ->live()
                        ->afterStateUpdated(function ($state, Forms\Set $set) {
                            if ($template = TransactionTemplate::find($state)) {
                                $set('amount', $template->amount);
                                $set('category_id', $template->category_id);
                                $set('account_id', $template->account_id);
                                $set('title', $template->title ?? $template->name);
                                $set('description', $template->description);
                            }
                        }),

                    Forms\Components\TextInput::make('amount')
                        ->label('Monto')
                        ->numeric()
                        ->prefix('$')
                        ->required(),

                    Forms\Components\DatePicker::make('date')
                        ->label('Fecha')
                        ->default(now())
                        ->required(),

                    Forms\Components\Hidden::make('category_id'),
                    Forms\Components\Hidden::make('account_id'),
                    Forms\Components\Hidden::make('title'),
                    Forms\Components\Hidden::make('description'),
                ])
                ->action(function (array $data) {
                    $template = TransactionTemplate::find($data['template_id']);

                    Transaction::create([
                        'title' => $data['title'],
                        'amount' => $data['amount'],
                        'category_id' => $data['category_id'],
                        'account_id' => $data['account_id'],
                        'description' => $data['description'],
                        'date' => $data['date'],
                    ]);

                    if ($template->is_recurring) {
                        $template->update(['last_generated_at' => now()]);
                    }
                })
                ->successNotificationTitle('Transacción creada desde template'),
        ];
    }

    public static function create(): CreateTransaction
    {
        return new CreateTransaction();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }

    // ... resto del código (table, relations, pages)
}
