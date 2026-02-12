<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Single column indexes for common filters
            $table->index('account_id', 'idx_transactions_account_id');
            $table->index('category_id', 'idx_transactions_category_id');
            $table->index('date', 'idx_transactions_date');
            $table->index('created_at', 'idx_transactions_created_at');

            // Composite indexes for common query patterns
            $table->index(['account_id', 'date'], 'idx_transactions_account_date');
            $table->index(['category_id', 'date'], 'idx_transactions_category_date');
            $table->index(['date', 'account_id'], 'idx_transactions_date_account');

            // Transfer-related
            $table->index('reference_id', 'idx_transactions_reference_id');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex('idx_transactions_account_id');
            $table->dropIndex('idx_transactions_category_id');
            $table->dropIndex('idx_transactions_date');
            $table->dropIndex('idx_transactions_created_at');
            $table->dropIndex('idx_transactions_account_date');
            $table->dropIndex('idx_transactions_category_date');
            $table->dropIndex('idx_transactions_date_account');
            $table->dropIndex('idx_transactions_reference_id');
        });
    }
};
