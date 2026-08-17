<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Enhance Sale Headers ──────────────────────────────────────────
        Schema::table('sale_headers', function (Blueprint $table) {
            if (!Schema::hasColumn('sale_headers', 'invoice_no')) {
                $table->string('invoice_no', 60)->nullable()->after('sale_id');
            }
            if (!Schema::hasColumn('sale_headers', 'payment_status')) {
                $table->string('payment_status', 30)->default('PAID')->after('grand_total');
            }
            if (!Schema::hasColumn('sale_headers', 'idempotency_key')) {
                $table->string('idempotency_key', 64)->nullable()->after('status');
            }
            if (!Schema::hasColumn('sale_headers', 'notes')) {
                $table->text('notes')->nullable()->after('idempotency_key');
            }
        });

        // ── 2. Enhance Purchase Headers ──────────────────────────────────────
        Schema::table('purchase_headers', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_headers', 'reference_no')) {
                $table->string('reference_no', 60)->nullable()->after('purchase_id');
            }
            if (!Schema::hasColumn('purchase_headers', 'tax_amount')) {
                $table->decimal('tax_amount', 12, 2)->default(0.00)->after('total_amount');
            }
            if (!Schema::hasColumn('purchase_headers', 'grand_total')) {
                $table->decimal('grand_total', 12, 2)->default(0.00)->after('tax_amount');
            }
            if (!Schema::hasColumn('purchase_headers', 'notes')) {
                $table->text('notes')->nullable()->after('status');
            }
        });

        // ── 3. Enhance Stock Movements ───────────────────────────────────────
        Schema::table('stock_movements', function (Blueprint $table) {
            if (!Schema::hasColumn('stock_movements', 'stock_before')) {
                $table->integer('stock_before')->nullable()->after('quantity');
            }
            if (!Schema::hasColumn('stock_movements', 'stock_after')) {
                $table->integer('stock_after')->nullable()->after('stock_before');
            }
            if (!Schema::hasColumn('stock_movements', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('employee_id');
            }
        });

        // ── 4. Enhance Payments ──────────────────────────────────────────────
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'transaction_ref')) {
                $table->string('transaction_ref', 100)->nullable()->after('payment_status');
            }
            if (!Schema::hasColumn('payments', 'amount_tendered')) {
                $table->decimal('amount_tendered', 12, 2)->nullable()->after('amount');
            }
            if (!Schema::hasColumn('payments', 'change_due')) {
                $table->decimal('change_due', 12, 2)->nullable()->after('amount_tendered');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['transaction_ref', 'amount_tendered', 'change_due']);
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropColumn(['stock_before', 'stock_after', 'created_by']);
        });

        Schema::table('purchase_headers', function (Blueprint $table) {
            $table->dropColumn(['reference_no', 'tax_amount', 'grand_total', 'notes']);
        });

        Schema::table('sale_headers', function (Blueprint $table) {
            $table->dropColumn(['invoice_no', 'payment_status', 'idempotency_key', 'notes']);
        });
    }
};
