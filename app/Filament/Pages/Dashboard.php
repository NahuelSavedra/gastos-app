<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Dashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.dashboard';

    protected function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\BalanceOverview::class,
            \App\Filament\Widgets\TransactionsTable::class,
        ];
    }
}
