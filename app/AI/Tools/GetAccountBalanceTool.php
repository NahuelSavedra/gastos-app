<?php

namespace App\AI\Tools;

use App\Models\Account;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

class GetAccountBalanceTool implements Tool
{
    public function description(): string
    {
        return 'Obtiene el balance actual de una o todas las cuentas. Puede incluir detalle de movimientos del mes si se solicita.';
    }

    public function handle(Request $request): string
    {
        $accountId = $request['account_id'] ?? null;
        $includeMonthDetail = $request['include_month_detail'] ?? false;

        if ($accountId) {
            $account = Account::find($accountId);
            if (! $account) {
                return "❌ Error: Cuenta con ID {$accountId} no encontrada.";
            }

            $output = sprintf(
                "🏦 CUENTA: %s\n\n".
                "💵 Balance actual: $%s\n".
                "📊 Tipo: %s\n".
                '✓ Incluida en totales: %s',
                $account->name,
                number_format($account->current_balance, 2, ',', '.'),
                $this->getAccountTypeLabel($account->type),
                $account->include_in_totals ? 'Sí' : 'No'
            );

            if ($includeMonthDetail) {
                $startOfMonth = Carbon::now()->startOfMonth();
                $monthIncome = Transaction::where('account_id', $account->id)
                    ->where('date', '>=', $startOfMonth)
                    ->whereHas('category', fn ($q) => $q->where('type', 'income'))
                    ->sum('amount');

                $monthExpenses = Transaction::where('account_id', $account->id)
                    ->where('date', '>=', $startOfMonth)
                    ->whereHas('category', fn ($q) => $q->where('type', 'expense'))
                    ->sum('amount');

                $output .= sprintf(
                    "\n\n📅 MOVIMIENTOS DE %s:\n".
                    "💰 Ingresos: $%s\n".
                    "💸 Gastos: $%s\n".
                    '📊 Balance del mes: $%s',
                    Carbon::now()->locale('es')->isoFormat('MMMM YYYY'),
                    number_format($monthIncome, 2, ',', '.'),
                    number_format($monthExpenses, 2, ',', '.'),
                    number_format($monthIncome - $monthExpenses, 2, ',', '.')
                );
            }

            return $output;
        }

        // Resumen de todas las cuentas
        $accounts = Account::orderBy('name')->get();
        $output = "🏦 RESUMEN DE CUENTAS\n\n";

        foreach ($accounts as $account) {
            $icon = $this->getAccountIcon($account->type);
            $output .= sprintf(
                "%s %s: $%s\n",
                $icon,
                $account->name,
                number_format($account->current_balance, 2, ',', '.')
            );
        }

        $totalBalance = $accounts->where('include_in_totals', true)->sum('current_balance');
        $output .= sprintf("\n💵 BALANCE TOTAL: $%s", number_format($totalBalance, 2, ',', '.'));

        return $output;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'account_id' => $schema->integer()->description('ID de la cuenta específica (opcional). Si no se especifica, muestra resumen de todas.'),
            'include_month_detail' => $schema->boolean()->description('Si se debe incluir detalle de movimientos del mes actual'),
        ];
    }

    private function getAccountTypeLabel(string $type): string
    {
        return match ($type) {
            'checking' => 'Cuenta corriente',
            'savings' => 'Cuenta de ahorros',
            'cash' => 'Efectivo',
            'credit_card' => 'Tarjeta de crédito',
            'investment' => 'Inversión',
            'wallet' => 'Billetera digital',
            default => $type,
        };
    }

    private function getAccountIcon(string $type): string
    {
        return match ($type) {
            'checking' => '🏦',
            'savings' => '💰',
            'cash' => '💵',
            'credit_card' => '💳',
            'investment' => '📈',
            'wallet' => '👛',
            default => '💼',
        };
    }
}
