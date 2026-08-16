<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->index('product_name', 'idx_products_name');
            $table->index('brand', 'idx_products_brand');
            $table->index('status', 'idx_products_status');
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->index(['product_id', 'quantity'], 'idx_variants_product_qty');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('idx_products_name');
            $table->dropIndex('idx_products_brand');
            $table->dropIndex('idx_products_status');
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropIndex('idx_variants_product_qty');
        });
    }
};
