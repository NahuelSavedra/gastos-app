<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('transactions', 'description')) {
                $table->text('description')->nullable()->after('title');
            }

            // hacer nullable la columna date y setear default CURRENT_DATE (si el motor lo soporta)
            if (Schema::hasColumn('transactions', 'date')) {
                $table->date('date')->nullable()->default(DB::raw('CURRENT_DATE'))->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (Schema::hasColumn('transactions', 'description')) {
                $table->dropColumn('description');
            }
            if (Schema::hasColumn('transactions', 'date')) {
                $table->date('date')->nullable(false)->default(null)->change();
            }
        });
    }
};
