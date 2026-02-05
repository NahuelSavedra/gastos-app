<?php

namespace App\Console\Commands;

use App\Models\TransactionTemplate;
use Illuminate\Console\Command;

class GenerateRecurringTransactions extends Command
{
    protected $signature = 'transactions:generate-recurring';

    protected $description = 'Genera automáticamente las transacciones recurrentes del día';

    public function handle(): int
    {
        $this->info('🔄 Buscando transacciones recurrentes para generar...');

        $templates = TransactionTemplate::active()
            ->autoCreate()
            ->recurring()
            ->get();

        $generated = 0;

        foreach ($templates as $template) {
            if ($template->shouldGenerateToday()) {
                try {
                    $transaction = $template->createTransaction();
                    $template->update(['last_generated_at' => now()]);

                    $this->line("✅ Creada: {$transaction->title} - \${$transaction->amount}");
                    $generated++;
                } catch (\Exception $e) {
                    $this->error("❌ Error al crear {$template->name}: {$e->getMessage()}");
                }
            }
        }

        if ($generated === 0) {
            $this->comment('ℹ️  No hay transacciones pendientes para hoy');
        } else {
            $this->info("✨ Se generaron {$generated} transacciones");
        }

        return self::SUCCESS;
    }
}
