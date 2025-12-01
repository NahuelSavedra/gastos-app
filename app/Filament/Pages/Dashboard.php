<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AccountsOverviewWidget;
use App\Filament\Widgets\BalanceOverview;
use App\Filament\Widgets\ExpenseCategoriesWidget;
use App\Filament\Widgets\QuickTransactionsWidget;
use App\Filament\Widgets\QuickTransfers;
use App\Filament\Widgets\TransactionsTable;
use Filament\Pages\Page;

class Dashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.dashboard';

    public function getWidgets(): array
    {
        return [
            // 🏆 PRIORIDAD 1: Totales generales (lo más importante)
            BalanceOverview::class,

            // 🏦 PRIORIDAD 2: Resumen de cuentas
            AccountsOverviewWidget::class,

            // 📊 PRIORIDAD 3: Gastos por categorías
            ExpenseCategoriesWidget::class,

            // 📋 PRIORIDAD 4: Transacciones (menos importante, al final)
            TransactionsTable::class,

            QuickTransactionsWidget::class,

            QuickTransfers::class
        ];
    }

    /**
     * Configuración de columnas responsive
     */
    public function getColumns(): int | string | array
    {
        return [
            'sm' => 1,  // Móvil: 1 columna
            'md' => 2,  // Tablet: 2 columnas
            'lg' => 2,  // Desktop: 2 columnas
            'xl' => 2,  // Pantalla grande: 2 columnas
        ];
    }
}
