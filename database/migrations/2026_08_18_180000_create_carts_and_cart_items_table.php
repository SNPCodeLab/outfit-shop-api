<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id('cart_id');
            $table->string('session_id', 100)->nullable();
            $table->foreignId('customer_id')->nullable()->constrained('customers', 'customer_id')->nullOnDelete();
            $table->string('status', 30)->default('ACTIVE'); // ACTIVE, CHECKED_OUT, ABANDONED
            $table->string('currency', 10)->default('USD');
            $table->timestamps();

            $table->index('session_id', 'idx_carts_session');
            $table->index('customer_id', 'idx_carts_customer');
            $table->index(['status', 'updated_at'], 'idx_carts_status');
        });

        Schema::create('cart_items', function (Blueprint $table) {
            $table->id('cart_item_id');
            $table->foreignId('cart_id')->constrained('carts', 'cart_id')->cascadeOnDelete();
            $table->foreignId('variant_id')->constrained('product_variants', 'variant_id')->cascadeOnDelete();
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 12, 2)->nullable();
            $table->timestamps();

            $table->unique(['cart_id', 'variant_id'], 'uk_cart_variant');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
    }
};
