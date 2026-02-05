<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransferService
{
    public function transfer(array $data): array
    {
        // Validaciones personalizadas
        $this->validateTransfer($data);

        return DB::transaction(function () use ($data) {
            $fromAccount = Account::findOrFail($data['from_account_id']);
            $toAccount = Account::findOrFail($data['to_account_id']);
            $amount = (float) $data['amount'];

            // Verificar saldo suficiente si tienes columna balance
            if (isset($fromAccount->balance) && $fromAccount->balance < $amount) {
                throw ValidationException::withMessages([
                    'amount' => ['Saldo insuficiente en la cuenta origen.'],
                ]);
            }

            // Crear o encontrar categoría de transferencia
            $transferCategory = Category::firstOrCreate([
                'name' => 'Transfer',
            ], [
                'color' => '#6B7280',
                'icon' => '🔄',
            ]);

            // Crear transacciones gemelas
            $expenseTransaction = Transaction::create([
                'title' => $data['title'] ?: "Transfer to {$toAccount->name}",
                'amount' => $amount,
                'type' => 'expense',
                'category_id' => $transferCategory->id,
                'account_id' => $fromAccount->id,
                'date' => $data['date'],
                'description' => $data['description'] ?? null,
                'reference_id' => uniqid('transfer_'), // Para vincular transacciones
            ]);

            $incomeTransaction = Transaction::create([
                'title' => $data['title'] ?: "Transfer from {$fromAccount->name}",
                'amount' => $amount,
                'type' => 'income',
                'category_id' => $transferCategory->id,
                'account_id' => $toAccount->id,
                'date' => $data['date'],
                'description' => $data['description'] ?? null,
                'reference_id' => $expenseTransaction->reference_id, // Misma referencia
            ]);

            // Actualizar balances si los manejas
            // $fromAccount->decrement('balance', $amount);
            // $toAccount->increment('balance', $amount);

            return [
                'success' => true,
                'message' => "Transferencia de \${$amount} realizada exitosamente",
                'from_account' => $fromAccount->name,
                'to_account' => $toAccount->name,
                'amount' => $amount,
                'transactions' => [$expenseTransaction, $incomeTransaction],
            ];
        });
    }

    /**
     * Validar datos de transferencia
     */
    private function validateTransfer(array $data): void
    {
        if ($data['from_account_id'] === $data['to_account_id']) {
            throw ValidationException::withMessages([
                'to_account_id' => ['La cuenta origen y destino no pueden ser iguales.'],
            ]);
        }

        if ($data['amount'] <= 0) {
            throw ValidationException::withMessages([
                'amount' => ['El monto debe ser mayor a cero.'],
            ]);
        }

        // Verificar que las cuentas existen
        if (! Account::where('id', $data['from_account_id'])->exists()) {
            throw ValidationException::withMessages([
                'from_account_id' => ['La cuenta origen no existe.'],
            ]);
        }

        if (! Account::where('id', $data['to_account_id'])->exists()) {
            throw ValidationException::withMessages([
                'to_account_id' => ['La cuenta destino no existe.'],
            ]);
        }
    }

    /**
     * Obtener transferencias relacionadas
     */
    public function getRelatedTransfers(Transaction $transaction): array
    {
        if (! $transaction->reference_id) {
            return [];
        }

        return Transaction::where('reference_id', $transaction->reference_id)
            ->where('id', '!=', $transaction->id)
            ->with(['account', 'category'])
            ->get()
            ->toArray();
    }
}
