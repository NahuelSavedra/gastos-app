<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransferTemplateResource\Pages;
use App\Models\Account;
use App\Models\TransferTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TransferTemplateResource extends Resource
{
    protected static ?string $model = TransferTemplate::class;
    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';
    protected static ?string $navigationLabel = 'Templates de Transferencia';
    protected static ?string $navigationGroup = 'Configuración';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Información del Template')
                ->schema([
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\TextInput::make('name')
                                ->label('Nombre del Template')
                                ->placeholder('Ej: Galicia → MP')
                                ->required()
                                ->maxLength(255)
                                ->columnSpan(2),

                            Forms\Components\Select::make('from_account_id')
                                ->label('Cuenta Origen')
                                ->relationship('fromAccount', 'name')
                                ->searchable()
                                ->required()
                                ->live()
                                ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                    // Auto-generar nombre si ambas cuentas están seleccionadas
                                    if ($state && $get('to_account_id')) {
                                        $from = Account::find($state);
                                        $to = Account::find($get('to_account_id'));
                                        if ($from && $to && !$get('name')) {
                                            $set('name', "{$from->name} → {$to->name}");
                                        }
                                    }
                                }),

                            Forms\Components\Select::make('to_account_id')
                                ->label('Cuenta Destino')
                                ->relationship('toAccount', 'name')
                                ->searchable()
                                ->required()
                                ->live()
                                ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                    // Auto-generar nombre si ambas cuentas están seleccionadas
                                    if ($state && $get('from_account_id')) {
                                        $from = Account::find($get('from_account_id'));
                                        $to = Account::find($state);
                                        if ($from && $to && !$get('name')) {
                                            $set('name', "{$from->name} → {$to->name}");
                                        }
                                    }
                                }),

                            Forms\Components\TextInput::make('default_amount')
                                ->label('Monto por Defecto (Opcional)')
                                ->numeric()
                                ->prefix('$')
                                ->placeholder('Dejar vacío para ingresar cada vez')
                                ->minValue(0)
                                ->step(0.01),

                            Forms\Components\TextInput::make('order')
                                ->label('Orden')
                                ->numeric()
                                ->default(0)
                                ->minValue(0)
                                ->helperText('Orden de aparición en el widget'),
                        ]),
                ]),

            Forms\Components\Section::make('Personalización')
                ->schema([
                    Forms\Components\Grid::make(3)
                        ->schema([
                            Forms\Components\Select::make('icon')
                                ->label('Ícono')
                                ->options([
                                    'heroicon-o-arrow-right-circle' => 'Flecha Circular',
                                    'heroicon-o-arrow-path' => 'Flechas Circular',
                                    'heroicon-o-banknotes' => 'Billetes',
                                    'heroicon-o-credit-card' => 'Tarjeta',
                                    'heroicon-o-building-library' => 'Banco',
                                ])
                                ->default('heroicon-o-arrow-right-circle')
                                ->required(),

                            Forms\Components\Select::make('color')
                                ->label('Color')
                                ->options([
                                    'primary' => 'Primario (Azul)',
                                    'success' => 'Éxito (Verde)',
                                    'warning' => 'Advertencia (Amarillo)',
                                    'danger' => 'Peligro (Rojo)',
                                    'info' => 'Info (Cyan)',
                                    'gray' => 'Gris',
                                ])
                                ->default('primary')
                                ->required(),

                            Forms\Components\Toggle::make('is_active')
                                ->label('Activo')
                                ->default(true)
                                ->inline(false),
                        ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order')
                    ->label('#')
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('fromAccount.name')
                    ->label('Desde')
                    ->badge()
                    ->color('danger'),

                Tables\Columns\IconColumn::make('arrow')
                    ->label('')
                    ->icon('heroicon-o-arrow-right')
                    ->color('gray'),

                Tables\Columns\TextColumn::make('toAccount.name')
                    ->label('Hasta')
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('default_amount')
                    ->label('Monto Default')
                    ->money('ARS')
                    ->placeholder('Variable'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Activo')
                    ->placeholder('Todos')
                    ->trueLabel('Solo activos')
                    ->falseLabel('Solo inactivos'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('order')
            ->reorderable('order');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransferTemplates::route('/'),
            'create' => Pages\CreateTransferTemplate::route('/create'),
            'edit' => Pages\EditTransferTemplate::route('/{record}/edit'),
        ];
    }
}
