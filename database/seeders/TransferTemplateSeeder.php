<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\TransferTemplate;
use Illuminate\Database\Seeder;

class TransferTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Galicia → MP',
                'from' => 'Galicia',
                'to' => 'Mercado Pago',
                'color' => 'primary',
                'order' => 1,
            ],
            [
                'name' => 'Supervielle → MP',
                'from' => 'Supervielle',
                'to' => 'Mercado Pago',
                'color' => 'success',
                'order' => 2,
            ],
            [
                'name' => 'Galicia → Fima',
                'from' => 'Galicia',
                'to' => 'Fima',
                'color' => 'warning',
                'order' => 3,
            ],
        ];

        foreach ($templates as $template) {
            $fromAccount = Account::where('name', 'LIKE', "%{$template['from']}%")->first();
            $toAccount = Account::where('name', 'LIKE', "%{$template['to']}%")->first();

            if ($fromAccount && $toAccount) {
                TransferTemplate::create([
                    'name' => $template['name'],
                    'from_account_id' => $fromAccount->id,
                    'to_account_id' => $toAccount->id,
                    'color' => $template['color'],
                    'order' => $template['order'],
                    'is_active' => true,
                ]);
            }
        }
    }
}
