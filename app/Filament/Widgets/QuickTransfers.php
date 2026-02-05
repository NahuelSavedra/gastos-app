<?php

namespace App\Filament\Widgets;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\TransferTemplate;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;

class QuickTransfers extends Widget
{
    protected static string $view = 'filament.widgets.quick-transfers';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 1;

    protected static bool $isDiscovered = false;

    public ?int $selectedTemplateId = null;

    public ?float $amount = null;

    public ?string $date = null;

    public ?string $description = null;

    public function mount(): void
    {
        $this->date = now()->format('Y-m-d');
    }

    public function selectTemplate(int $templateId): void
    {
        $this->selectedTemplateId = $templateId;

        $template = TransferTemplate::find($templateId);
        if ($template && $template->default_amount) {
            $this->amount = $template->default_amount;
        }
    }

    public function executeTransfer(): void
    {
        $this->validate([
            'selectedTemplateId' => 'required|exists:transfer_templates,id',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
        ], [
            'selectedTemplateId.required' => 'Debe seleccionar un template',
            'amount.required' => 'El monto es requerido',
            'amount.min' => 'El monto debe ser mayor a 0',
            'date.required' => 'La fecha es requerida',
        ]);

        $template = TransferTemplate::with(['fromAccount', 'toAccount'])->findOrFail($this->selectedTemplateId);

        try {
            DB::transaction(function () use ($template) {
                // Categorías de transferencia
                $transferExpenseCategory = Category::firstOrCreate(
                    ['name' => 'Transferencia'],
                    ['type' => 'expense']
                );

                $transferIncomeCategory = Category::firstOrCreate(
                    ['name' => 'Transferencia (Recibida)'],
                    ['type' => 'income']
                );

                $referenceId = 'transfer_'.uniqid();
                $description = $this->description ?: "Transferencia rápida: {$template->name}";

                // Inserción masiva
                Transaction::insert([
                    [
                        'title' => "Transferencia a {$template->toAccount->name}",
                        'amount' => $this->amount,
                        'category_id' => $transferExpenseCategory->id,
                        'account_id' => $template->from_account_id,
                        'date' => $this->date,
                        'description' => $description,
                        'reference_id' => $referenceId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'title' => "Transferencia desde {$template->fromAccount->name}",
                        'amount' => $this->amount,
                        'category_id' => $transferIncomeCategory->id,
                        'account_id' => $template->to_account_id,
                        'date' => $this->date,
                        'description' => $description,
                        'reference_id' => $referenceId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                ]);
            });

            Notification::make()
                ->success()
                ->title('Transferencia realizada')
                ->body("Se transfirieron \${$this->amount} exitosamente")
                ->send();

            // Resetear formulario
            $this->reset(['selectedTemplateId', 'amount', 'description']);
            $this->date = now()->format('Y-m-d');

            // Refrescar otros widgets
            $this->dispatch('refresh-accounts');

        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Error en la transferencia')
                ->body($e->getMessage())
                ->send();
        }
    }

    public function cancelTransfer(): void
    {
        $this->reset(['selectedTemplateId', 'amount', 'description']);
        $this->date = now()->format('Y-m-d');
    }

    public function getTemplates()
    {
        return TransferTemplate::with(['fromAccount', 'toAccount'])
            ->active()
            ->get();
    }

    public function getSelectedTemplate()
    {
        return $this->selectedTemplateId
            ? TransferTemplate::with(['fromAccount', 'toAccount'])->find($this->selectedTemplateId)
            : null;
    }
}
