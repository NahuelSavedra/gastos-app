<?php

namespace App\AI\Tools;

use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\DB;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

class GetExpensesByCategoryTool implements Tool
{
    public function description(): string
    {
        return 'Obtiene el total de gastos o ingresos por categoría en un período específico. Puede consultar una categoría específica o un resumen de todas.';
    }

    public function handle(Request $request): string
    {
        $startDate = Carbon::parse($request['start_date']);
        $endDate = Carbon::parse($request['end_date']);
        $categoryId = $request['category_id'] ?? null;

        $query = Transaction::whereBetween('date', [$startDate, $endDate])
            ->where('category_id', '!=', function ($q) {
                $q->select('id')
                    ->from('categories')
                    ->where('name', 'Transferencia')
                    ->limit(1);
            });

        if ($categoryId) {
            $category = Category::find($categoryId);
            if (! $category) {
                return "❌ Error: Categoría con ID {$categoryId} no encontrada.";
            }
            $query->where('category_id', $categoryId);

            $total = $query->sum('amount');
            $count = $query->count();

            return sprintf(
                "📊 Categoría '%s' (%s):\n\n".
                "💵 Total: $%s\n".
                "📝 Cantidad de transacciones: %d\n".
                '📅 Período: %s a %s',
                $category->name,
                $category->type === 'expense' ? 'Gastos' : 'Ingresos',
                number_format($total, 2, ',', '.'),
                $count,
                $startDate->format('d/m/Y'),
                $endDate->format('d/m/Y')
            );
        }

        // Resumen por todas las categorías
        $results = $query->select('category_id', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('category_id')
            ->with('category')
            ->get()
            ->sortByDesc('total');

        $output = sprintf(
            "📊 RESUMEN DE GASTOS/INGRESOS\n".
            "📅 Período: %s a %s\n\n",
            $startDate->format('d/m/Y'),
            $endDate->format('d/m/Y')
        );

        foreach ($results as $result) {
            $icon = $result->category->type === 'expense' ? '💸' : '💰';
            $output .= sprintf(
                "%s %s: $%s (%d transacciones)\n",
                $icon,
                $result->category->name,
                number_format($result->total, 2, ',', '.'),
                $result->count
            );
        }

        $totalAmount = $results->sum('total');
        $output .= sprintf("\n💵 TOTAL: $%s", number_format($totalAmount, 2, ',', '.'));

        return $output;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'start_date' => $schema->string()->description('Fecha inicio en formato YYYY-MM-DD')->required(),
            'end_date' => $schema->string()->description('Fecha fin en formato YYYY-MM-DD')->required(),
            'category_id' => $schema->integer()->description('ID de categoría específica (opcional). Si no se especifica, muestra resumen de todas.'),
        ];
    }
}
