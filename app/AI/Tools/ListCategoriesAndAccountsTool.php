<?php

namespace App\AI\Tools;

use App\Models\Account;
use App\Models\Category;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

class ListCategoriesAndAccountsTool implements Tool
{
    public function description(): string
    {
        return 'Obtiene la lista de categorías y cuentas disponibles con sus IDs. SIEMPRE usa esta herramienta antes de crear una transacción para obtener los IDs correctos.';
    }

    public function handle(Request $request): string
    {
        $categories = Category::orderBy('type')->orderBy('name')->get();
        $accounts = Account::orderBy('name')->get();

        $output = "📋 CATEGORÍAS Y CUENTAS DISPONIBLES\n\n";

        // Categorías de gastos
        $output .= "💸 CATEGORÍAS DE GASTOS:\n";
        foreach ($categories->where('type', 'expense') as $cat) {
            $output .= sprintf("  - ID: %d | %s\n", $cat->id, $cat->name);
        }

        // Categorías de ingresos
        $output .= "\n💰 CATEGORÍAS DE INGRESOS:\n";
        foreach ($categories->where('type', 'income') as $cat) {
            $output .= sprintf("  - ID: %d | %s\n", $cat->id, $cat->name);
        }

        // Cuentas
        $output .= "\n🏦 CUENTAS DISPONIBLES:\n";
        foreach ($accounts as $acc) {
            $output .= sprintf(
                "  - ID: %d | %s (Tipo: %s, Balance: $%s)\n",
                $acc->id,
                $acc->name,
                $acc->type,
                number_format($acc->current_balance, 2, ',', '.')
            );
        }

        return $output;
    }

    public function schema(JsonSchema $schema): array
    {
        return []; // No requiere parámetros
    }
}
