<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;

class UpdateAccountTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Actualizar tipos de cuentas según los nombres
        $accountUpdates = [
            'Galicia' => [
                'account_type' => 'checking',
                'icon' => '🏦',
                'description' => 'Cuenta corriente Banco Galicia',
                'include_in_totals' => true,
            ],
            'MP' => [
                'account_type' => 'wallet',
                'icon' => '👛',
                'description' => 'Billetera MercadoPago',
                'include_in_totals' => true,
            ],
            'Efectivo' => [
                'account_type' => 'cash',
                'icon' => '💵',
                'description' => 'Dinero en efectivo',
                'include_in_totals' => true,
            ],
            'Supervielle' => [
                'account_type' => 'checking',
                'icon' => '🏦',
                'description' => 'Cuenta corriente Banco Supervielle',
                'include_in_totals' => true,
            ],
            'FIMA' => [
                'account_type' => 'savings',
                'icon' => '💰',
                'description' => 'Cuenta de ahorro FIMA (Fondo común de inversión)',
                'include_in_totals' => true,
            ],
        ];

        foreach ($accountUpdates as $accountName => $updates) {
            $account = Account::where('name', $accountName)->first();

            if ($account) {
                $account->update($updates);
                $this->command->info("✅ Actualizada cuenta: {$accountName} como {$updates['account_type']}");
            } else {
                $this->command->warn("⚠️  Cuenta no encontrada: {$accountName}");
            }
        }

        $this->command->info("\n🎉 Actualización completada!");
    }
}
