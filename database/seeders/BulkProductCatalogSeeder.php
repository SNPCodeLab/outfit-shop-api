<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\ClothingSize;
use App\Models\Color;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Ultra-high performance batch bulk seeder for OutfitShop SS-MIS.
 * Seeds all 24 brands, categories, products, variants, and image gallery mappings
 * from Desktop/product-items in batched multi-row chunks.
 */
class BulkProductCatalogSeeder extends Seeder
{
    private const BRAND_METADATA = [
        'Louis Vuitton' => ['origin' => 'France', 'desc' => 'Iconic French luxury fashion house renowned for leather goods, haute couture, and trunk craftsmanship.'],
        'Stussy' => ['origin' => 'United States', 'desc' => 'Pioneering Californian streetwear brand fusing surf, skate, and hip-hop culture.'],
        'GitHub' => ['origin' => 'United States', 'desc' => 'Official developer merchandise, Invertocat apparel, and tech collectibles.'],
        'Lululemon' => ['origin' => 'Canada', 'desc' => 'Premium technical athletic apparel for yoga, running, and training.'],
        'Palm Angels' => ['origin' => 'Italy', 'desc' => 'Italian luxury streetwear label capturing the spirit of Los Angeles skate culture.'],
        'Pleasures' => ['origin' => 'United States', 'desc' => 'Los Angeles streetwear brand rooted in punk, grunge, and 90s music subcultures.'],
        'Icecream' => ['origin' => 'United States', 'desc' => 'Playful streetwear and skate apparel by Pharrell Williams and NIGO.'],
        'Adidas' => ['origin' => 'Germany', 'desc' => 'Global sportswear giant blending athletic performance with streetwear style.'],
        'Google Store' => ['origin' => 'United States', 'desc' => 'Official Google hardware, developer merchandise, and tech lifestyle apparel.'],
        'Maison Margiela' => ['origin' => 'France', 'desc' => 'Avant-garde Parisian fashion house celebrated for deconstructive luxury.'],
        'Godspeed' => ['origin' => 'United States', 'desc' => 'Contemporary graphic streetwear and limited edition apparel.'],
        'Reese Cooper' => ['origin' => 'United States', 'desc' => 'American luxury heritage wear and utilitarian storytelling design.'],
        'Tesla' => ['origin' => 'United States', 'desc' => 'Official Tesla apparel, Cybercab merch, and minimalist tech lifestyle goods.'],
        'xAI Grok' => ['origin' => 'United States', 'desc' => 'Official xAI & Grok apparel, trucker caps, thermal beanies, and drinkware.'],
        'Fear of God' => ['origin' => 'United States', 'desc' => 'Jerry Lorenzo luxury streetwear and timeless Essentials collections.'],
        'Puma' => ['origin' => 'Germany', 'desc' => 'Sportswear and football-inspired lifestyle collections.'],
        'Honour The Gift' => ['origin' => 'United States', 'desc' => 'Russell Westbrook personal streetwear imprint celebrating inner-city resilience.'],
        'Market' => ['origin' => 'United States', 'desc' => 'Cult streetwear and graphic-forward lifestyle brand.'],
        'Nike' => ['origin' => 'United States', 'desc' => 'World-renowned athletics and sneaker culture pioneer.'],
        'Kids Worldwide' => ['origin' => 'United States', 'desc' => 'Youth and children fashion elevating community expression.'],
        'NBA' => ['origin' => 'United States', 'desc' => 'Official National Basketball Association lifestyle and fan apparel.'],
        'Born x Raised' => ['origin' => 'United States', 'desc' => 'Venice Beach streetwear brand honoring authentic Los Angeles culture.'],
        'Jordan' => ['origin' => 'United States', 'desc' => 'Legendary basketball footwear and athletic heritage brand.'],
        'The Boring Company' => ['origin' => 'United States', 'desc' => 'Official industrial tech apparel, Cutterhead hats, and tunnel gear.'],
    ];

