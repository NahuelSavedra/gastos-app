<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\TransactionResource;
use App\Models\TransactionTemplate;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\View;

class QuickTransactionsWidget extends Widget
{
    protected static ?int $sort = 0;
    protected static string $view = 'filament.widgets.quick-transactions';
    protected int | string | array $columnSpan = 'full';

    public array $amounts = [];

    public function getViewData(): array
    {
        $allTemplates = TransactionTemplate::active()
            ->with(['category', 'account'])
            ->orderBy('name')
            ->get();

        return [
            'templates' => $allTemplates->groupBy(function ($template) {
                return $template->is_recurring ? 'recurring' : 'oneTime';
            }),
            'pending' => $allTemplates->filter(fn($t) => !$t->isUsedThisMonth()),
            'paid' => $allTemplates->filter(fn($t) => $t->isUsedThisMonth()),
        ];
    }

    /**
     * Crear transacción desde template con monto fijo
     */
    public function createFromTemplate(int $templateId): void
    {
        $template = TransactionTemplate::findOrFail($templateId);

        // Validar que el template tenga monto fijo
        if (!$template->amount) {
            Notification::make()
                ->title('Error')
                ->body('Este template requiere ingresar un monto')
                ->danger()
                ->send();
            return;
        }

        $transaction = $template->createTransaction([
            'amount' => $template->amount,
            'date' => now(),
        ]);

        if ($template->is_recurring) {
            $template->update(['last_generated_at' => now()]);
        }

        Notification::make()
            ->title('✅ Transacción creada')
            ->body("Se creó: {$transaction->title} - \${$transaction->amount}")
            ->success()
            ->send();

        // Limpiar montos y refrescar
        $this->amounts = [];
        $this->dispatch('transaction-created');
    }

    /**
     * Crear transacción desde template con monto variable
     */
    public function createWithAmount(int $templateId): void
    {
        $template = TransactionTemplate::findOrFail($templateId);

        // Obtener el monto del array
        $amount = $this->amounts[$templateId] ?? null;

        // Validación
        if (!$amount || $amount <= 0) {
            Notification::make()
                ->title('Error')
                ->body('Por favor ingresa un monto válido')
                ->danger()
                ->send();
            return;
        }

        $transaction = $template->createTransaction([
            'amount' => $amount,
            'date' => now(),
        ]);

        if ($template->is_recurring) {
            $template->update(['last_generated_at' => now()]);
        }

        Notification::make()
            ->title('✅ Transacción creada')
            ->body("Se creó: {$transaction->title} - \${$transaction->amount}")
            ->success()
            ->send();

        // Limpiar el monto usado
        unset($this->amounts[$templateId]);
        $this->dispatch('transaction-created');
    }
}
