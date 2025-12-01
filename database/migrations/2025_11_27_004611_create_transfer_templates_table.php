<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transfer_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // "Galicia → MP"
            $table->foreignId('from_account_id')->constrained('accounts')->cascadeOnDelete();
            $table->foreignId('to_account_id')->constrained('accounts')->cascadeOnDelete();
            $table->decimal('default_amount', 15, 2)->nullable(); // Monto por defecto (opcional)
            $table->string('icon')->default('heroicon-o-arrow-right-circle');
            $table->string('color')->default('primary'); // Color del botón
            $table->integer('order')->default(0); // Orden de visualización
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transfer_templates');
    }
};
