<?php

namespace App\Filament\Resources\TransactionTemplateResource\Pages;

use App\Filament\Resources\TransactionTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTransactionTemplate extends EditRecord
{
    protected static string $resource = TransactionTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
