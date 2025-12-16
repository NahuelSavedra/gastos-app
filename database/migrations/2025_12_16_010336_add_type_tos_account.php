<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->string('account_type')->default('checking')->after('name');
            $table->string('color')->nullable()->after('account_type');
            $table->string('icon')->nullable()->after('color');
            $table->text('description')->nullable()->after('icon');
            $table->boolean('include_in_totals')->default(true)->after('description');

            // Renombrar balance a initial_balance si todavía se llama balance
            if (Schema::hasColumn('accounts', 'balance') && !Schema::hasColumn('accounts', 'initial_balance')) {
                $table->renameColumn('balance', 'initial_balance');
            } elseif (!Schema::hasColumn('accounts', 'initial_balance')) {
                $table->decimal('initial_balance', 15, 2)->default(0)->after('include_in_totals');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn([
                'account_type',
                'color',
                'icon',
                'description',
                'include_in_totals'
            ]);
        });
    }
};
