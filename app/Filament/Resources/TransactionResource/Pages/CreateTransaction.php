<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;

class CreateTransaction extends CreateRecord
{
    protected static string $resource = TransactionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Limpiar el campo temporal is_transfer
        unset($data['is_transfer']);

        return $data;
    }

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        // Verificar si es transferencia por la categoría
        $category = Category::find($data['category_id']);

        if ($category && $category->name === 'Transferencia' && isset($data['to_account_id'])) {
            return $this->handleTransfer($data);
        }

        // Para transacciones normales (income/expense)
        // El type se deriva automáticamente de la categoría gracias al accessor
        unset($data['to_account_id']);

        return static::getModel()::create($data);
    }

    protected function handleTransfer(array $data): Transaction
    {
        return DB::transaction(function () use ($data) {
            $fromAccount = Account::findOrFail($data['account_id']);
            $toAccount = Account::findOrFail($data['to_account_id']);
            $amount = (float) $data['amount'];

            // Validaciones
            if ($amount <= 0) {
                throw new \Exception('El monto debe ser mayor a cero.');
            }

            if ($fromAccount->id === $toAccount->id) {
                throw new \Exception('No puedes transferir a la misma cuenta.');
            }

            // Categorías de transferencia
            $transferExpenseCategory = Category::firstOrCreate(
                ['name' => 'Transferencia'],
                ['type' => 'expense']
            );

            $transferIncomeCategory = Category::firstOrCreate(
                ['name' => 'Transferencia (Recibida)'],
                ['type' => 'income']
            );

            $referenceId = 'transfer_' . uniqid();

            // Transacción de salida (expense)
            $expenseTransaction = Transaction::create([
                'title' => $data['title'] ?: "Transferencia a {$toAccount->name}",
                'amount' => $amount,
                'category_id' => $transferExpenseCategory->id,
                'account_id' => $fromAccount->id,
                'date' => $data['date'] ?? now(),
                'description' => $data['description'] ?? "Transferencia desde {$fromAccount->name} a {$toAccount->name}",
                'reference_id' => $referenceId,
            ]);

            // Transacción de entrada (income)
            Transaction::create([
                'title' => $data['title'] ?: "Transferencia desde {$fromAccount->name}",
                'amount' => $amount,
                'category_id' => $transferIncomeCategory->id,
                'account_id' => $toAccount->id,
                'date' => $data['date'] ?? now(),
                'description' => $data['description'] ?? "Transferencia desde {$fromAccount->name} a {$toAccount->name}",
                'reference_id' => $referenceId,
            ]);

            return $expenseTransaction;
        });
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Transacción creada exitosamente';
    }
}
