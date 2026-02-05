<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AccountsOverviewWidget;
use App\Filament\Widgets\BalanceOverview;
use App\Filament\Widgets\ExpenseCategoriesWidget;
use App\Filament\Widgets\TransactionsTable;
use Carbon\Carbon;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?string $title = 'Dashboard';

    public function filtersForm(Form $form): Form
    {
        $months = [];
        $currentDate = Carbon::now();

        // Generar últimos 12 meses
        for ($i = 0; $i < 12; $i++) {
            $date = $currentDate->copy()->subMonths($i);
            $key = $date->format('Y-m');
            $label = $date->translatedFormat('F Y');
            $months[$key] = ucfirst($label);
        }

        return $form
            ->schema([
                Select::make('month')
                    ->label('Período')
                    ->options($months)
                    ->default($currentDate->format('Y-m'))
                    ->selectablePlaceholder(false)
                    ->native(false),
            ]);
    }

    public function getWidgets(): array
    {
        return [
            // 🏆 PRIORIDAD 1: Totales generales
            BalanceOverview::class,

            // 🔮 PRIORIDAD 1.5: Balance proyectado con gastos pendientes
            \App\Filament\Widgets\ProjectedBalanceWidget::class,

            // 🏦 PRIORIDAD 2: Resumen de cuentas
            AccountsOverviewWidget::class,

            // 📊 PRIORIDAD 3: Gastos por categorías
            ExpenseCategoriesWidget::class,

            // 📋 PRIORIDAD 4: Transacciones recientes
            TransactionsTable::class,

            // 📊 PRIORIDAD 5: Promedios por categoría
            \App\Filament\Widgets\CategoryAveragesWidget::class,
        ];
    }

    public function getColumns(): int|string|array
    {
        return [
            'sm' => 1,
            'md' => 2,
            'lg' => 2,
            'xl' => 2,
        ];
    }
}
