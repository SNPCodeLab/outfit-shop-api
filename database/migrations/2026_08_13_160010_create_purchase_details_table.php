<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_details', function (Blueprint $table) {
            $table->id('purchase_detail_id');
            $table->foreignId('purchase_id')->constrained('purchase_headers', 'purchase_id')->onDelete('cascade');
            $table->foreignId('variant_id')->constrained('product_variants', 'variant_id');
            $table->integer('quantity');
            $table->decimal('cost_price', 12, 2);
            $table->decimal('sub_total', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_details');
    }
};
