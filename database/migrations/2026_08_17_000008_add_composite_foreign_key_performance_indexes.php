<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations to add composite indexes for foreign keys, timestamps, and multi-column queries.
     */
    public function up(): void
    {
        // 1. Sale Headers Composite Indexes
        Schema::table('sale_headers', function (Blueprint $table) {
            if (Schema::hasColumn('sale_headers', 'employee_id') && Schema::hasColumn('sale_headers', 'status')) {
                $table->index(['employee_id', 'status', 'sale_date'], 'idx_sales_emp_status_date');
            }
            if (Schema::hasColumn('sale_headers', 'customer_id')) {
                $table->index(['customer_id', 'sale_date'], 'idx_sales_cust_date');
            }
            if (Schema::hasColumn('sale_headers', 'status')) {
                $table->index(['status', 'sale_date'], 'idx_sales_status_date');
            }
            if (Schema::hasColumn('sale_headers', 'invoice_no')) {
                $table->index('invoice_no', 'idx_sales_invoice_no');
            }
        });

        // 2. Sale Details Composite Indexes
        Schema::table('sale_details', function (Blueprint $table) {
            $table->index(['sale_id', 'variant_id'], 'idx_sale_details_sale_variant');
        });

        // 3. Stock Movements Composite Indexes
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->index(['variant_id', 'movement_date'], 'idx_movements_variant_date');
            $table->index(['movement_type', 'movement_date'], 'idx_movements_type_date');
            if (Schema::hasColumn('stock_movements', 'reference_type') && Schema::hasColumn('stock_movements', 'reference_id')) {
                $table->index(['reference_type', 'reference_id'], 'idx_movements_ref_type_id');
            }
        });

        // 4. Product Variants Composite Indexes
        Schema::table('product_variants', function (Blueprint $table) {
            $table->index(['product_id', 'size_id', 'color_id'], 'idx_variants_prod_size_color');
            if (Schema::hasColumn('product_variants', 'barcode')) {
                $table->index('barcode', 'idx_variants_barcode');
            }
        });

        // 5. Purchase Headers Composite Indexes
        Schema::table('purchase_headers', function (Blueprint $table) {
            $table->index(['supplier_id', 'purchase_date'], 'idx_purchases_supplier_date');
            if (Schema::hasColumn('purchase_headers', 'status')) {
                $table->index(['status', 'purchase_date'], 'idx_purchases_status_date');
            }
        });

        // 6. Purchase Details Composite Indexes
        Schema::table('purchase_details', function (Blueprint $table) {
            $table->index(['purchase_id', 'variant_id'], 'idx_purch_details_purch_variant');
        });

        // 7. Payments Composite Indexes
        Schema::table('payments', function (Blueprint $table) {
            $table->index(['sale_id', 'payment_method'], 'idx_payments_sale_method');
        });

        // 8. POS Shifts Composite Indexes
        if (Schema::hasTable('pos_shifts')) {
            Schema::table('pos_shifts', function (Blueprint $table) {
                if (Schema::hasColumn('pos_shifts', 'employee_id') && Schema::hasColumn('pos_shifts', 'status')) {
                    $table->index(['employee_id', 'status'], 'idx_shifts_employee_status');
                }
            });
        }

        // 9. API Logs Composite Indexes
        if (Schema::hasTable('api_logs')) {
            Schema::table('api_logs', function (Blueprint $table) {
                $table->index(['status', 'created_at'], 'idx_api_logs_status_created');
                $table->index(['path', 'method'], 'idx_api_logs_path_method');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sale_headers', function (Blueprint $table) {
            $table->dropIndex('idx_sales_emp_status_date');
            $table->dropIndex('idx_sales_cust_date');
            $table->dropIndex('idx_sales_status_date');
            $table->dropIndex('idx_sales_invoice_no');
        });

        Schema::table('sale_details', function (Blueprint $table) {
            $table->dropIndex('idx_sale_details_sale_variant');
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropIndex('idx_movements_variant_date');
            $table->dropIndex('idx_movements_type_date');
            $table->dropIndex('idx_movements_ref_type_id');
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropIndex('idx_variants_prod_size_color');
            $table->dropIndex('idx_variants_barcode');
        });

        Schema::table('purchase_headers', function (Blueprint $table) {
            $table->dropIndex('idx_purchases_supplier_date');
            $table->dropIndex('idx_purchases_status_date');
        });

        Schema::table('purchase_details', function (Blueprint $table) {
            $table->dropIndex('idx_purch_details_purch_variant');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('idx_payments_sale_method');
        });

        if (Schema::hasTable('pos_shifts')) {
            Schema::table('pos_shifts', function (Blueprint $table) {
                $table->dropIndex('idx_shifts_employee_status');
            });
        }

        if (Schema::hasTable('api_logs')) {
            Schema::table('api_logs', function (Blueprint $table) {
                $table->dropIndex('idx_api_logs_status_created');
                $table->dropIndex('idx_api_logs_path_method');
            });
        }
    }
};
