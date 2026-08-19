<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id('payment_id');
            $table->foreignId('sale_id')->constrained('sale_headers', 'sale_id')->onDelete('cascade');
            $table->timestamp('payment_date')->useCurrent();
            $table->decimal('amount', 12, 2);
            $table->string('payment_method')->default('CASH'); // CASH, CARD, QR, ABA
            $table->string('payment_status')->default('PAID'); // PAID, PENDING, REFUNDED
            $table->string('reference_number')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
