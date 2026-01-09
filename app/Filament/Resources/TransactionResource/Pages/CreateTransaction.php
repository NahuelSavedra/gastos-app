<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

class CreateTransaction extends CreateRecord
{
    protected static string $resource = TransactionResource::class;

    // Property to track if we should redirect to create another
    public bool $createAnother = false;

    protected function getRedirectUrl(): string
    {
        // If creating another, stay on the create page
        if ($this->createAnother) {
            return $this->getResource()::getUrl('create');
        }
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('clearAccountContext')
                ->label('Limpiar cuenta guardada')
                ->color('gray')
                ->icon('heroicon-o-x-circle')
                ->visible(fn () => Session::has('last_transaction_account_id'))
                ->action(function () {
                    Session::forget('last_transaction_account_id');
                    $this->fillForm();
                }),
        ];
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction(),
            $this->getCreateAnotherFormAction(),
            $this->getCancelFormAction(),
        ];
    }

    protected function getCreateFormAction(): Action
    {
        return Action::make('create')
            ->label('Crear')
            ->submit('create')
            ->keyBindings(['mod+s']);
    }

    protected function getCreateAnotherFormAction(): Action
    {
        return Action::make('createAnother')
            ->label('Crear y agregar otro')
            ->action(function () {
                $this->createAnother = true;
                $this->create();
            })
            ->keyBindings(['mod+shift+s'])
            ->color('gray');
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Restore last used account from session
        $lastAccountId = Session::get('last_transaction_account_id');
        if ($lastAccountId && Account::where('id', $lastAccountId)->exists()) {
            $data['account_id'] = $lastAccountId;
        }

        return $data;
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Save the account context to session for consecutive creations
        if (isset($data['account_id'])) {
            Session::put('last_transaction_account_id', $data['account_id']);
        }

        // Limpiar campos temporales
        unset($data['is_transfer'], $data['quick_amount']);

        return $data;
    }

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        // Optimización: Cargar categoría con un solo query
        $category = $this->getCachedCategory($data['category_id']);

        if ($category && $category->name === 'Transferencia' && isset($data['to_account_id'])) {
            return $this->handleTransfer($data, $category);
        }

        // Transacción normal
        unset($data['to_account_id']);

        return static::getModel()::create($data);
    }

    protected function handleTransfer(array $data, Category $transferExpenseCategory): Transaction
    {
        return DB::transaction(function () use ($data, $transferExpenseCategory) {
            // Optimización: Una sola query para ambas cuentas
            $accounts = Account::whereIn('id', [
                $data['account_id'],
                $data['to_account_id']
            ])->get()->keyBy('id');

            $fromAccount = $accounts->get($data['account_id']);
            $toAccount = $accounts->get($data['to_account_id']);

            if (!$fromAccount || !$toAccount) {
                throw new \Exception('Una de las cuentas no existe.');
            }

            $amount = (float) $data['amount'];

            // Validaciones rápidas
            if ($amount <= 0) {
                throw new \Exception('El monto debe ser mayor a cero.');
            }

            if ($fromAccount->id === $toAccount->id) {
                throw new \Exception('No puedes transferir a la misma cuenta.');
            }

            // Obtener o crear categoría de ingreso (cacheada)
            $transferIncomeCategory = $this->getOrCreateTransferIncomeCategory();

            $referenceId = 'transfer_' . uniqid();
            $date = $data['date'] ?? now();
            $baseTitle = $data['title'] ?: 'Transferencia';
            $baseDescription = $data['description'] ?? "Transferencia entre cuentas";

            // Optimización: Inserción masiva con una sola query
            $transactions = [
                [
                    'title' => "{$baseTitle} a {$toAccount->name}",
                    'amount' => $amount,
                    'category_id' => $transferExpenseCategory->id,
                    'account_id' => $fromAccount->id,
                    'date' => $date,
                    'description' => $baseDescription,
                    'reference_id' => $referenceId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'title' => "{$baseTitle} desde {$fromAccount->name}",
                    'amount' => $amount,
                    'category_id' => $transferIncomeCategory->id,
                    'account_id' => $toAccount->id,
                    'date' => $date,
                    'description' => $baseDescription,
                    'reference_id' => $referenceId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ];

            Transaction::insert($transactions);

            // Retornar la primera transacción (necesitamos el modelo)
            return Transaction::where('reference_id', $referenceId)
                ->where('account_id', $fromAccount->id)
                ->first();
        });
    }

    protected function getCachedCategory(int $categoryId): ?Category
    {
        return Cache::remember(
            "category_{$categoryId}",
            3600,
            fn() => Category::find($categoryId)
        );
    }

    protected function getOrCreateTransferIncomeCategory(): Category
    {
        return Cache::remember('transfer_income_category', 3600, function () {
            return Category::firstOrCreate(
                ['name' => 'Transferencia (Recibida)'],
                ['type' => 'income']
            );
        });
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Transacción creada exitosamente';
    }
}
