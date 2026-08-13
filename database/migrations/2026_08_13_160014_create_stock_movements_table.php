<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id('movement_id');
            $table->foreignId('variant_id')->constrained('product_variants', 'variant_id');
            $table->string('movement_type'); // PURCHASE, SALE, RETURN_IN, RETURN_OUT, ADJUSTMENT
            $table->integer('quantity');
            $table->timestamp('movement_date')->useCurrent();
            $table->string('reference_type')->nullable(); // PURCHASE_HEADER, SALE_HEADER, MANUAL
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('note')->nullable();
            $table->foreignId('employee_id')->nullable()->constrained('employees', 'employee_id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