    public function run(): void
    {
        $this->command?->info('Preparing High-Speed Batch Seeding from Desktop/product-items...');

        $rootDir = '/Users/Apple16/Desktop/product-items';
        $manifestPath = base_path('docs/catalog/master_catalog_manifest.json');

        if (! is_dir($rootDir) && ! file_exists($manifestPath)) {
            $this->command?->error('Neither Desktop/product-items nor docs/catalog/master_catalog_manifest.json was found.');

            return;
        }

        $now = Carbon::now();

        // ── 1. Reference Data: Sizes ──────────────────────────────────────────
        $sizesMap = [
            'XS' => 'Extra Small (XS)',
            'S' => 'Small (S)',
            'M' => 'Medium (M)',
            'L' => 'Large (L)',
            'XL' => 'Extra Large (XL)',
            'XXL' => 'Double Extra Large (XXL)',
            'OS' => 'One Size (OS)',
        ];

        foreach ($sizesMap as $key => $name) {
            ClothingSize::firstOrCreate(['size_name' => $name], ['description' => $key === 'OS' ? 'Universal / Free Size' : "Standard {$key}"]);
        }
        $sizes = ClothingSize::all()->pluck('size_id', 'size_name')->toArray();

        // ── 2. Reference Data: Colors ─────────────────────────────────────────
        $colorsList = [
            'Classic Black' => '#000000',
            'Pure White' => '#FFFFFF',
            'Navy Blue' => '#000080',
            'Charcoal Gray' => '#4A4A4A',
            'Vintage Green' => '#2E8B57',
            'Crimson Red' => '#DC143C',
            'Sand / Beige' => '#F5F5DC',
        ];

        foreach ($colorsList as $cName => $hex) {
            Color::firstOrCreate(['color_name' => $cName], ['description' => $hex]);
        }
        $colorBlackId = Color::where('color_name', 'Classic Black')->value('color_id') ?? 1;

        // ── 3. Brands ─────────────────────────────────────────────────────────
        foreach (self::BRAND_METADATA as $brandName => $meta) {
            Brand::firstOrCreate(
                ['brand_name' => $brandName],
                [
                    'slug' => Str::slug($brandName),
                    'country_of_origin' => $meta['origin'],
                    'description' => $meta['desc'],
                    'is_featured' => in_array($brandName, ['Louis Vuitton', 'Stussy', 'GitHub', 'Lululemon', 'Tesla', 'xAI Grok']),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
        $brands = Brand::all()->keyBy('brand_name');

        // ── 4. Categories ─────────────────────────────────────────────────────
        $categoryNames = [
            'T-Shirts & Tops' => 'Graphic tees, polos, jerseys, long sleeves, and shirts',
            'Hoodies & Sweatshirts' => 'Pullover hoodies, crewnecks, sweaters, and fleece',
            'Jackets & Outerwear' => 'Track jackets, coats, windbreakers, and bombers',
            'Pants & Shorts' => 'Denim jeans, sweatpants, track pants, trousers, and shorts',
            'Footwear & Sneakers' => 'Luxury sneakers, runners, slides, and footwear',
            'Hats & Headwear' => 'Caps, trucker hats, 5-panel hats, beanies, and headwear',
            'Bags & Luggage' => 'Totes, backpacks, crossbody bags, handbags, and travel luggage',
            'Drinkware & Bottles' => 'Camp mugs, travel tumblers, bottles, and drinkware',
            'Stickers & Decals' => 'Sticker packs, vinyl decals, and collectible pins',
            'Accessories & Lifestyle' => 'Keychains, plushes, desk mats, lifestyle goods, and collectibles',
            "Men's Activewear" => 'Technical performance activewear, training shorts, and tops for men',
            "Women's Activewear" => 'Yoga tights, leggings, athletic tops, and activewear for women',
            'Ready-to-Wear & Luxury Goods' => 'High fashion runway apparel and bespoke luxury goods',
            'Streetwear & Graphic Tops' => 'Classic streetwear silhouettes and graphic lookbooks',
            'Apparel & Merchandise' => 'Official branded merchandise and tech lifestyle apparel',
        ];

        foreach ($categoryNames as $catName => $catDesc) {
            Category::firstOrCreate(
                ['category_name' => $catName],
                [
                    'slug' => Str::slug($catName),
                    'description' => $catDesc,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
        $categories = Category::all()->keyBy('category_name');
        $defaultCatId = $categories['Apparel & Merchandise']->category_id ?? 1;

        // ── 5. Ingest and Prepare Product Batches ──────────────────────────────
        $brandDirs = glob($rootDir.'/*', GLOB_ONLYDIR) ?: [];

        $productsBatch = [];
        $tempProductMetadata = [];

        $productIdCounter = 1;

        foreach ($brandDirs as $brandDir) {
            $brandName = basename($brandDir);
            if (! isset($brands[$brandName])) {
                continue;
            }
            $brandModel = $brands[$brandName];

            $catDirs = glob($brandDir.'/*', GLOB_ONLYDIR) ?: [];
            foreach ($catDirs as $catDir) {
                $catName = basename($catDir);
                $categoryModel = $categories[$catName] ?? null;
                $catId = $categoryModel ? $categoryModel->category_id : $defaultCatId;

                $imageFiles = glob($catDir.'/*.*') ?: [];
                $productGroups = [];
                foreach ($imageFiles as $imgFile) {
                    $filename = basename($imgFile);
                    if (str_starts_with($filename, '.')) {
                        continue;
                    }
                    $nameNoExt = pathinfo($filename, PATHINFO_FILENAME);
                    $baseTitle = preg_replace('/_[0-9]+$/', '', $nameNoExt);
                    $baseTitle = trim($baseTitle);

                    $productGroups[$baseTitle][] = $filename;
                }

                foreach ($productGroups as $productTitle => $files) {
                    $basePrice = match ($brandName) {
                        'Louis Vuitton', 'Maison Margiela' => rand(450, 1850),
                        'Palm Angels', 'Fear of God', 'Reese Cooper' => rand(180, 480),
                        'Lululemon', 'Stussy', 'Pleasures', 'Icecream', 'Godspeed', 'Born x Raised', 'Honour The Gift' => rand(65, 160),
                        'Tesla', 'xAI Grok', 'The Boring Company' => rand(35, 120),
                        'GitHub', 'Google Store' => rand(25, 75),
                        default => rand(40, 120),
                    };

                    $primaryFile = $files[0];
                    $cloudinaryUrl = "https://res.cloudinary.com/od8t271n/image/upload/v1787064621/product-items/{$brandModel->slug}/".rawurlencode($primaryFile);

                    $productsBatch[] = [
                        'category_id' => $catId,
                        'brand_id' => $brandModel->brand_id,
                        'product_type' => 'PHYSICAL_APPAREL',
                        'product_name' => $productTitle,
                        'brand' => $brandModel->brand_name,
                        'gender' => str_contains(strtolower($catName), 'women') ? 'WOMEN' : (str_contains(strtolower($catName), 'men') ? 'MEN' : 'UNISEX'),
                        'material_fabric' => 'Premium Fabric & Materials',
                        'season_collection' => 'Annual Collection 2026',
                        'description' => "Official {$brandModel->brand_name} {$productTitle} featuring authentic craftsmanship.",
                        'image_url' => $cloudinaryUrl,
                        'featured_badge' => $brandModel->is_featured ? 'FEATURED' : null,
                        'status' => 'ACTIVE',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];

                    $tempProductMetadata[] = [
                        'brand_slug' => $brandModel->slug,
                        'cat_name' => $catName,
                        'base_price' => $basePrice,
                        'files' => $files,
                        'cloudinary_url' => $cloudinaryUrl,
                    ];
                }
            }
        }

        $this->command?->info('Inserting '.count($productsBatch).' products into database in chunks...');

        DB::beginTransaction();

        try {
            // Insert Products in Chunks
            foreach (array_chunk($productsBatch, 200) as $chunk) {
                DB::table('products')->insert($chunk);
            }

            // Fetch all inserted products ordered by ID
            $insertedProducts = DB::table('products')
                ->select('product_id', 'product_name', 'brand_id')
                ->orderBy('product_id')
                ->get();

            $variantsBatch = [];
            $imagesBatch = [];

            foreach ($insertedProducts as $idx => $prod) {
                $meta = $tempProductMetadata[$idx] ?? null;
                if (! $meta) {
                    continue;
                }

                $pId = $prod->product_id;
                $basePrice = $meta['base_price'];
                $costPrice = round($basePrice * 0.45, 2);
                $wholesalePrice = round($basePrice * 0.8, 2);
                $brandSlug = $meta['brand_slug'];
                $catName = $meta['cat_name'];

                $isOneSize = in_array($catName, ['Hats & Headwear', 'Drinkware & Bottles', 'Stickers & Decals', 'Bags & Luggage', 'Accessories & Lifestyle']);
                $sizeKeys = $isOneSize ? ['OS'] : ['S', 'M', 'L', 'XL'];

                foreach ($sizeKeys as $sKey) {
                    $sizeName = $sizesMap[$sKey] ?? 'Small (S)';
                    $sizeId = $sizes[$sizeName] ?? 1;
                    $sku = strtoupper(substr($brandSlug, 0, 3)).'-'.$pId.'-'.$sKey;

                    $variantsBatch[] = [
                        'product_id' => $pId,
                        'size_id' => $sizeId,
                        'color_id' => $colorBlackId,
                        'sku' => $sku,
                        'barcode' => '885'.str_pad((string) $pId, 6, '0', STR_PAD_LEFT).rand(100, 999),
                        'cost_price' => $costPrice,
                        'sale_price' => $basePrice,
                        'wholesale_price' => $wholesalePrice,
                        'quantity' => rand(15, 60),
                        'reorder_level' => 10,
                        'image_url' => $meta['cloudinary_url'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                foreach ($meta['files'] as $fIdx => $fName) {
                    $imgUrl = "https://res.cloudinary.com/od8t271n/image/upload/v1787064621/product-items/{$brandSlug}/".rawurlencode($fName);
                    $imagesBatch[] = [
                        'product_id' => $pId,
                        'image_url' => $imgUrl,
                        'is_primary' => ($fIdx === 0),
                        'sort_order' => $fIdx + 1,
                        'alt_text' => "{$prod->product_name} angle ".($fIdx + 1),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            // Insert Variants in Chunks
            $this->command?->info('Inserting '.count($variantsBatch).' product variants...');
            foreach (array_chunk($variantsBatch, 300) as $vChunk) {
                DB::table('product_variants')->insert($vChunk);
            }

            // Insert Product Images in Chunks
            $this->command?->info('Inserting '.count($imagesBatch).' product images...');
            foreach (array_chunk($imagesBatch, 300) as $iChunk) {
                DB::table('product_images')->insert($iChunk);
            }

            DB::commit();

            $this->command?->info('Bulk Seeding Completed Successfully!');
            $this->command?->info(' • Total Brands: '.count($brands));
            $this->command?->info(' • Total Categories: '.count($categories));
            $this->command?->info(' • Total Products: '.count($productsBatch));
            $this->command?->info(' • Total Variants: '.count($variantsBatch));
            $this->command?->info(' • Total Product Images: '.count($imagesBatch));

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->command?->error('Error during batch bulk seeding: '.$e->getMessage());
            throw $e;
        }
    }
}
