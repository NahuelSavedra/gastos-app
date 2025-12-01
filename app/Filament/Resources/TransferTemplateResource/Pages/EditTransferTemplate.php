<?php

namespace App\Filament\Resources\TransferTemplateResource\Pages;

use App\Filament\Resources\TransferTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTransferTemplate extends EditRecord
{
    protected static string $resource = TransferTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
