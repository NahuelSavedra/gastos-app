<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionTemplateResource\Pages;
use App\Models\Account;
use App\Models\Category;
use App\Models\TransactionTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TransactionTemplateResource extends Resource
{
    protected static ?string $model = TransactionTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';

    protected static ?string $navigationGroup = 'Finanzas';

    protected static ?string $navigationLabel = 'Templates';

    protected static ?string $modelLabel = 'Template';

    protected static ?string $pluralModelLabel = 'Templates';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información Básica')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('📝 Nombre del Template')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('ej: Alquiler, Telecentro, Supermercado Día...')
                                    ->helperText('Nombre identificatorio para este template'),

                                Forms\Components\TextInput::make('title')
                                    ->label('🏷️ Título de la Transacción')
                                    ->maxLength(255)
                                    ->placeholder('Dejar vacío para usar el nombre')
                                    ->helperText('Título que aparecerá en la transacción creada'),
                            ]),

                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('amount')
                                    ->label('💵 Monto')
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->placeholder('0.00')
                                    ->helperText('Dejar vacío si el monto varía cada vez'),

                                Forms\Components\Select::make('category_id')
                                    ->label('🏷️ Categoría')
                                    ->options(function () {
                                        return Category::orderBy('type')
                                            ->orderBy('name')
                                            ->get()
                                            ->mapWithKeys(function ($category) {
                                                $icon = $category->type === 'income' ? '📈' : '📉';

                                                return [$category->id => "{$icon} {$category->name}"];
                                            });
                                    })
                                    ->required()
                                    ->searchable()
                                    ->preload(),

                                Forms\Components\Select::make('account_id')
                                    ->label('🏦 Cuenta')
                                    ->options(Account::pluck('name', 'id'))
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                            ]),

                        Forms\Components\Textarea::make('description')
                            ->label('📝 Descripción')
                            ->maxLength(500)
                            ->rows(2)
                            ->placeholder('Descripción opcional para la transacción...'),
                    ]),

                Forms\Components\Section::make('Configuración de Recurrencia')
                    ->description('Define si este template se repite automáticamente')
                    ->schema([
                        Forms\Components\Toggle::make('is_recurring')
                            ->label('🔄 Es recurrente')
                            ->live()
                            ->helperText('Activa esto si el gasto se repite periódicamente'),

                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\Select::make('recurrence_type')
                                    ->label('📅 Tipo de Recurrencia')
                                    ->options([
                                        'monthly' => 'Mensual',
                                        'weekly' => 'Semanal',
                                        'yearly' => 'Anual',
                                    ])
                                    ->required(fn (Get $get) => $get('is_recurring'))
                                    ->visible(fn (Get $get) => $get('is_recurring'))
                                    ->live(),

                                Forms\Components\Select::make('recurrence_day')
                                    ->label(fn (Get $get) => match ($get('recurrence_type')) {
                                        'monthly' => '📆 Día del Mes',
                                        'weekly' => '📆 Día de la Semana',
                                        'yearly' => '📆 Fecha (MM-DD)',
                                        default => '📆 Día',
                                    })
                                    ->options(fn (Get $get) => match ($get('recurrence_type')) {
                                        'monthly' => collect(range(1, 31))->mapWithKeys(fn ($d) => [$d => "Día $d"]),
                                        'weekly' => [
                                            1 => 'Lunes',
                                            2 => 'Martes',
                                            3 => 'Miércoles',
                                            4 => 'Jueves',
                                            5 => 'Viernes',
                                            6 => 'Sábado',
                                            7 => 'Domingo',
                                        ],
                                        'yearly' => collect(range(1, 12))->flatMap(function ($month) {
                                            $days = cal_days_in_month(CAL_GREGORIAN, $month, date('Y'));

                                            return collect(range(1, $days))->mapWithKeys(function ($day) use ($month) {
                                                $date = sprintf('%02d-%02d', $month, $day);

                                                return [$date => date('j \d\e F', mktime(0, 0, 0, $month, $day))];
                                            });
                                        }),
                                        default => [],
                                    })
                                    ->required(fn (Get $get) => $get('is_recurring'))
                                    ->visible(fn (Get $get) => $get('is_recurring') && filled($get('recurrence_type')))
                                    ->searchable(),

                                Forms\Components\Toggle::make('auto_create')
                                    ->label('⚡ Crear Automáticamente')
                                    ->visible(fn (Get $get) => $get('is_recurring'))
                                    ->helperText('Si está activo, se creará automáticamente en la fecha indicada'),
                            ]),

                        Forms\Components\Placeholder::make('recurrence_preview')
                            ->label('📋 Resumen de Recurrencia')
                            ->content(function (Get $get): string {
                                if (! $get('is_recurring')) {
                                    return '⚪ No es recurrente';
                                }

                                $type = $get('recurrence_type');
                                $day = $get('recurrence_day');
                                $auto = $get('auto_create') ? 'Se creará automáticamente' : 'Deberás crearla manualmente';

                                $schedule = match ($type) {
                                    'monthly' => "Cada día $day del mes",
                                    'weekly' => 'Cada '.['', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'][$day ?? 0],
                                    'yearly' => 'Cada año el '.($day ? date('j \d\e F', strtotime("2024-$day")) : ''),
                                    default => 'Sin configurar',
                                };

                                return "🔄 {$schedule}. {$auto}";
                            })
                            ->visible(fn (Get $get) => $get('is_recurring') && filled($get('recurrence_type'))),
                    ]),

                Forms\Components\Section::make('Estado')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('✅ Template Activo')
                            ->default(true)
                            ->helperText('Desactiva este template si no quieres usarlo temporalmente'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['category', 'account']))
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Categoría')
                    ->badge()
                    ->color(fn ($record) => $record->category->type === 'income' ? 'success' : 'danger')
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Monto')
                    ->money('ARS', true)
                    ->sortable()
                    ->placeholder('Variable'),

                Tables\Columns\TextColumn::make('account.name')
                    ->label('Cuenta')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_recurring')
                    ->label('Recurrente')
                    ->boolean()
                    ->trueIcon('heroicon-o-arrow-path')
                    ->falseIcon('heroicon-o-minus')
                    ->trueColor('info'),

                Tables\Columns\TextColumn::make('recurrence_info')
                    ->label('Frecuencia')
                    ->getStateUsing(function ($record): string {
                        if (! $record->is_recurring) {
                            return '-';
                        }

                        return match ($record->recurrence_type) {
                            'monthly' => "Día {$record->recurrence_day}",
                            'weekly' => ['', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'][$record->recurrence_day] ?? '-',
                            'yearly' => date('d/m', strtotime("2024-{$record->recurrence_day}")),
                            default => '-',
                        };
                    }),

                Tables\Columns\IconColumn::make('auto_create')
                    ->label('Auto')
                    ->boolean()
                    ->trueIcon('heroicon-o-bolt')
                    ->falseIcon('heroicon-o-hand-raised')
                    ->trueColor('warning'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Estado')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
            ])
            ->defaultSort('name')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_recurring')
                    ->label('Recurrentes')
                    ->placeholder('Todos')
                    ->trueLabel('Solo recurrentes')
                    ->falseLabel('Solo únicos'),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Activos')
                    ->placeholder('Todos')
                    ->trueLabel('Solo activos')
                    ->falseLabel('Solo inactivos'),
            ])
            ->actions([
                Tables\Actions\Action::make('use_template')
                    ->label('Usar')
                    ->icon('heroicon-o-plus-circle')
                    ->color('success')
                    ->form([
                        Forms\Components\TextInput::make('amount')
                            ->label('Monto')
                            ->numeric()
                            ->prefix('$')
                            ->required()
                            ->default(fn ($record) => $record->amount),

                        Forms\Components\DatePicker::make('date')
                            ->label('Fecha')
                            ->required()
                            ->default(now()),

                        Forms\Components\Textarea::make('description')
                            ->label('Descripción (opcional)')
                            ->rows(2),
                    ])
                    ->action(function (TransactionTemplate $record, array $data) {
                        $record->createTransaction($data);

                        if ($record->is_recurring) {
                            $record->update(['last_generated_at' => now()]);
                        }

                        return redirect()->to(TransactionResource::getUrl('index'));
                    })
                    ->successNotificationTitle('Transacción creada desde template'),

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
            'index' => Pages\ListTransactionTemplates::route('/'),
            'create' => Pages\CreateTransactionTemplate::route('/create'),
            'edit' => Pages\EditTransactionTemplate::route('/{record}/edit'),
        ];
    }
}
