<?php

namespace App\Livewire;

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Filament\Notifications\Notification;

class BatchTransactionModal extends Component
{
    public bool $showModal = false;

    // Datos comunes
    #[Validate('required|exists:accounts,id')]
    public ?int $account_id = null;

    #[Validate('required|in:income,expense')]
    public ?string $transaction_type = null;

    #[Validate('required|exists:categories,id')]
    public ?int $category_id = null;

    #[Validate('required|string|max:255')]
    public ?string $description = null;

    // Líneas individuales
    public array $lines = [];

    // Estado de carga
    public bool $saving = false;

    public function mount(): void
    {
        $this->initializeLines();
    }

    protected function initializeLines(): void
    {
        $today = now()->format('Y-m-d');
        $this->lines = [
            ['id' => uniqid(), 'date' => $today, 'amount' => null, 'notes' => null],
            ['id' => uniqid(), 'date' => $today, 'amount' => null, 'notes' => null],
            ['id' => uniqid(), 'date' => $today, 'amount' => null, 'notes' => null],
        ];
    }

    #[\Livewire\Attributes\On('open-batch-modal')]
    public function openModal(): void
    {
        $this->reset(['account_id', 'transaction_type', 'category_id', 'description', 'saving']);
        $this->resetValidation();
        $this->initializeLines();
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
    }

    public function addLine(): void
    {
        if (count($this->lines) >= 50) {
            return;
        }

        $this->lines[] = [
            'id' => uniqid(),
            'date' => now()->format('Y-m-d'),
            'amount' => null,
            'notes' => null,
        ];
    }

    public function setDateForLine(int $index, string $date): void
    {
        if (isset($this->lines[$index])) {
            $this->lines[$index]['date'] = $date;
        }
    }

    public function setDateForAllLines(string $date): void
    {
        foreach ($this->lines as $index => $line) {
            $this->lines[$index]['date'] = $date;
        }
    }

    public function removeLine(int $index): void
    {
        if (count($this->lines) <= 1) {
            return;
        }

        unset($this->lines[$index]);
        $this->lines = array_values($this->lines);
    }

    #[Computed]
    public function accounts(): array
    {
        return Account::orderBy('name')->pluck('name', 'id')->toArray();
    }

    #[Computed]
    public function categories(): array
    {
        $query = Category::orderBy('name');

        if ($this->transaction_type) {
            $query->where('type', $this->transaction_type);
        }

        return $query->pluck('name', 'id')->toArray();
    }

    #[Computed]
    public function totalAmount(): float
    {
        return collect($this->lines)
            ->filter(fn($line) => !empty($line['date']) && !empty($line['amount']))
            ->sum('amount');
    }

    #[Computed]
    public function totalCount(): int
    {
        return collect($this->lines)
            ->filter(fn($line) => !empty($line['date']) && !empty($line['amount']))
            ->count();
    }

    public function updatedTransactionType(): void
    {
        // Reset category when type changes
        $this->category_id = null;
    }

    public function save(): void
    {
        $this->saving = true;

        try {
            // Validar datos comunes
            $this->validate([
                'account_id' => 'required|exists:accounts,id',
                'transaction_type' => 'required|in:income,expense',
                'category_id' => 'required|exists:categories,id',
                'description' => 'required|string|max:255',
            ], [
                'account_id.required' => 'Selecciona una cuenta',
                'transaction_type.required' => 'Selecciona el tipo de transacción',
                'category_id.required' => 'Selecciona una categoría',
                'description.required' => 'Ingresa una descripción',
            ]);

            // Filtrar líneas válidas
            $validLines = collect($this->lines)
                ->filter(fn($line) => !empty($line['date']) && !empty($line['amount']) && $line['amount'] > 0)
                ->values()
                ->toArray();

            if (empty($validLines)) {
                Notification::make()
                    ->warning()
                    ->title('Sin transacciones')
                    ->body('Debes agregar al menos una transacción con fecha y monto')
                    ->send();
                $this->saving = false;
                return;
            }

            // Validar líneas individuales
            foreach ($validLines as $index => $line) {
                if ($line['amount'] <= 0) {
                    $this->addError("lines.{$index}.amount", 'El monto debe ser mayor a 0');
                    $this->saving = false;
                    return;
                }
            }

            Log::info('BatchTransactionModal: Iniciando creación de ' . count($validLines) . ' transacciones');

            DB::transaction(function () use ($validLines) {
                $now = now();
                $records = [];

                foreach ($validLines as $line) {
                    $records[] = [
                        'account_id' => $this->account_id,
                        'category_id' => $this->category_id,
                        'title' => $this->description,
                        'description' => $line['notes'] ?? null,
                        'amount' => $line['amount'],
                        'date' => $line['date'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                Transaction::insert($records);
            });

            Log::info('BatchTransactionModal: Creación exitosa de ' . count($validLines) . ' transacciones');

            $count = count($validLines);

            Notification::make()
                ->success()
                ->title('Transacciones creadas')
                ->body("Se crearon {$count} transacciones exitosamente")
                ->send();

            $this->closeModal();
            $this->dispatch('batch-transactions-created');

        } catch (\Exception $e) {
            Log::error('BatchTransactionModal: Error al crear transacciones', ['error' => $e->getMessage()]);

            Notification::make()
                ->danger()
                ->title('Error')
                ->body('Hubo un error al crear las transacciones. Por favor intenta nuevamente.')
                ->send();
        } finally {
            $this->saving = false;
        }
    }

    public function render()
    {
        return view('livewire.batch-transaction-modal');
    }
}
