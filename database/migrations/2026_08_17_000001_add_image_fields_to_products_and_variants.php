<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('image_url', 500)->nullable()->after('description');
            $table->string('image_public_id', 255)->nullable()->after('image_url');
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->string('image_url', 500)->nullable()->after('barcode');
            $table->string('image_public_id', 255)->nullable()->after('image_url');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['image_url', 'image_public_id']);
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn(['image_url', 'image_public_id']);
        });
    }
};
