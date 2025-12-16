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
                Forms\Components\Section::make('Información Básica')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('🏦 Nombre de la cuenta')
                            ->required()
                            ->maxLength(100)
                            ->placeholder('Ej: Galicia, MercadoPago, Efectivo')
                            ->columnSpan(2),

                        Forms\Components\Select::make('account_type')
                            ->label('📋 Tipo de Cuenta')
                            ->options([
                                'checking' => '🏦 Cuenta Corriente',
                                'savings' => '💰 Cuenta de Ahorro',
                                'cash' => '💵 Efectivo',
                                'credit_card' => '💳 Tarjeta de Crédito',
                                'investment' => '📈 Inversión',
                                'wallet' => '👛 Billetera Digital',
                            ])
                            ->required()
                            ->default('checking')
                            ->live()
                            ->helperText('Define el tipo de cuenta para mejor organización'),

                        Forms\Components\Toggle::make('include_in_totals')
                            ->label('📊 Incluir en totales')
                            ->helperText('¿Esta cuenta debe incluirse en los cálculos de balance general?')
                            ->default(true)
                            ->inline(false),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Balance')
                    ->schema([
                        Forms\Components\TextInput::make('initial_balance')
                            ->label('💰 Saldo inicial')
                            ->numeric()
                            ->default(0)
                            ->prefix('$')
                            ->helperText('El balance inicial de esta cuenta al momento de crearla'),
                    ]),

                Forms\Components\Section::make('Personalización (Opcional)')
                    ->schema([
                        Forms\Components\TextInput::make('icon')
                            ->label('🎨 Icono personalizado')
                            ->placeholder('Ej: 💳, 🏦, 💵')
                            ->maxLength(10)
                            ->helperText('Emoji que representa esta cuenta (opcional, se usa el del tipo por defecto)'),

                        Forms\Components\ColorPicker::make('color')
                            ->label('🎨 Color personalizado')
                            ->helperText('Color para identificar esta cuenta (opcional)'),

                        Forms\Components\Textarea::make('description')
                            ->label('📝 Descripción')
                            ->placeholder('Detalles adicionales sobre esta cuenta...')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Cuenta')
                    ->formatStateUsing(function (Account $record): string {
                        return $record->account_icon . ' ' . $record->name;
                    })
                    ->sortable()
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\BadgeColumn::make('account_type')
                    ->label('Tipo')
                    ->formatStateUsing(function (Account $record): string {
                        return $record->type_name;
                    })
                    ->colors([
                        'primary' => 'checking',
                        'success' => 'savings',
                        'warning' => 'cash',
                        'danger' => 'credit_card',
                        'info' => 'wallet',
                        'secondary' => 'investment',
                    ]),

                Tables\Columns\TextColumn::make('initial_balance')
                    ->label('💰 Saldo inicial')
                    ->money('ARS')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('current_balance')
                    ->label('💳 Balance Actual')
                    ->getStateUsing(function (Account $record): float {
                        return $record->current_balance;
                    })
                    ->money('ARS')
                    ->color(fn ($state): string => $state >= 0 ? 'success' : 'danger')
                    ->weight('bold')
                    ->sortable(),

                Tables\Columns\IconColumn::make('include_in_totals')
                    ->label('En Totales')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('📅 Creada')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->filters([
                Tables\Filters\SelectFilter::make('account_type')
                    ->label('Tipo de Cuenta')
                    ->options([
                        'checking' => '🏦 Cuenta Corriente',
                        'savings' => '💰 Cuenta de Ahorro',
                        'cash' => '💵 Efectivo',
                        'credit_card' => '💳 Tarjeta de Crédito',
                        'investment' => '📈 Inversión',
                        'wallet' => '👛 Billetera Digital',
                    ]),
                Tables\Filters\TernaryFilter::make('include_in_totals')
                    ->label('Incluidas en totales')
                    ->placeholder('Todas')
                    ->trueLabel('Solo incluidas')
                    ->falseLabel('Solo excluidas'),
            ])
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
