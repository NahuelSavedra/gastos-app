<?php

namespace App\Filament\Resources\TransferTemplateResource\Pages;

use App\Filament\Resources\TransferTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTransferTemplates extends ListRecords
{
    protected static string $resource = TransferTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
