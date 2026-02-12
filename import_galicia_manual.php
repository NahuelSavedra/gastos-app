<?php

/**
 * Script para importar transacciones de Galicia manualmente
 * Ejecutar con: php import_galicia_manual.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

$accountId = 1; // Galicia

// Mapeo de tipos de transacción a categorías
$categoryMap = [
    'Transferencia de terceros' => 25,      // Transferencia recibida (income)
    'Debito debin recurrente' => 31,        // Débito DEBIN (expense)
    'Transferencia a terceros' => 22,       // Transferencia enviada (expense)
    'Sist. nac. de pagos - haberes' => 1,   // Sueldo (income)
    'Transf. ctas propias' => 22,           // Transferencia enviada (expense)
    'Rescate fima' => 34,                   // Rescate inversión (income)
    'Interes capitalizado' => 36,           // Intereses (income)
    'Pago de servicios' => 26,              // Pago de servicios (expense)
    'Pago tarjeta visa' => 32,              // Pago tarjeta Visa (expense)
    'Deb. autom. de serv.' => 33,           // Débito automático (expense)
    'Suscripcion fima' => 35,               // Inversión (expense)
    'Merpago*meli' => 30,                   // Compra débito (expense)
    '3sm*la nuevaestancia' => 30,           // Compra débito (expense)
    'Pedidosya propinas' => 13,             // Pedidos (expense)
    'Pedidosya restaurante' => 13,          // Pedidos (expense)
    'Tienda cafe n' => 30,                  // Compra débito (expense)
];

// Transacciones a importar (parseadas del texto)
$transactions = [
    ['date' => '2026-01-30', 'description' => 'Transferencia de terceros', 'amount' => 110000.00, 'is_expense' => false],
    ['date' => '2026-01-29', 'description' => 'Debito debin recurrente', 'amount' => 100000.00, 'is_expense' => true],
    ['date' => '2026-01-29', 'description' => 'Transferencia a terceros', 'amount' => 100000.00, 'is_expense' => true],
    ['date' => '2026-01-29', 'description' => 'Sist. nac. de pagos - haberes', 'amount' => 2374599.00, 'is_expense' => false],
    ['date' => '2026-01-28', 'description' => 'Transf. ctas propias', 'amount' => 2537.24, 'is_expense' => true],
    ['date' => '2026-01-27', 'description' => 'Debito debin recurrente', 'amount' => 30000.00, 'is_expense' => true],
    ['date' => '2026-01-26', 'description' => 'Debito debin recurrente', 'amount' => 40000.00, 'is_expense' => true],
    ['date' => '2026-01-26', 'description' => 'Debito debin recurrente', 'amount' => 60000.00, 'is_expense' => true],
    ['date' => '2026-01-26', 'description' => 'Merpago*meli', 'amount' => 18399.00, 'is_expense' => true],
    ['date' => '2026-01-26', 'description' => 'Rescate fima', 'amount' => 100000.00, 'is_expense' => false],
    ['date' => '2026-01-23', 'description' => 'Debito debin recurrente', 'amount' => 60000.00, 'is_expense' => true],
    ['date' => '2026-01-22', 'description' => 'Interes capitalizado', 'amount' => 16.45, 'is_expense' => false],
    ['date' => '2026-01-21', 'description' => 'Debito debin recurrente', 'amount' => 80000.00, 'is_expense' => true],
    ['date' => '2026-01-19', 'description' => 'Debito debin recurrente', 'amount' => 80000.00, 'is_expense' => true],
    ['date' => '2026-01-16', 'description' => 'Pago de servicios', 'amount' => 22549.97, 'is_expense' => true],
    ['date' => '2026-01-15', 'description' => '3sm*la nuevaestancia', 'amount' => 11864.00, 'is_expense' => true],
    ['date' => '2026-01-14', 'description' => 'Debito debin recurrente', 'amount' => 120000.00, 'is_expense' => true],
    ['date' => '2026-01-14', 'description' => 'Debito debin recurrente', 'amount' => 20549.03, 'is_expense' => true],
    ['date' => '2026-01-09', 'description' => 'Transferencia a terceros', 'amount' => 183000.22, 'is_expense' => true],
    ['date' => '2026-01-08', 'description' => 'Pedidosya propinas', 'amount' => 1300.00, 'is_expense' => true],
    ['date' => '2026-01-08', 'description' => 'Pedidosya restaurante', 'amount' => 13360.00, 'is_expense' => true],
    ['date' => '2026-01-06', 'description' => 'Transferencia a terceros', 'amount' => 90000.00, 'is_expense' => true],
    ['date' => '2026-01-05', 'description' => 'Pago tarjeta visa', 'amount' => 184809.20, 'is_expense' => true],
    ['date' => '2026-01-05', 'description' => 'Pago tarjeta visa', 'amount' => 16866.80, 'is_expense' => true],
    ['date' => '2026-01-05', 'description' => 'Debito debin recurrente', 'amount' => 100000.00, 'is_expense' => true],
    ['date' => '2026-01-05', 'description' => 'Tienda cafe n', 'amount' => 25200.00, 'is_expense' => true],
    ['date' => '2026-01-02', 'description' => 'Debito debin recurrente', 'amount' => 100000.00, 'is_expense' => true],
    ['date' => '2026-01-02', 'description' => 'Debito debin recurrente', 'amount' => 52558.19, 'is_expense' => true],
    ['date' => '2026-01-02', 'description' => 'Debito debin recurrente', 'amount' => 13666.92, 'is_expense' => true],
    ['date' => '2026-01-02', 'description' => 'Transferencia a terceros', 'amount' => 624216.00, 'is_expense' => true],
    ['date' => '2026-01-02', 'description' => 'Deb. autom. de serv.', 'amount' => 13688.00, 'is_expense' => true],
    ['date' => '2025-12-30', 'description' => 'Transferencia a terceros', 'amount' => 200000.00, 'is_expense' => true],
    ['date' => '2025-12-29', 'description' => 'Debito debin recurrente', 'amount' => 100000.00, 'is_expense' => true],
    ['date' => '2025-12-29', 'description' => 'Suscripcion fima', 'amount' => 865000.00, 'is_expense' => true],
    ['date' => '2025-12-29', 'description' => 'Sist. nac. de pagos - haberes', 'amount' => 2161675.00, 'is_expense' => false],
    ['date' => '2025-12-29', 'description' => 'Transferencia de terceros', 'amount' => 66500.00, 'is_expense' => false],
    ['date' => '2025-12-29', 'description' => 'Tienda cafe n', 'amount' => 30200.00, 'is_expense' => true],
];

echo "=== Importación Manual de Transacciones Galicia ===\n\n";

$imported = 0;
$skipped = 0;
$errors = [];

DB::beginTransaction();

try {
    foreach ($transactions as $index => $tx) {
        $descLower = strtolower($tx['description']);

        // Buscar categoría
        $categoryId = null;
        foreach ($categoryMap as $pattern => $catId) {
            if (strtolower($pattern) === $descLower) {
                $categoryId = $catId;
                break;
            }
        }

        if (! $categoryId) {
            // Fallback a categoría genérica
            $categoryId = $tx['is_expense'] ? 12 : 23; // Desconocidos / Ingreso de dinero
        }

        // Generar reference_id único
        $referenceId = 'import_galicia_manual_'.md5($tx['date'].'_'.$index.'_'.$tx['description'].'_'.$tx['amount']);

        // Verificar duplicados
        if (Transaction::where('reference_id', $referenceId)->exists()) {
            echo "⏭️  Saltando (duplicado): {$tx['date']} - {$tx['description']} - \${$tx['amount']}\n";
            $skipped++;

            continue;
        }

        // Crear transacción
        Transaction::create([
            'title' => ucfirst($tx['description']),
            'description' => 'Importación manual Galicia',
            'amount' => $tx['amount'],
            'date' => $tx['date'],
            'account_id' => $accountId,
            'category_id' => $categoryId,
            'reference_id' => $referenceId,
        ]);

        $type = $tx['is_expense'] ? '📉' : '📈';
        $amountFormatted = number_format($tx['amount'], 2, ',', '.');
        echo "{$type} Importado: {$tx['date']} - {$tx['description']} - \${$amountFormatted}\n";
        $imported++;
    }

    DB::commit();

    echo "\n=== Resumen ===\n";
    echo "✅ Importadas: {$imported}\n";
    echo "⏭️  Saltadas (duplicados): {$skipped}\n";

    if (! empty($errors)) {
        echo '❌ Errores: '.count($errors)."\n";
        foreach ($errors as $error) {
            echo "   - {$error}\n";
        }
    }

} catch (\Exception $e) {
    DB::rollBack();
    echo '❌ Error: '.$e->getMessage()."\n";
    exit(1);
}

echo "\n¡Listo!\n";
