<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_headers', function (Blueprint $table) {
            $table->id('purchase_id');
            $table->foreignId('supplier_id')->constrained('suppliers', 'supplier_id');
            $table->foreignId('employee_id')->constrained('employees', 'employee_id');
            $table->timestamp('purchase_date')->useCurrent();
            $table->decimal('total_amount', 12, 2)->default(0.00);
            $table->string('status')->default('COMPLETED'); // COMPLETED, CANCELLED
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_headers');
    }
};
