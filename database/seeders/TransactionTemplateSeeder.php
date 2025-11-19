<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Category;
use App\Models\TransactionTemplate;
use Illuminate\Database\Seeder;

class TransactionTemplateSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener IDs de categorías (ajusta según tus datos)
        $categorySalary = Category::where('name', 'Sueldo')->first()?->id ?? 1;
        $categoryRent = Category::where('name', 'Alquiler')->first()?->id ?? 7;
        $categoryExpenses = Category::where('name', 'Expensas')->first()?->id ?? 8;
        $categoryCards = Category::where('name', 'Tarjetas')->first()?->id ?? 9;
        $categoryServices = Category::where('name', 'Servicios')->first()?->id ?? 10;
        $categoryGroceries = Category::where('name', 'Supermercado')->first()?->id ?? 3;
        $categoryRestaurant = Category::where('name', 'Restaurantes')->first()?->id ?? 13;
        $categoryOther = Category::where('name', 'Otros')->first()?->id ?? 11;

        // Obtener IDs de cuentas (ajusta según tus datos)
        $accountGalicia = Account::where('name', 'Galicia')->first()?->id ?? 1;
        $accountMP = Account::where('name', 'MP')->first()?->id ?? 2;
        $accountSupervielle = Account::where('name', 'Supervielle')->first()?->id ?? 4;

        $templates = [
            // ===== INGRESOS RECURRENTES =====
            [
                'name' => 'Sueldo Mensual',
                'title' => 'Sueldo',
                'amount' => null, // Variable
                'category_id' => $categorySalary,
                'account_id' => $accountSupervielle,
                'description' => 'Ingreso mensual de sueldo',
                'is_recurring' => true,
                'recurrence_type' => 'monthly',
                'recurrence_day' => 1,
                'auto_create' => false, // Deberás confirmarlo manualmente
                'is_active' => true,
            ],

            // ===== GASTOS FIJOS MENSUALES =====
            [
                'name' => 'Alquiler',
                'title' => 'Alquiler',
                'amount' => 586347,
                'category_id' => $categoryRent,
                'account_id' => $accountGalicia,
                'description' => 'Alquiler mensual',
                'is_recurring' => true,
                'recurrence_type' => 'monthly',
                'recurrence_day' => 6, // Basado en tu historial (2025-10-06)
                'auto_create' => true,
                'is_active' => true,
            ],

            [
                'name' => 'Expensas',
                'title' => 'Expensas',
                'amount' => 183000,
                'category_id' => $categoryExpenses,
                'account_id' => $accountGalicia,
                'description' => 'Expensas mensuales',
                'is_recurring' => true,
                'recurrence_type' => 'monthly',
                'recurrence_day' => 7,
                'auto_create' => true,
                'is_active' => true,
            ],

            [
                'name' => 'Telecentro',
                'title' => 'Telecentro',
                'amount' => 10999,
                'category_id' => $categoryServices,
                'account_id' => $accountGalicia,
                'description' => 'Internet mensual',
                'is_recurring' => true,
                'recurrence_type' => 'monthly',
                'recurrence_day' => 2,
                'auto_create' => true,
                'is_active' => true,
            ],

            [
                'name' => 'Edesur',
                'title' => 'Edesur',
                'amount' => null, // Variable
                'category_id' => $categoryServices,
                'account_id' => $accountMP,
                'description' => 'Luz',
                'is_recurring' => true,
                'recurrence_type' => 'monthly',
                'recurrence_day' => 4,
                'auto_create' => false,
                'is_active' => true,
            ],

            [
                'name' => 'Metrogas',
                'title' => 'Metrogas',
                'amount' => null, // Variable
                'category_id' => $categoryServices,
                'account_id' => $accountMP,
                'description' => 'Gas',
                'is_recurring' => true,
                'recurrence_type' => 'monthly',
                'recurrence_day' => 16,
                'auto_create' => false,
                'is_active' => true,
            ],

            [
                'name' => 'Movistar',
                'title' => 'Movistar',
                'amount' => 20450,
                'category_id' => $categoryServices,
                'account_id' => $accountMP,
                'description' => 'Celular',
                'is_recurring' => true,
                'recurrence_type' => 'monthly',
                'recurrence_day' => 16,
                'auto_create' => true,
                'is_active' => true,
            ],

            [
                'name' => 'Meli+',
                'title' => 'Meli+',
                'amount' => 18399,
                'category_id' => $categoryServices,
                'account_id' => $accountMP,
                'description' => 'Suscripción Mercado Libre',
                'is_recurring' => true,
                'recurrence_type' => 'monthly',
                'recurrence_day' => 27,
                'auto_create' => true,
                'is_active' => true,
            ],

            // ===== TARJETAS DE CRÉDITO =====
            [
                'name' => 'Tarjeta - Galicia',
                'title' => 'Tarjeta',
                'amount' => null, // Variable
                'category_id' => $categoryCards,
                'account_id' => $accountGalicia,
                'description' => 'Pago tarjeta de crédito',
                'is_recurring' => true,
                'recurrence_type' => 'monthly',
                'recurrence_day' => 6,
                'auto_create' => false,
                'is_active' => true,
            ],

            [
                'name' => 'Tarjeta - MP',
                'title' => 'Tarjeta',
                'amount' => null, // Variable
                'category_id' => $categoryCards,
                'account_id' => $accountMP,
                'description' => 'Pago tarjeta MP',
                'is_recurring' => true,
                'recurrence_type' => 'monthly',
                'recurrence_day' => 13,
                'auto_create' => false,
                'is_active' => true,
            ],

            // ===== GASTOS FRECUENTES (NO RECURRENTES) =====
            [
                'name' => 'Shi Le Chen',
                'title' => 'Shi Le Chen',
                'amount' => null,
                'category_id' => $categoryGroceries,
                'account_id' => $accountMP,
                'description' => 'Supermercado chino',
                'is_recurring' => false,
                'is_active' => true,
            ],

            [
                'name' => 'Día',
                'title' => 'Día',
                'amount' => null,
                'category_id' => $categoryGroceries,
                'account_id' => $accountMP,
                'description' => 'Supermercado Día',
                'is_recurring' => false,
                'is_active' => true,
            ],

            [
                'name' => 'Coto',
                'title' => 'Coto',
                'amount' => null,
                'category_id' => $categoryGroceries,
                'account_id' => $accountMP,
                'description' => 'Supermercado Coto',
                'is_recurring' => false,
                'is_active' => true,
            ],

            [
                'name' => 'Carrefour',
                'title' => 'Carrefour',
                'amount' => null,
                'category_id' => $categoryGroceries,
                'account_id' => $accountMP,
                'description' => 'Supermercado Carrefour',
                'is_recurring' => false,
                'is_active' => true,
            ],

            [
                'name' => 'PedidosYa',
                'title' => 'PedidosYa',
                'amount' => null,
                'category_id' => $categoryRestaurant,
                'account_id' => $accountGalicia,
                'description' => 'Delivery',
                'is_recurring' => false,
                'is_active' => true,
            ],

            [
                'name' => 'Rappi',
                'title' => 'Rappi',
                'amount' => null,
                'category_id' => $categoryRestaurant,
                'account_id' => $accountGalicia,
                'description' => 'Delivery',
                'is_recurring' => false,
                'is_active' => true,
            ],

            [
                'name' => 'Coffee Store',
                'title' => 'Coffee Store',
                'amount' => null,
                'category_id' => $categoryOther,
                'account_id' => $accountMP,
                'description' => 'Café',
                'is_recurring' => false,
                'is_active' => true,
            ],
        ];

        foreach ($templates as $template) {
            TransactionTemplate::create($template);
        }

        $this->command->info('✅ Templates creados exitosamente');
    }
}
