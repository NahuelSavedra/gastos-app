<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\View\View;

class ListTransactions extends ListRecords
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('batch_create')
                ->label('Crear Múltiples')
                ->icon('heroicon-o-squares-plus')
                ->color('primary')
                ->action(fn () => $this->dispatch('open-batch-modal')),
            Actions\CreateAction::make(),
        ];
    }

    public function getFooter(): ?View
    {
        return view('filament.resources.transaction-resource.pages.list-transactions-footer');
    }

    #[\Livewire\Attributes\On('batch-transactions-created')]
    public function refreshTable(): void
    {
        $this->resetTable();
    }
}
