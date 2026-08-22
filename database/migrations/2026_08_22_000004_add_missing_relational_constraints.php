<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Enforce missing relational integrity:
 *  - stock_movements.created_by becomes a real FK to employees
 *  - products gets a soft-delete-aware index backing the default listing
 *    query (WHERE deleted_at IS NULL ORDER BY created_at DESC) tracked in
 *    indexing.todo
 */
return new class extends Migration
{
    public function up(): void
    {
        // Null out orphaned created_by references before constraining
        $orphanIds = DB::table('stock_movements')
            ->whereNotNull('created_by')
            ->whereNotIn('created_by', DB::table('employees')->select('employee_id'))
            ->pluck('movement_id');
        if ($orphanIds->isNotEmpty()) {
            DB::table('stock_movements')->whereIn('movement_id', $orphanIds)->update(['created_by' => null]);
        }

        Schema::table('stock_movements', function (Blueprint $table) {
            $fks = Schema::getForeignKeys('stock_movements');
            if (! collect($fks)->contains(fn ($fk) => in_array('created_by', $fk['columns'] ?? []))) {
                $table->foreign('created_by')
                    ->references('employee_id')
                    ->on('employees')
                    ->nullOnDelete();
            }
        });

        Schema::table('products', function (Blueprint $table) {
            $indexes = collect(Schema::getIndexes('products'))->pluck('name');
            if (! $indexes->contains('idx_products_active_recent')) {
                $table->index(['deleted_at', 'created_at'], 'idx_products_active_recent');
            }
        });
    }

    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $fks = Schema::getForeignKeys('stock_movements');
            if (collect($fks)->contains(fn ($fk) => in_array('created_by', $fk['columns'] ?? []))) {
                $table->dropForeign(['created_by']);
            }
        });

        Schema::table('products', function (Blueprint $table) {
            $indexes = collect(Schema::getIndexes('products'))->pluck('name');
            if ($indexes->contains('idx_products_active_recent')) {
                $table->dropIndex('idx_products_active_recent');
            }
        });
    }
};
