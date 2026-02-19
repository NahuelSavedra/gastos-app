<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('credit_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name'); // Ej: "VISA Banco Galicia"
            $table->string('last_four', 4)->nullable();
            $table->decimal('credit_limit', 12, 2)->default(0);
            $table->tinyInteger('closing_day'); // 1–31
            $table->tinyInteger('due_day');     // 1–31
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_cards');
    }
};
