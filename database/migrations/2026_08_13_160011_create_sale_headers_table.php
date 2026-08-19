<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_headers', function (Blueprint $table) {
            $table->id('sale_id');
            $table->foreignId('customer_id')->nullable()->constrained('customers', 'customer_id');
            $table->foreignId('employee_id')->constrained('employees', 'employee_id');
            $table->timestamp('sale_date')->useCurrent();
            $table->decimal('total_amount', 12, 2)->default(0.00);
            $table->decimal('discount', 12, 2)->default(0.00);
            $table->decimal('tax_rate', 5, 2)->default(10.00);
            $table->decimal('tax_amount', 12, 2)->default(0.00);
            $table->decimal('grand_total', 12, 2)->default(0.00);
            $table->string('status')->default('COMPLETED'); // COMPLETED, ESTIMATE, VOIDED, REFUNDED
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_headers');
    }
};
