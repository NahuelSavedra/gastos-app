<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use App\Models\Account;
use App\Models\Category;
use App\Services\Import\TransactionImportService;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;

class ListTransactions extends ListRecords
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getImportAction(),
            Actions\Action::make('batch_create')
                ->label('Crear Múltiples')
                ->icon('heroicon-o-squares-plus')
                ->color('primary')
                ->action(fn () => $this->dispatch('open-batch-modal')),
            Actions\CreateAction::make(),
        ];
    }

    protected function getImportAction(): Actions\Action
    {
        return Actions\Action::make('import')
            ->label('Importar')
            ->icon('heroicon-o-arrow-up-tray')
            ->color('success')
            ->form([
                Select::make('parser')
                    ->label('Fuente')
                    ->options(fn () => app(TransactionImportService::class)->getAvailableParsers())
                    ->required()
                    ->default('mercadopago')
                    ->live(),

                Select::make('account_id')
                    ->label('Cuenta destino')
                    ->options(Account::pluck('name', 'id'))
                    ->required()
                    ->searchable(),

                FileUpload::make('import_file')
                    ->label('Archivo')
                    ->acceptedFileTypes([
                        'text/csv',
                        'application/vnd.ms-excel',
                        '.csv',
                        'application/pdf',
                        '.pdf',
                    ])
                    ->required()
                    ->disk('local')
                    ->directory('imports')
                    ->visibility('private')
                    ->helperText('CSV para MercadoPago, PDF para Galicia'),

                Select::make('default_category_id')
                    ->label('Categoría por defecto')
                    ->options(
                        Category::where('type', 'expense')
                            ->pluck('name', 'id')
                    )
                    ->searchable()
                    ->helperText('Para transacciones sin regla de categorización'),

                Toggle::make('auto_create_categories')
                    ->label('Auto-crear categorías')
                    ->helperText('Crear categorías automáticamente desde el tipo de transacción')
                    ->default(true),

                Toggle::make('preview')
                    ->label('Solo previsualizar')
                    ->helperText('Ver qué se importaría sin guardar')
                    ->default(false),
            ])
            ->action(function (array $data): void {
                $importService = app(TransactionImportService::class);

                $filePath = Storage::disk('local')->path($data['import_file']);

                $result = $importService->import(
                    $filePath,
                    $data['parser'],
                    $data['account_id'],
                    $data['default_category_id'] ?? null,
                    $data['preview'] ?? false,
                    $data['auto_create_categories'] ?? false
                );

                // Clean up uploaded file
                Storage::disk('local')->delete($data['import_file']);

                $isPreview = $data['preview'] ?? false;
                $title = $isPreview ? 'Previsualización completada' : 'Importación completada';

                if ($result->getFailedCount() > 0 && $result->getImportedCount() === 0) {
                    Notification::make()
                        ->title('Error en importación')
                        ->body($result->getSummaryMessage())
                        ->danger()
                        ->send();
                } else {
                    $notification = Notification::make()
                        ->title($title)
                        ->body($result->getSummaryMessage())
                        ->success();

                    // Add action to configure rules after successful import
                    if (! $isPreview && $result->getImportedCount() > 0) {
                        $notification->actions([
                            \Filament\Notifications\Actions\Action::make('configure_rules')
                                ->label('Configurar reglas')
                                ->url(route('filament.app.resources.import-category-rules.index'))
                                ->button(),
                        ]);
                    }

                    $notification->send();
                }

                if (! $isPreview) {
                    $this->resetTable();
                }
            });
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
