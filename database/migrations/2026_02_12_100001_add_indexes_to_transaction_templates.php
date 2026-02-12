<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaction_templates', function (Blueprint $table) {
            $table->index('is_active', 'idx_templates_is_active');
            $table->index(['is_active', 'is_recurring'], 'idx_templates_active_recurring');
            $table->index(['account_id', 'category_id'], 'idx_templates_account_category');
            $table->index(['is_recurring', 'auto_create', 'is_active'], 'idx_templates_auto_generation');
        });
    }

    public function down(): void
    {
        Schema::table('transaction_templates', function (Blueprint $table) {
            $table->dropIndex('idx_templates_is_active');
            $table->dropIndex('idx_templates_active_recurring');
            $table->dropIndex('idx_templates_account_category');
            $table->dropIndex('idx_templates_auto_generation');
        });
    }
};
