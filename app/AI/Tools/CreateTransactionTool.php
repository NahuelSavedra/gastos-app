<?php

namespace App\AI\Tools;

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\DB;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

class CreateTransactionTool implements Tool
{
    public function description(): string
    {
        return 'Crea una nueva transacción (gasto o ingreso) en el sistema. Requiere monto, categoría (ID), cuenta (ID) y fecha. IMPORTANTE: Debes usar list_categories_and_accounts primero para obtener los IDs correctos.';
    }

    public function handle(Request $request): string
    {
        // Validar que la categoría existe
        $category = Category::find($request['category_id']);
        if (! $category) {
            return "❌ Error: Categoría con ID {$request['category_id']} no encontrada. Usa list_categories_and_accounts para ver las categorías disponibles.";
        }

        // Validar que la cuenta existe
        $account = Account::find($request['account_id']);
        if (! $account) {
            return "❌ Error: Cuenta con ID {$request['account_id']} no encontrada. Usa list_categories_and_accounts para ver las cuentas disponibles.";
        }

        DB::beginTransaction();
        try {
            $transaction = Transaction::create([
                'title' => $request['title'],
                'description' => $request['description'] ?? null,
                'amount' => $request['amount'],
                'date' => Carbon::parse($request['date']),
                'category_id' => $category->id,
                'account_id' => $account->id,
            ]);

            DB::commit();

            return sprintf(
                "✅ Transacción creada exitosamente:\n\n".
                "📝 Título: %s\n".
                "💵 Monto: $%s\n".
                "📂 Categoría: %s (%s)\n".
                "🏦 Cuenta: %s\n".
                "📅 Fecha: %s\n".
                '🆔 ID: %d',
                $transaction->title,
                number_format($transaction->amount, 2, ',', '.'),
                $category->name,
                $category->type === 'expense' ? 'Gasto' : 'Ingreso',
                $account->name,
                $transaction->date->format('d/m/Y'),
                $transaction->id
            );
        } catch (\Exception $e) {
            DB::rollBack();

            return '❌ Error al crear transacción: '.$e->getMessage();
        }
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'title' => $schema->string()->description('Título descriptivo de la transacción')->required(),
            'amount' => $schema->number()->description('Monto en pesos argentinos (solo número, sin símbolo)')->required(),
            'category_id' => $schema->integer()->description('ID de la categoría (obtener con list_categories_and_accounts)')->required(),
            'account_id' => $schema->integer()->description('ID de la cuenta (obtener con list_categories_and_accounts)')->required(),
            'date' => $schema->string()->description('Fecha en formato YYYY-MM-DD')->required(),
            'description' => $schema->string()->description('Descripción adicional opcional'),
        ];
    }
}
