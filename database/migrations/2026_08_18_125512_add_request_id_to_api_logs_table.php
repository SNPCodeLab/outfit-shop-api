<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('api_logs', function (Blueprint $table) {
            $table->uuid('request_id')->nullable()->unique()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('api_logs', function (Blueprint $table) {
            $table->dropColumn('request_id');
        });
    }
};
