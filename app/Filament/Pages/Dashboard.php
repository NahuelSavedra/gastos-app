<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Dashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.dashboard';

    public function getWidgets(): array
    {
        return [
            // 🏆 PRIORIDAD 1: Totales generales (lo más importante)
            \App\Filament\Widgets\BalanceOverview::class,

            // 🏦 PRIORIDAD 2: Resumen de cuentas
            \App\Filament\Widgets\AccountsOverviewWidget::class,

            // 📊 PRIORIDAD 3: Gastos por categorías
            \App\Filament\Widgets\ExpenseCategoriesWidget::class,

            // 📋 PRIORIDAD 4: Transacciones (menos importante, al final)
            \App\Filament\Widgets\TransactionsTable::class,
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
