<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use App\Models\Category;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTransaction extends CreateRecord
{
    protected static string $resource = TransactionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['date'])) {
            $data['date'] = now()->toDateString();
        }

        if (! empty($data['category_id'])) {
            $data['type'] = Category::query()->whereKey($data['category_id'])->value('type') ?? $data['type'] ?? null;
        }

        return $data;
    }

}
