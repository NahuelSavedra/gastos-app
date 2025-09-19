<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;

class CreateTransaction extends CreateRecord
{
    protected static string $resource = TransactionResource::class;

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        // Si es transferencia, manejamos de manera especial
        if ($data['transaction_type'] === 'transfer') {
            return $this->handleTransfer($data);
        }

        // Para transacciones normales
        $data['type'] = $data['transaction_type'];
        unset($data['transaction_type'], $data['from_account_id'], $data['to_account_id']);

        return static::getModel()::create($data);
    }

    protected function handleTransfer(array $data): Transaction
    {
        return DB::transaction(function () use ($data) {
            $fromAccount = Account::findOrFail($data['from_account_id']);
            $toAccount = Account::findOrFail($data['to_account_id']);
            $amount = (float) $data['amount'];

            // Validar que el monto sea válido
            if ($amount <= 0) {
                throw new \Exception('El monto debe ser mayor a cero.');
            }

            // Crear o encontrar categoría de transferencia
            $transferCategory = Category::firstOrCreate([
                'name' => 'Transfer'
            ], [
                'color' => '#6B7280'
            ]);

            $referenceId = 'transfer_' . uniqid();

            // Transacción de salida (expense)
            $expenseTransaction = Transaction::create([
                'title' => $data['title'] ?: "Transfer to {$toAccount->name}",
                'amount' => $amount,
                'type' => 'expense',
                'category_id' => $transferCategory->id,
                'account_id' => $fromAccount->id,
                'date' => $data['date'],
                'description' => $data['description'] ?? null,
                'reference_id' => $referenceId,
            ]);

            // Transacción de entrada (income)
            Transaction::create([
                'title' => $data['title'] ?: "Transfer from {$fromAccount->name}",
                'amount' => $amount,
                'type' => 'income',
                'category_id' => $transferCategory->id,
                'account_id' => $toAccount->id,
                'date' => $data['date'],
                'description' => $data['description'] ?? null,
                'reference_id' => $referenceId,
            ]);

            // Actualizar balances si tienes columna balance en accounts
            // $fromAccount->decrement('balance', $amount);
            // $toAccount->increment('balance', $amount);

            // Retornamos la primera transacción para que Filament sepa que se creó algo
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
