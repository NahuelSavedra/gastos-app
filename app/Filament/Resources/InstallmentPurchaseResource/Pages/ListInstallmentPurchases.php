<?php

namespace App\Filament\Resources\InstallmentPurchaseResource\Pages;

use App\Filament\Resources\InstallmentPurchaseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInstallmentPurchases extends ListRecords
{
    protected static string $resource = InstallmentPurchaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Nueva Compra en Cuotas'),
        ];
    }
}
