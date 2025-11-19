<?php

namespace App\Filament\Resources\TransactionTemplateResource\Pages;

use App\Filament\Resources\TransactionTemplateResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTransactionTemplate extends CreateRecord
{
    protected static string $resource = TransactionTemplateResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
