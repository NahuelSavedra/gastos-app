<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\TransactionResource;
use App\Models\TransactionTemplate;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;

class QuickTransactionsWidget extends Widget
{
    protected static ?int $sort = 0;
    protected static string $view = 'filament.widgets.quick-transactions';
    protected int | string | array $columnSpan = 'full';

    // Propiedades públicas
    public array $amounts = [];
    public array $dates = []; // ✅ NUEVO

    public function getViewData(): array
    {
        return [
            'templates' => TransactionTemplate::active()
                ->with(['category', 'account'])
                ->orderBy('name')
                ->get()
                ->groupBy(function ($template) {
                    return $template->is_recurring ? 'recurring' : 'oneTime';
                }),
        ];
    }

    /**
     * Crear transacción desde template con monto fijo
     */
    public function createFromTemplate(int $templateId): void
    {
        $template = TransactionTemplate::findOrFail($templateId);

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

        $this->amounts = [];
        $this->dispatch('transaction-created');
    }

    /**
     * Crear transacción desde template con monto variable
     */
    public function createWithAmount(int $templateId): void
    {
        $template = TransactionTemplate::findOrFail($templateId);

        $amount = $this->amounts[$templateId] ?? null;

        if (!$amount || $amount <= 0) {
            Notification::make()
                ->title('Error')
                ->body('Por favor ingresa un monto válido')
                ->danger()
                ->send();
            return;
        }

        // ✅ LÓGICA DE FECHA: Recurrentes = hoy, No recurrentes = fecha personalizable
        $date = $template->is_recurring
            ? now()
            : ($this->dates[$templateId] ?? now());

        $transaction = $template->createTransaction([
            'amount' => $amount,
            'date' => $date,
        ]);

        if ($template->is_recurring) {
            $template->update(['last_generated_at' => now()]);
        }

        Notification::make()
            ->title('✅ Transacción creada')
            ->body("Se creó: {$transaction->title} - \${$transaction->amount} - {$transaction->date->format('d/m/Y')}")
            ->success()
            ->send();

        // Limpiar datos
        unset($this->amounts[$templateId], $this->dates[$templateId]);
        $this->dispatch('transaction-created');
    }
}
