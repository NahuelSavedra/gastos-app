<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CreditCardResource\Pages;
use App\Models\Account;
use App\Models\CreditCard;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CreditCardResource extends Resource
{
    protected static ?string $model = CreditCard::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationLabel = 'Tarjetas de Crédito';

    protected static ?string $modelLabel = 'Tarjeta de Crédito';

    protected static ?string $pluralModelLabel = 'Tarjetas de Crédito';

    protected static ?string $navigationGroup = 'Finanzas';

    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre de la tarjeta')
                            ->required()
                            ->maxLength(100)
                            ->placeholder('Ej: VISA Banco Galicia')
                            ->columnSpan(2),

                        Forms\Components\Select::make('account_id')
                            ->label('Cuenta vinculada')
                            ->options(
                                Account::where('account_type', 'credit_card')
                                    ->pluck('name', 'id')
                            )
                            ->nullable()
                            ->searchable()
                            ->placeholder('Seleccionar cuenta (opcional)')
                            ->helperText('Cuenta de tipo Tarjeta de Crédito asociada'),

                        Forms\Components\TextInput::make('last_four')
                            ->label('Últimos 4 dígitos')
                            ->maxLength(4)
                            ->minLength(4)
                            ->numeric()
                            ->placeholder('1234')
                            ->helperText('Opcional'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Límite y Fechas')
                    ->schema([
                        Forms\Components\TextInput::make('credit_limit')
                            ->label('Límite de crédito')
                            ->numeric()
                            ->required()
                            ->default(0)
                            ->prefix('$')
                            ->minValue(0),

                        Forms\Components\TextInput::make('closing_day')
                            ->label('Día de cierre')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(31)
                            ->helperText('Día del mes en que cierra el resumen'),

                        Forms\Components\TextInput::make('due_day')
                            ->label('Día de vencimiento')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(31)
                            ->helperText('Día del mes en que vence el pago'),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Estado')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Tarjeta activa')
                            ->default(true)
                            ->inline(false),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Tarjeta')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (CreditCard $record) => $record->last_four ? "···· {$record->last_four}" : null),

                Tables\Columns\TextColumn::make('account.name')
                    ->label('Cuenta')
                    ->badge()
                    ->color('primary')
                    ->placeholder('Sin cuenta'),

                Tables\Columns\TextColumn::make('credit_limit')
                    ->label('Límite')
                    ->money('ARS')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_debt')
                    ->label('Deuda total')
                    ->getStateUsing(fn (CreditCard $record) => $record->total_debt)
                    ->money('ARS')
                    ->color('danger'),

                Tables\Columns\TextColumn::make('monthly_payment')
                    ->label('Pago mensual')
                    ->getStateUsing(fn (CreditCard $record) => $record->monthly_payment)
                    ->money('ARS')
                    ->color('warning'),

                Tables\Columns\TextColumn::make('due_day')
                    ->label('Vence día')
                    ->suffix(' de cada mes')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activa')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger'),
            ])
            ->defaultSort('name')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Estado')
                    ->placeholder('Todas')
                    ->trueLabel('Solo activas')
                    ->falseLabel('Solo inactivas'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Ver Detalle'),
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
            'index' => Pages\ListCreditCards::route('/'),
            'create' => Pages\CreateCreditCard::route('/create'),
            'view' => Pages\ViewCreditCard::route('/{record}'),
            'edit' => Pages\EditCreditCard::route('/{record}/edit'),
        ];
    }
}
