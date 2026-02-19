<?php

namespace App\Filament\Resources\InstallmentPurchaseResource\Pages;

use App\Filament\Resources\InstallmentPurchaseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInstallmentPurchase extends EditRecord
{
    protected static string $resource = InstallmentPurchaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
