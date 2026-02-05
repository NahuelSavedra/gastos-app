<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_category_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('source')->nullable(); // mercadopago, galicia, etc. null = all sources
            $table->string('field'); // business_unit, sub_unit, transaction_type, etc.
            $table->string('operator'); // equals, contains, starts_with, ends_with
            $table->string('value');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->integer('priority')->default(0); // higher = evaluated first
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['source', 'is_active', 'priority']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_category_rules');
    }
};
