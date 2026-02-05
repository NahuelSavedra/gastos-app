<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ImportCategoryRuleResource\Pages;
use App\Models\Category;
use App\Models\ImportCategoryRule;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ImportCategoryRuleResource extends Resource
{
    protected static ?string $model = ImportCategoryRule::class;

    protected static ?string $navigationIcon = 'heroicon-o-funnel';

    protected static ?string $navigationGroup = 'Configuración';

    protected static ?string $modelLabel = 'Regla de Categorización';

    protected static ?string $pluralModelLabel = 'Reglas de Categorización';

    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Regla')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Ej: Delivery a Comida'),

                        Forms\Components\Select::make('source')
                            ->label('Fuente')
                            ->options([
                                'mercadopago' => 'MercadoPago',
                                'galicia' => 'Banco Galicia',
                            ])
                            ->placeholder('Todas las fuentes')
                            ->helperText('Dejar vacío para aplicar a todas las fuentes'),

                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\Select::make('field')
                                    ->label('Campo')
                                    ->required()
                                    ->options(ImportCategoryRule::getAvailableFields()),

                                Forms\Components\Select::make('operator')
                                    ->label('Operador')
                                    ->required()
                                    ->options(ImportCategoryRule::getOperators()),

                                Forms\Components\TextInput::make('value')
                                    ->label('Valor')
                                    ->required()
                                    ->maxLength(255),
                            ]),

                        Forms\Components\Select::make('category_id')
                            ->label('Categoría')
                            ->required()
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->getOptionLabelFromRecordUsing(fn (Category $record) => "{$record->name} ({$record->type})"),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('priority')
                                    ->label('Prioridad')
                                    ->numeric()
                                    ->default(0)
                                    ->helperText('Mayor número = mayor prioridad'),

                                Forms\Components\Toggle::make('is_active')
                                    ->label('Activa')
                                    ->default(true),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('source')
                    ->label('Fuente')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => $state ? ucfirst($state) : 'Todas'),

                Tables\Columns\TextColumn::make('field')
                    ->label('Campo')
                    ->formatStateUsing(fn (string $state) => ImportCategoryRule::getAvailableFields()[$state] ?? $state),

                Tables\Columns\TextColumn::make('operator')
                    ->label('Operador')
                    ->formatStateUsing(fn (string $state) => ImportCategoryRule::getOperators()[$state] ?? $state),

                Tables\Columns\TextColumn::make('value')
                    ->label('Valor')
                    ->searchable(),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Categoría')
                    ->badge()
                    ->color(fn ($record) => $record->category?->type === 'income' ? 'success' : 'danger'),

                Tables\Columns\TextColumn::make('priority')
                    ->label('Prioridad')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activa')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('source')
                    ->label('Fuente')
                    ->options([
                        'mercadopago' => 'MercadoPago',
                        'galicia' => 'Banco Galicia',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Activa'),
                Tables\Filters\Filter::make('created_at')
                    ->label('Fecha de creación')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Desde'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Hasta'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['created_from'], fn ($q) => $q->whereDate('created_at', '>=', $data['created_from']))
                            ->when($data['created_until'], fn ($q) => $q->whereDate('created_at', '<=', $data['created_until']));
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListImportCategoryRules::route('/'),
            'create' => Pages\CreateImportCategoryRule::route('/create'),
            'edit' => Pages\EditImportCategoryRule::route('/{record}/edit'),
        ];
    }
}
