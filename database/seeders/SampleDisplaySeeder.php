<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\ClothingSize;
use App\Models\Color;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SampleDisplaySeeder extends Seeder
{
    public function run(): void
    {
        // ─────────────────────────────────────────────────────────────────────
        // 1. MASTER CATEGORIES (Covering All Future Product Types)
        // ─────────────────────────────────────────────────────────────────────
        $allCategories = [
            // Luxury Apparel & Fashion
            ['name' => 'Dresses & Evening Gowns',      'slug' => 'dresses-gowns',          'type' => 'STANDARD_PHYSICAL', 'desc' => 'High-end silk evening gowns and crinoline dresses.'],
            ['name' => 'Tops & Silk Blouses',          'slug' => 'tops-silk-blouses',      'type' => 'STANDARD_PHYSICAL', 'desc' => 'Tailored embroidered shirts, poplin blouses, and silk tops.'],
            ['name' => 'Bottoms, Shorts & Culottes',   'slug' => 'bottoms-culottes',       'type' => 'STANDARD_PHYSICAL', 'desc' => 'Monogram silk culottes, tailored shorts, and twill trousers.'],
            ['name' => 'Skirts & Flare Skirts',        'slug' => 'skirts-flares',          'type' => 'STANDARD_PHYSICAL', 'desc' => 'Crinoline flare skirts and fluted knit midi skirts.'],
            ['name' => 'Outerwear & Tailored Jackets', 'slug' => 'outerwear-jackets',      'type' => 'STANDARD_PHYSICAL', 'desc' => 'Collared tailored jackets and luxury outerwear.'],
            ['name' => 'Knitwear & Sweaters',          'slug' => 'knitwear-sweaters',      'type' => 'STANDARD_PHYSICAL', 'desc' => 'Fine-gauge knit sweaters and woven wool tops.'],
            ['name' => 'Luxury Leather Bags',          'slug' => 'luxury-bags',            'type' => 'STANDARD_PHYSICAL', 'desc' => 'Mini T-Lock crossbody bags, leather totes, and clutches.'],
            ['name' => 'Footwear & Leather Shoes',     'slug' => 'footwear-shoes',         'type' => 'STANDARD_PHYSICAL', 'desc' => 'T-Strap nappa leather sandals and buttercup ballerinas.'],
            ['name' => 'Monogram Silk Accessories',    'slug' => 'monogram-accessories',   'type' => 'STANDARD_PHYSICAL', 'desc' => 'Embroidered monogram silk scarves and luxury accessories.'],
            ['name' => "Men's Classic Polos",          'slug' => 'mens-classic-polos',     'type' => 'STANDARD_PHYSICAL', 'desc' => "Designer classic embroidered polo shirts and activewear."],
            
            // FMCG, Drinks & Supermarket
            ['name' => 'Craft Beers & Cold Beverages', 'slug' => 'beverages-drinks',       'type' => 'FMCG_EXPIRABLE',    'desc' => 'Premium Cambodian craft beers, sodas, and mineral water.'],
            ['name' => 'Packaged Foods & Snacks',      'slug' => 'packaged-foods',         'type' => 'FMCG_EXPIRABLE',    'desc' => 'Consumer packaged snacks and gourmet pantry foods.'],
            
            // Digital Publications & eBooks
            ['name' => 'Digital eBooks & Publications','slug' => 'digital-ebooks',         'type' => 'DIGITAL_DOWNLOAD',  'desc' => 'Downloadable technical books, design guides, and eBooks.'],
            
            // Cosmetics & Wellness
            ['name' => 'Skincare & Cosmetics',         'slug' => 'skincare-cosmetics',     'type' => 'FMCG_EXPIRABLE',    'desc' => 'Luxury skincare, face serums, and beauty cosmetics.'],
            ['name' => 'Pharmacy & Wellness',          'slug' => 'pharmacy-wellness',      'type' => 'FMCG_EXPIRABLE',    'desc' => 'Health vitamins, supplements, and daily wellness items.'],
        ];

        $categories = [];
        foreach ($allCategories as $cat) {
            $categories[$cat['slug']] = Category::firstOrCreate(
                ['slug' => $cat['slug']],
                [
                    'category_name' => $cat['name'],
                    'description'   => $cat['desc'],
                    'is_active'     => true,
                ]
            );
        }

        // ─────────────────────────────────────────────────────────────────────
        // 2. MASTER SIZES & MEASUREMENTS
        // ─────────────────────────────────────────────────────────────────────
        $sizes = [
            'S'   => ClothingSize::firstOrCreate(['size_code' => 'S'],   ['size_name' => 'Small', 'sort_order' => 1]),
            'M'   => ClothingSize::firstOrCreate(['size_code' => 'M'],   ['size_name' => 'Medium', 'sort_order' => 2]),
            'L'   => ClothingSize::firstOrCreate(['size_code' => 'L'],   ['size_name' => 'Large', 'sort_order' => 3]),
            'XL'  => ClothingSize::firstOrCreate(['size_code' => 'XL'],  ['size_name' => 'Extra Large', 'sort_order' => 4]),
            'STD' => ClothingSize::firstOrCreate(['size_code' => 'STD'], ['size_name' => 'Standard / Free Size', 'sort_order' => 5]),
            'PDF' => ClothingSize::firstOrCreate(['size_code' => 'PDF'], ['size_name' => 'Digital PDF Edition', 'sort_order' => 6]),
            'CAN' => ClothingSize::firstOrCreate(['size_code' => '330ML'],['size_name' => '330ml Can', 'sort_order' => 7]),
        ];

        // ─────────────────────────────────────────────────────────────────────
        // 3. MASTER LUXURY COLORS
        // ─────────────────────────────────────────────────────────────────────
        $colors = [
            'BLK' => Color::firstOrCreate(['color_code' => 'BLK'], ['color_name' => 'Noir Black',      'hex_code' => '#000000']),
            'IVR' => Color::firstOrCreate(['color_code' => 'IVR'], ['color_name' => 'Ivory White',     'hex_code' => '#FFFFF0']),
            'TER' => Color::firstOrCreate(['color_code' => 'TER'], ['color_name' => 'Terracotta Clay', 'hex_code' => '#E2725B']),
            'NVY' => Color::firstOrCreate(['color_code' => 'NVY'], ['color_name' => 'Midnight Navy',   'hex_code' => '#000080']),
            'SGE' => Color::firstOrCreate(['color_code' => 'SGE'], ['color_name' => 'Sage Green',      'hex_code' => '#87A96B']),
            'BGE' => Color::firstOrCreate(['color_code' => 'BGE'], ['color_name' => 'Cream Beige',     'hex_code' => '#F5F5DC']),
            'GLD' => Color::firstOrCreate(['color_code' => 'GLD'], ['color_name' => 'Canary Gold',     'hex_code' => '#FFD700']),
            'UNI' => Color::firstOrCreate(['color_code' => 'UNI'], ['color_name' => 'Standard Color',  'hex_code' => '#FFFFFF']),
        ];

        // ─────────────────────────────────────────────────────────────────────
        // 4. BRANDS
        // ─────────────────────────────────────────────────────────────────────
        $brandKhmeriel = Brand::firstOrCreate(
            ['slug' => 'khmeriel'],
            ['brand_name' => 'KhmeRiel Signature', 'country_of_origin' => 'Cambodia', 'is_active' => true]
        );
        $brandVattanac = Brand::firstOrCreate(
            ['slug' => 'vattanac'],
            ['brand_name' => 'Vattanac Brewery', 'country_of_origin' => 'Cambodia', 'is_active' => true]
        );
        $brandKwd = Brand::firstOrCreate(
            ['slug' => 'kwd-publishing'],
            ['brand_name' => 'KWD Publishing House', 'country_of_origin' => 'Cambodia', 'is_active' => true]
        );

        // ─────────────────────────────────────────────────────────────────────
        // 5. EXACTLY 9 DISPLAY PRODUCTS (3 VARIANTS EACH, 100% ENGLISH NAMES)
        // ─────────────────────────────────────────────────────────────────────
        $displayProducts = [
            // Product 1: Evening Gown
            [
                'cat_slug'     => 'dresses-gowns',
                'brand_id'     => $brandKhmeriel->brand_id,
                'name_en'      => 'KhmeRiel Silk Evening Column Gown',
                'desc_en'      => 'Floor-length pure silk evening column gown tailored for formal galas.',
                'image_url'    => 'https://res.cloudinary.com/od8t271n/image/upload/v1786905171/KHMERIEL_DRESSES_CRINOLINE_COLUMN_GOWN_TERRACOTTA_LOOK_cloth_096.png',
                'public_id'    => 'KHMERIEL_DRESSES_CRINOLINE_COLUMN_GOWN_TERRACOTTA_LOOK_cloth_096',
                'product_type' => 'STANDARD_PHYSICAL',
                'variants'     => [
                    ['size' => 'S', 'color' => 'TER', 'sku_prefix' => 'DRS-096-TER-S', 'cost' => 45.00, 'price' => 125.00, 'qty' => 18],
                    ['size' => 'M', 'color' => 'TER', 'sku_prefix' => 'DRS-096-TER-M', 'cost' => 45.00, 'price' => 125.00, 'qty' => 24],
                    ['size' => 'L', 'color' => 'BLK', 'sku_prefix' => 'DRS-096-BLK-L', 'cost' => 48.00, 'price' => 135.00, 'qty' => 12],
                ]
            ],
            // Product 2: Silk Blouse
            [
                'cat_slug'     => 'tops-silk-blouses',
                'brand_id'     => $brandKhmeriel->brand_id,
                'name_en'      => 'KhmeRiel Fluid Draped Silk Blouse',
                'desc_en'      => 'Relaxed silhouette fluid draped luxury blouse with flowing shoulder lines.',
                'image_url'    => 'https://res.cloudinary.com/od8t271n/image/upload/v1786905145/KHMERIEL_TOPS_FLUID_DRAPED_SILK_BLOUSE_CREAM_BEIGE_LOOK_cloth_012.png',
                'public_id'    => 'KHMERIEL_TOPS_FLUID_DRAPED_SILK_BLOUSE_CREAM_BEIGE_LOOK_cloth_012',
                'product_type' => 'STANDARD_PHYSICAL',
                'variants'     => [
                    ['size' => 'S', 'color' => 'BGE', 'sku_prefix' => 'TOP-012-BGE-S', 'cost' => 22.00, 'price' => 58.00, 'qty' => 35],
                    ['size' => 'M', 'color' => 'BGE', 'sku_prefix' => 'TOP-012-BGE-M', 'cost' => 22.00, 'price' => 58.00, 'qty' => 40],
                    ['size' => 'L', 'color' => 'IVR', 'sku_prefix' => 'TOP-012-IVR-L', 'cost' => 22.00, 'price' => 58.00, 'qty' => 28],
                ]
            ],
            // Product 3: Silk Culottes
            [
                'cat_slug'     => 'bottoms-culottes',
                'brand_id'     => $brandKhmeriel->brand_id,
                'name_en'      => 'KhmeRiel Monogram Silk PJ Culottes',
                'desc_en'      => 'Wide-leg silk lounge culottes featuring all-over monogram jacquard.',
                'image_url'    => 'https://res.cloudinary.com/od8t271n/image/upload/v1786905186/KHMERIEL_BOTTOMS_MONOGRAM_SILK_CULOTTES_IVORY_LOOK_cloth_277.png',
                'public_id'    => 'KHMERIEL_BOTTOMS_MONOGRAM_SILK_CULOTTES_IVORY_LOOK_cloth_277',
                'product_type' => 'STANDARD_PHYSICAL',
                'variants'     => [
                    ['size' => 'S', 'color' => 'IVR', 'sku_prefix' => 'BOT-277-IVR-S', 'cost' => 28.00, 'price' => 75.00, 'qty' => 20],
                    ['size' => 'M', 'color' => 'IVR', 'sku_prefix' => 'BOT-277-IVR-M', 'cost' => 28.00, 'price' => 75.00, 'qty' => 30],
                    ['size' => 'L', 'color' => 'BLK', 'sku_prefix' => 'BOT-277-BLK-L', 'cost' => 28.00, 'price' => 75.00, 'qty' => 22],
                ]
            ],
            // Product 4: Flare Skirt
            [
                'cat_slug'     => 'skirts-flares',
                'brand_id'     => $brandKhmeriel->brand_id,
                'name_en'      => 'KhmeRiel Crinoline Flare Midi Skirt',
                'desc_en'      => 'Architectural flare skirt crafted with structured crinoline weave.',
                'image_url'    => 'https://res.cloudinary.com/od8t271n/image/upload/v1786905197/KHMERIEL_SKIRTS_CRINOLINE_FLARE_SKIRT_TERRACOTTA_LOOK_cloth_298.png',
                'public_id'    => 'KHMERIEL_SKIRTS_CRINOLINE_FLARE_SKIRT_TERRACOTTA_LOOK_cloth_298',
                'product_type' => 'STANDARD_PHYSICAL',
                'variants'     => [
                    ['size' => 'S', 'color' => 'TER', 'sku_prefix' => 'SKT-298-TER-S', 'cost' => 30.00, 'price' => 88.00, 'qty' => 15],
                    ['size' => 'M', 'color' => 'TER', 'sku_prefix' => 'SKT-298-TER-M', 'cost' => 30.00, 'price' => 88.00, 'qty' => 25],
                    ['size' => 'L', 'color' => 'BLK', 'sku_prefix' => 'SKT-298-BLK-L', 'cost' => 30.00, 'price' => 88.00, 'qty' => 14],
                ]
            ],
            // Product 5: Tailored Jacket
            [
                'cat_slug'     => 'outerwear-jackets',
                'brand_id'     => $brandKhmeriel->brand_id,
                'name_en'      => 'KhmeRiel Collared Tailored Jacket',
                'desc_en'      => 'Single-breasted tailored outerwear jacket with structured shoulder pads.',
                'image_url'    => 'https://res.cloudinary.com/od8t271n/image/upload/v1786905170/KHMERIEL_OUTERWEAR_COLLARED_TAILORED_JACKET_SAGE_GREEN_LOOK_cloth_047.png',
                'public_id'    => 'KHMERIEL_OUTERWEAR_COLLARED_TAILORED_JACKET_SAGE_GREEN_LOOK_cloth_047',
                'product_type' => 'STANDARD_PHYSICAL',
                'variants'     => [
                    ['size' => 'M',  'color' => 'SGE', 'sku_prefix' => 'OUT-047-SGE-M', 'cost' => 60.00, 'price' => 165.00, 'qty' => 12],
                    ['size' => 'L',  'color' => 'SGE', 'sku_prefix' => 'OUT-047-SGE-L', 'cost' => 60.00, 'price' => 165.00, 'qty' => 16],
                    ['size' => 'XL', 'color' => 'BLK', 'sku_prefix' => 'OUT-047-BLK-XL','cost' => 62.00, 'price' => 175.00, 'qty' => 10],
                ]
            ],
            // Product 6: Knit Sweater
            [
                'cat_slug'     => 'knitwear-sweaters',
                'brand_id'     => $brandKhmeriel->brand_id,
                'name_en'      => 'KhmeRiel Fine Gauge Knit Sweater',
                'desc_en'      => 'Ultra-soft fine gauge knit crewneck sweater for trans-seasonal layering.',
                'image_url'    => 'https://res.cloudinary.com/od8t271n/image/upload/v1786905149/KHMERIEL_KNITWEAR_FINE_GAUGE_KNIT_SWEATER_TERRACOTTA_LOOK_cloth_349.png',
                'public_id'    => 'KHMERIEL_KNITWEAR_FINE_GAUGE_KNIT_SWEATER_TERRACOTTA_LOOK_cloth_349',
                'product_type' => 'STANDARD_PHYSICAL',
                'variants'     => [
                    ['size' => 'S', 'color' => 'TER', 'sku_prefix' => 'KNT-349-TER-S', 'cost' => 26.00, 'price' => 68.00, 'qty' => 25],
                    ['size' => 'M', 'color' => 'TER', 'sku_prefix' => 'KNT-349-TER-M', 'cost' => 26.00, 'price' => 68.00, 'qty' => 35],
                    ['size' => 'L', 'color' => 'IVR', 'sku_prefix' => 'KNT-349-IVR-L', 'cost' => 26.00, 'price' => 68.00, 'qty' => 20],
                ]
            ],
            // Product 7: Leather Crossbody Bag
            [
                'cat_slug'     => 'luxury-bags',
                'brand_id'     => $brandKhmeriel->brand_id,
                'name_en'      => 'KhmeRiel Mini T-Lock Leather Crossbody Bag',
                'desc_en'      => 'Italian-tanned smooth calfskin crossbody bag with polished metal T-lock closure.',
                'image_url'    => 'https://res.cloudinary.com/od8t271n/image/upload/v1786905158/KHMERIEL_BAGS_MINI_T_LOCK_CROSSBODY_BAG_BLACK_LOOK_cloth_128.png',
                'public_id'    => 'KHMERIEL_BAGS_MINI_T_LOCK_CROSSBODY_BAG_BLACK_LOOK_cloth_128',
                'product_type' => 'STANDARD_PHYSICAL',
                'variants'     => [
                    ['size' => 'STD', 'color' => 'BLK', 'sku_prefix' => 'BAG-128-BLK-STD', 'cost' => 55.00, 'price' => 145.00, 'qty' => 14],
                    ['size' => 'STD', 'color' => 'IVR', 'sku_prefix' => 'BAG-128-IVR-STD', 'cost' => 55.00, 'price' => 145.00, 'qty' => 16],
                    ['size' => 'STD', 'color' => 'TER', 'sku_prefix' => 'BAG-128-TER-STD', 'cost' => 55.00, 'price' => 145.00, 'qty' => 10],
                ]
            ],
            // Product 8: Craft Beer (FMCG Demonstration)
            [
                'cat_slug'     => 'beverages-drinks',
                'brand_id'     => $brandVattanac->brand_id,
                'name_en'      => 'Vattanac Premium Craft Beer 330ml',
                'desc_en'      => 'Crisp Cambodian luxury lager brewed from 100% pure malt and noble European hops.',
                'image_url'    => 'https://res.cloudinary.com/od8t271n/image/upload/v1786898754/KhmerRiel.png',
                'public_id'    => 'KhmerRiel_Beer_Vattanac_330ml',
                'product_type' => 'FMCG_EXPIRABLE',
                'variants'     => [
                    ['size' => 'CAN', 'color' => 'GLD', 'sku_prefix' => 'BEER-VAT-330-CAN',  'cost' => 0.85, 'price' => 1.75,  'qty' => 120],
                    ['size' => 'STD', 'color' => 'GLD', 'sku_prefix' => 'BEER-VAT-6PACK',    'cost' => 4.80, 'price' => 9.50,  'qty' => 40],
                    ['size' => 'STD', 'color' => 'GLD', 'sku_prefix' => 'BEER-VAT-CARTON24', 'cost' => 18.50,'price' => 32.00, 'qty' => 25],
                ]
            ],
            // Product 9: Digital Publication eBook (Digital Download Demonstration)
            [
                'cat_slug'     => 'digital-ebooks',
                'brand_id'     => $brandKwd->brand_id,
                'name_en'      => 'Cloud Networking Concepts Master Handbook',
                'desc_en'      => 'Complete digital handbook covering full-stack cloud infrastructure and networking protocols.',
                'image_url'    => 'https://res.cloudinary.com/od8t271n/image/upload/v1786905169/KHMERIEL_DIGITAL_BOOK_PUBLICATION_EN_A_TYPOLOGY_OF_VERBAL_BORROWINGS_JAN_WOHLGEMUTH_cloth_389.pdf',
                'public_id'    => 'KHMERIEL_DIGITAL_BOOK_PUBLICATION_EN_A_TYPOLOGY_OF_VERBAL_BORROWINGS_JAN_WOHLGEMUTH_cloth_389',
                'product_type' => 'DIGITAL_DOWNLOAD',
                'variants'     => [
                    ['size' => 'PDF', 'color' => 'UNI', 'sku_prefix' => 'EBOOK-NET-DIGITAL', 'cost' => 0.00, 'price' => 15.00, 'qty' => 999],
                    ['size' => 'STD', 'color' => 'UNI', 'sku_prefix' => 'EBOOK-NET-STUDENT', 'cost' => 0.00, 'price' => 9.99,  'qty' => 999],
                    ['size' => 'STD', 'color' => 'UNI', 'sku_prefix' => 'EBOOK-NET-ENTERPRISE','cost' => 0.00, 'price' => 49.00, 'qty' => 999],
                ]
            ],
        ];

        foreach ($displayProducts as $index => $pData) {
            $cat = $categories[$pData['cat_slug']];

            $product = Product::create([
                'product_name'     => $pData['name_en'],
                'slug'             => Str::slug($pData['name_en']) . '-' . ($index + 1),
                'category_id'      => $cat->category_id,
                'brand_id'         => $pData['brand_id'],
                'product_type'     => $pData['product_type'],
                'description'      => $pData['desc_en'],
                'image_url'        => $pData['image_url'],
                'image_public_id'  => $pData['public_id'],
                'is_featured'      => $index < 6, // Feature first 6 clothing products
                'status'           => 'ACTIVE',
            ]);

            // Add Product Image entry
            ProductImage::create([
                'product_id'       => $product->product_id,
                'image_url'        => $pData['image_url'],
                'image_public_id'  => $pData['public_id'],
                'is_primary'       => true,
                'sort_order'       => 0,
            ]);

            // Add 3 Variants for this Product
            foreach ($pData['variants'] as $vIndex => $vData) {
                $size = $sizes[$vData['size']];
                $color = $colors[$vData['color']];

                $barcode = '885' . str_pad((string)($product->product_id * 100 + $vIndex + 1), 9, '0', STR_PAD_LEFT);

                ProductVariant::create([
                    'product_id'       => $product->product_id,
                    'sku'              => $vData['sku_prefix'],
                    'barcode'          => $barcode,
                    'size_id'          => $size->size_id,
                    'color_id'         => $color->color_id,
                    'cost_price'       => $vData['cost'],
                    'sale_price'       => $vData['price'],
                    'quantity'         => $vData['qty'],
                    'reorder_level'    => 10,
                    'image_url'        => $pData['image_url'],
                    'image_public_id'  => $pData['public_id'],
                    'status'           => 'ACTIVE',
                ]);
            }
        }
    }
}
