<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('api_logs', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->nullable()->index();
            $table->string('token_name')->nullable();
            $table->string('method', 10);
            $table->string('path');
            $table->string('ip', 45)->nullable();
            $table->integer('status');
            $table->float('duration_ms');
            $table->integer('response_size')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_logs');
    }
};
