<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Enhance Categories Table ──────────────────────────────────────
        Schema::table('categories', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->after('category_id')->constrained('categories', 'category_id')->nullOnDelete();
            $table->string('department_type', 50)->default('APPAREL')->after('slug');
            $table->string('image_url', 500)->nullable()->after('description');

            // Indexes
            $table->index('department_type', 'idx_categories_department');
            $table->index('parent_id', 'idx_categories_parent');
        });

        // ── 2. Enhance Products Table ────────────────────────────────────────
        Schema::table('products', function (Blueprint $table) {
            $table->string('product_type', 30)->default('PHYSICAL_APPAREL')->after('category_id'); // PHYSICAL_APPAREL, PHYSICAL_FMCG, DIGITAL_DOWNLOAD
            $table->string('gender', 20)->nullable()->after('brand'); // MEN, WOMEN, UNISEX, KIDS
            $table->string('material_fabric', 150)->nullable()->after('gender'); // Silk, Twill, Linen, Cotton
            $table->string('season_collection', 100)->nullable()->after('material_fabric'); // Spring/Summer 2026, Purple Label, US Open
            $table->string('author_artist', 150)->nullable()->after('season_collection'); // For books/publications
            $table->string('isbn_code', 50)->nullable()->after('author_artist');
            $table->string('featured_badge', 50)->nullable()->after('status'); // NEW_ARRIVAL, BEST_SELLER, LIMITED_EDITION

            // Search & Filter Performance Indexes
            $table->index('product_type', 'idx_products_type');
            $table->index('gender', 'idx_products_gender');
            $table->index('season_collection', 'idx_products_season');
        });

        // ── 3. Enhance Product Variants Table ────────────────────────────────
        Schema::table('product_variants', function (Blueprint $table) {
            // Make size_id and color_id nullable (for FMCG, beverages, digital books)
            $table->foreignId('size_id')->nullable()->change();
            $table->foreignId('color_id')->nullable()->change();

            // Packaging & FMCG Attributes
            $table->string('unit_of_measure', 30)->default('PIECE')->after('color_id'); // PIECE, BOTTLE, CAN, PACK_6, CARTON_24, BOX, PDF_DOWNLOAD
            $table->string('volume_or_weight', 50)->nullable()->after('unit_of_measure'); // 330ml, 500ml, 1.5L, 250g
            $table->decimal('alcohol_by_volume', 4, 2)->nullable()->after('volume_or_weight'); // 5.00% ABV
            $table->string('download_file_url', 500)->nullable()->after('image_public_id'); // For digital book PDFs
            $table->decimal('wholesale_price', 12, 2)->nullable()->after('sale_price');

            // Indexes
            $table->index('unit_of_measure', 'idx_variants_uom');
        });

        // ── 4. Create Product Images Gallery Table ───────────────────────────
        Schema::create('product_images', function (Blueprint $table) {
            $table->id('image_id');
            $table->foreignId('product_id')->constrained('products', 'product_id')->cascadeOnDelete();
            $table->foreignId('variant_id')->nullable()->constrained('product_variants', 'variant_id')->nullOnDelete();
            $table->string('image_url', 500);
            $table->string('image_public_id', 255)->nullable();
            $table->string('shot_type', 30)->default('LOOK'); // LOOK, FLAT, DETAIL, BANNER, COVER
            $table->string('alt_text', 255)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            // Indexes
            $table->index(['product_id', 'sort_order'], 'idx_product_images_order');
            $table->index('shot_type', 'idx_product_images_shot');
        });

        // ── 5. Create Product Expiry Batches Table ────────────────────────────
        Schema::create('product_batches', function (Blueprint $table) {
            $table->id('batch_id');
            $table->foreignId('variant_id')->constrained('product_variants', 'variant_id')->cascadeOnDelete();
            $table->string('batch_number', 100);
            $table->date('manufacturing_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->integer('quantity_received');
            $table->integer('quantity_remaining');
            $table->string('status', 30)->default('ACTIVE'); // ACTIVE, EXPIRED, QUARANTINED
            $table->timestamps();

            // Indexes
            $table->index(['variant_id', 'expiry_date'], 'idx_batches_variant_expiry');
            $table->index('batch_number', 'idx_batches_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_batches');
        Schema::dropIfExists('product_images');

        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropIndex('idx_variants_uom');
            $table->dropColumn([
                'unit_of_measure',
                'volume_or_weight',
                'alcohol_by_volume',
                'download_file_url',
                'wholesale_price',
            ]);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('idx_products_type');
            $table->dropIndex('idx_products_gender');
            $table->dropIndex('idx_products_season');
            $table->dropColumn([
                'product_type',
                'gender',
                'material_fabric',
                'season_collection',
                'author_artist',
                'isbn_code',
                'featured_badge',
            ]);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex('idx_categories_department');
            $table->dropIndex('idx_categories_parent');
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['parent_id', 'department_type', 'image_url']);
        });
    }
};
