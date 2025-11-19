<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaction_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // "Alquiler Octubre", "Telecentro", etc.
            $table->string('title')->nullable(); // Título que se usará en la transacción
            $table->decimal('amount', 15, 2)->nullable(); // Monto fijo o null si varía
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->foreignId('account_id')->constrained()->onDelete('cascade');
            $table->text('description')->nullable();
            $table->boolean('is_recurring')->default(false); // Si es recurrente
            $table->string('recurrence_type')->nullable(); // 'monthly', 'weekly', 'yearly'
            $table->integer('recurrence_day')->nullable(); // Día del mes (1-31) o día de la semana (1-7)
            $table->boolean('auto_create')->default(false); // Si se crea automáticamente
            $table->date('last_generated_at')->nullable(); // Última vez que se generó automáticamente
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_templates');
    }
};
