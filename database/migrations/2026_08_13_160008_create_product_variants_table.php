<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id('variant_id');
            $table->foreignId('product_id')->constrained('products', 'product_id')->onDelete('cascade');
            $table->foreignId('size_id')->constrained('clothing_sizes', 'size_id')->onDelete('cascade');
            $table->foreignId('color_id')->constrained('colors', 'color_id')->onDelete('cascade');
            $table->string('sku')->unique();
            $table->string('barcode')->unique()->nullable();
            $table->decimal('cost_price', 12, 2)->default(0.00);
            $table->decimal('sale_price', 12, 2)->default(0.00);
            $table->integer('quantity')->default(0);
            $table->integer('reorder_level')->default(5);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['product_id', 'size_id', 'color_id'], 'uq_product_size_color');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
