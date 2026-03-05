<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('installment_purchases', function (Blueprint $table) {
            $table->foreignId('transaction_id')
                ->nullable()
                ->after('credit_card_id')
                ->constrained('transactions')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('installment_purchases', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\Transaction::class);
            $table->dropColumn('transaction_id');
        });
    }
};
