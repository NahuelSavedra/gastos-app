<?php

namespace App\Filament\Resources\ImportCategoryRuleResource\Pages;

use App\Filament\Resources\ImportCategoryRuleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditImportCategoryRule extends EditRecord
{
    protected static string $resource = ImportCategoryRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
