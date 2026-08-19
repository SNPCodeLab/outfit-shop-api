<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id('audit_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_type')->nullable(); // Employee, User
            $table->string('action'); // LOGIN, LOGOUT, CREATE, UPDATE, DELETE, SALE, PURCHASE, ADJUSTMENT
            $table->string('entity'); // Employee, Product, SaleHeader, etc.
            $table->string('entity_id')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
