<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            $table->foreignId('brand_id')->nullable()->after('product_id')->constrained('brands', 'brand_id')->nullOnDelete();

            // Strict Deduplication Constraints
            // Prevents same brand+product having duplicate URLs
            $table->unique(['brand_id', 'product_id', 'image_url'], 'uk_brand_product_image_url');
        });
    }

    public function down(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            $table->dropUnique('uk_brand_product_image_url');
            $table->dropForeign(['brand_id']);
            $table->dropColumn('brand_id');
        });
    }
};
