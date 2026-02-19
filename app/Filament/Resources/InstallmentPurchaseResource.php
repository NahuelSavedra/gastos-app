<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InstallmentPurchaseResource\Pages;
use App\Models\Category;
use App\Models\CreditCard;
use App\Models\InstallmentPurchase;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class InstallmentPurchaseResource extends Resource
{
    protected static ?string $model = InstallmentPurchase::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationLabel = 'Compras en Cuotas';

    protected static ?string $modelLabel = 'Compra en Cuotas';

    protected static ?string $pluralModelLabel = 'Compras en Cuotas';

    protected static ?string $navigationGroup = 'Finanzas';

    protected static ?int $navigationSort = 11;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información de la Compra')
                    ->schema([
                        Forms\Components\Select::make('credit_card_id')
                            ->label('Tarjeta de crédito')
                            ->options(CreditCard::active()->pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('category_id')
                            ->label('Categoría')
                            ->options(
                                Category::where('type', 'expense')
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                            )
                            ->nullable()
                            ->searchable()
                            ->placeholder('Sin categoría'),

                        Forms\Components\TextInput::make('title')
                            ->label('Descripción')
                            ->required()
                            ->maxLength(200)
                            ->placeholder('Ej: Heladera Samsung')
                            ->columnSpan(2),

                        Forms\Components\TextInput::make('store')
                            ->label('Comercio / Tienda')
                            ->maxLength(100)
                            ->placeholder('Ej: Frávega, MercadoLibre')
                            ->columnSpan(2),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Cuotas')
                    ->schema([
                        Forms\Components\TextInput::make('total_amount')
                            ->label('Monto total')
                            ->numeric()
                            ->required()
                            ->prefix('$')
                            ->minValue(0.01)
                            ->live(debounce: 500)
                            ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                $installments = (int) $get('installments_count');
                                if ($installments > 0 && $state > 0) {
                                    $set('installment_amount', round($state / $installments, 2));
                                }
                            }),

                        Forms\Components\TextInput::make('installments_count')
                            ->label('Cantidad de cuotas')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(999)
                            ->live(debounce: 500)
                            ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                $total = (float) $get('total_amount');
                                if ((int) $state > 0 && $total > 0) {
                                    $set('installment_amount', round($total / (int) $state, 2));
                                }
                            }),

                        Forms\Components\TextInput::make('installment_amount')
                            ->label('Monto por cuota')
                            ->numeric()
                            ->prefix('$')
                            ->disabled()
                            ->dehydrated()
                            ->helperText('Calculado automáticamente'),

                        Forms\Components\TextInput::make('paid_installments')
                            ->label('Cuotas ya pagadas')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->helperText('Si ya pagaste algunas cuotas al registrar'),

                        Forms\Components\DatePicker::make('first_payment_date')
                            ->label('Fecha del primer pago')
                            ->required()
                            ->default(now()->startOfMonth())
                            ->displayFormat('d/m/Y')
                            ->native(false),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Notas')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->label('Notas adicionales')
                            ->rows(3)
                            ->placeholder('Detalles adicionales sobre esta compra...')
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Descripción')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (InstallmentPurchase $record) => $record->store),

                Tables\Columns\TextColumn::make('creditCard.name')
                    ->label('Tarjeta')
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                Tables\Columns\TextColumn::make('installments_progress')
                    ->label('Cuotas')
                    ->getStateUsing(fn (InstallmentPurchase $record) => "{$record->paid_installments}/{$record->installments_count}")
                    ->badge()
                    ->color(fn (InstallmentPurchase $record) => $record->is_completed ? 'success' : 'warning'),

                Tables\Columns\TextColumn::make('installment_amount')
                    ->label('Cuota')
                    ->money('ARS')
                    ->sortable(),

                Tables\Columns\TextColumn::make('remaining_amount')
                    ->label('Restante')
                    ->getStateUsing(fn (InstallmentPurchase $record) => $record->remaining_amount)
                    ->money('ARS')
                    ->color('danger'),

                Tables\Columns\TextColumn::make('next_payment_date')
                    ->label('Próx. cuota')
                    ->getStateUsing(fn (InstallmentPurchase $record) => $record->next_payment_date?->format('d/m/Y') ?? '—')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estado')
                    ->getStateUsing(fn (InstallmentPurchase $record) => $record->is_completed ? 'Completada' : 'Activa')
                    ->colors([
                        'success' => 'Completada',
                        'warning' => 'Activa',
                    ]),
            ])
            ->defaultSort('first_payment_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('credit_card_id')
                    ->label('Tarjeta')
                    ->options(CreditCard::pluck('name', 'id'))
                    ->searchable(),

                Tables\Filters\Filter::make('active')
                    ->label('Solo activas')
                    ->query(fn ($query) => $query->active()),

                Tables\Filters\Filter::make('completed')
                    ->label('Solo completadas')
                    ->query(fn ($query) => $query->whereColumn('paid_installments', '>=', 'installments_count')),
            ])
            ->actions([
                Tables\Actions\Action::make('mark_paid')
                    ->label('Marcar cuota pagada')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (InstallmentPurchase $record) => ! $record->is_completed)
                    ->action(function (InstallmentPurchase $record) {
                        $record->increment('paid_installments');
                        Notification::make()
                            ->title('Cuota marcada como pagada')
                            ->body("Cuota {$record->paid_installments}/{$record->installments_count}")
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(false),

                Tables\Actions\Action::make('unmark_paid')
                    ->label('Desmarcar cuota')
                    ->icon('heroicon-o-x-circle')
                    ->color('warning')
                    ->visible(fn (InstallmentPurchase $record) => $record->paid_installments > 0)
                    ->action(function (InstallmentPurchase $record) {
                        $record->decrement('paid_installments');
                        Notification::make()
                            ->title('Cuota desmarcada')
                            ->warning()
                            ->send();
                    })
                    ->requiresConfirmation(false),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInstallmentPurchases::route('/'),
            'create' => Pages\CreateInstallmentPurchase::route('/create'),
            'edit' => Pages\EditInstallmentPurchase::route('/{record}/edit'),
        ];
    }
}
