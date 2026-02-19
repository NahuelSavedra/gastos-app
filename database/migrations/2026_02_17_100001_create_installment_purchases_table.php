<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('installment_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('credit_card_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('store')->nullable();
            $table->decimal('total_amount', 12, 2);
            $table->decimal('installment_amount', 12, 2); // monto por cuota
            $table->integer('installments_count');         // total de cuotas
            $table->integer('paid_installments')->default(0);
            $table->date('first_payment_date');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('installment_purchases');
    }
};
