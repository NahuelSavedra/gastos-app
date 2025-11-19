<?php

namespace App\Filament\Resources\TransactionTemplateResource\Pages;

use App\Filament\Resources\TransactionTemplateResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

class ListTransactionTemplates extends ListRecords
{
    protected static string $resource = TransactionTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
