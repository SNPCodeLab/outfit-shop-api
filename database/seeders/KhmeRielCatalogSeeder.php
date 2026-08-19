<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use App\Models\ClothingSize;
use App\Models\Color;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class KhmeRielCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $categoriesData = [
            ['name' => 'Tops & Shirts',           'slug' => 'tops-shirts',         'desc' => 'Luxury shirts, blouses, and classic embroidered tops.'],
            ['name' => 'Bottoms & Trousers',       'slug' => 'bottoms-trousers',    'desc' => 'Tailored trousers, culottes, twill pants, and shorts.'],
            ['name' => 'Dresses',                  'slug' => 'dresses',             'desc' => 'Silk maxi dresses, crinoline dresses, and evening gowns.'],
            ['name' => 'Knitwear',                 'slug' => 'knitwear',            'desc' => 'Seasonal knitwear, cardigans, and woven sweaters.'],
            ['name' => 'Outerwear & Coats',        'slug' => 'outerwear-coats',     'desc' => 'Collared jackets, trench coats, and tailoring.'],
            ['name' => 'Skirts',                   'slug' => 'skirts',              'desc' => 'Fluted knit skirts, beaded skirts, and midi skirts.'],
            ['name' => 'Bags & Leather Goods',     'slug' => 'bags-leather-goods',  'desc' => 'T-Lock clutches, Roam leather totes, and mini bags.'],
            ['name' => 'Shoes & Footwear',         'slug' => 'shoes-footwear',      'desc' => 'T-Strap nappa sandals, ballerinas, and slip-on flats.'],
            ['name' => 'Accessories & Scarves',    'slug' => 'accessories-scarves', 'desc' => 'Embroidered monogram silk scarves and luxury accessories.'],
        ];

        $categories = [];
        foreach ($categoriesData as $cat) {
            $categories[$cat['slug']] = Category::firstOrCreate(
                ['slug' => $cat['slug']],
                ['category_name' => $cat['name'], 'description' => $cat['desc'], 'is_active' => true]
            );
        }

        // Standard Sizes
        $sizes = [
            'XS' => ClothingSize::firstOrCreate(['size_code' => 'XS'], ['size_name' => 'Extra Small']),
            'S' => ClothingSize::firstOrCreate(['size_code' => 'S'], ['size_name' => 'Small']),
            'M' => ClothingSize::firstOrCreate(['size_code' => 'M'], ['size_name' => 'Medium']),
            'L' => ClothingSize::firstOrCreate(['size_code' => 'L'], ['size_name' => 'Large']),
            'XL' => ClothingSize::firstOrCreate(['size_code' => 'XL'], ['size_name' => 'Extra Large']),
            '2XL' => ClothingSize::firstOrCreate(['size_code' => '2XL'], ['size_name' => 'Double Extra Large']),
            'OS' => ClothingSize::firstOrCreate(['size_code' => 'OS'], ['size_name' => 'One Size']),
        ];

        // Standard Luxury Colors
        $colors = [
            'BLK' => Color::firstOrCreate(['color_code' => 'BLK'], ['color_name' => 'Black',     'hex_code' => '#111111']),
            'IVR' => Color::firstOrCreate(['color_code' => 'IVR'], ['color_name' => 'Ivory',     'hex_code' => '#FFFFF0']),
            'BGE' => Color::firstOrCreate(['color_code' => 'BGE'], ['color_name' => 'Beige',     'hex_code' => '#D4C4B5']),
            'BRN' => Color::firstOrCreate(['color_code' => 'BRN'], ['color_name' => 'Dark Brown', 'hex_code' => '#4A3326']),
            'BUT' => Color::firstOrCreate(['color_code' => 'BUT'], ['color_name' => 'Buttercup', 'hex_code' => '#F7E7A9']),
            'OLV' => Color::firstOrCreate(['color_code' => 'OLV'], ['color_name' => 'Olive',     'hex_code' => '#556B2F']),
        ];

        // Curated KhmeRiel Products
        $productsData = [
            [
                'cat' => 'tops-shirts',
                'name' => 'KhmeRiel Classic Embroidered Shirt',
                'desc' => 'Signature tailored silk-cotton shirt with understated micro embroidery.',
                'price' => 68.00,
                'cost' => 32.00,
                'colors' => ['BLK', 'IVR'],
                'sizes' => ['S', 'M', 'L', 'XL'],
            ],
            [
                'cat' => 'tops-shirts',
                'name' => 'KhmeRiel Fluid Draped Blouse',
                'desc' => 'Relaxed silhouette luxury blouse with flowing shoulder lines.',
                'price' => 58.00,
                'cost' => 28.00,
                'colors' => ['IVR', 'BGE'],
                'sizes' => ['S', 'M', 'L'],
            ],
            [
                'cat' => 'bottoms-trousers',
                'name' => 'KhmeRiel Monogram Silk PJ Culottes',
                'desc' => 'Lightweight luxury loungewear culottes with woven tone-on-tone monogram.',
                'price' => 85.00,
                'cost' => 40.00,
                'colors' => ['BLK', 'IVR'],
                'sizes' => ['S', 'M', 'L', 'XL'],
            ],
            [
                'cat' => 'bottoms-trousers',
                'name' => 'KhmeRiel Relaxed Twill Trousers',
                'desc' => 'Pleated straight-leg trousers tailored for breathable all-day comfort.',
                'price' => 75.00,
                'cost' => 35.00,
                'colors' => ['BLK', 'BGE', 'OLV'],
                'sizes' => ['S', 'M', 'L', 'XL', '2XL'],
            ],
            [
                'cat' => 'bottoms-trousers',
                'name' => 'KhmeRiel Tie-Waist Jersey Shorts',
                'desc' => 'Minimalist drawstring jersey shorts with soft premium texture.',
                'price' => 42.00,
                'cost' => 18.00,
                'colors' => ['BLK', 'IVR'],
                'sizes' => ['S', 'M', 'L'],
            ],
            [
                'cat' => 'dresses',
                'name' => 'KhmeRiel V-Neck Silk Maxi Dress',
                'desc' => 'Floor-length flowing dress tailored from 100% fine mulberry silk.',
                'price' => 140.00,
                'cost' => 65.00,
                'colors' => ['BLK', 'IVR'],
                'sizes' => ['S', 'M', 'L'],
            ],
            [
                'cat' => 'bags-leather-goods',
                'name' => 'KhmeRiel T-Lock Leather Clutch Bag',
                'desc' => 'Handcrafted premium calfskin leather clutch with signature gold T-lock hardware.',
                'price' => 180.00,
                'cost' => 85.00,
                'colors' => ['BLK', 'BGE'],
                'sizes' => ['OS'],
            ],
            [
                'cat' => 'bags-leather-goods',
                'name' => 'KhmeRiel Roam Leather Everyday Tote',
                'desc' => 'Structured spacious leather tote with reinforced handles and interior organizer.',
                'price' => 220.00,
                'cost' => 105.00,
                'colors' => ['BLK', 'BRN'],
                'sizes' => ['OS'],
            ],
            [
                'cat' => 'shoes-footwear',
                'name' => 'KhmeRiel T-Strap Nappa Leather Sandals',
                'desc' => 'Sleek architectural T-strap flat sandals crafted from soft nappa leather.',
                'price' => 95.00,
                'cost' => 45.00,
                'colors' => ['BLK', 'BRN'],
                'sizes' => ['S', 'M', 'L'],
            ],
            [
                'cat' => 'shoes-footwear',
                'name' => 'KhmeRiel Naplack Leather Ballerinas',
                'desc' => 'Supple gloss leather slip-on ballerinas with cushioned leather insole.',
                'price' => 98.00,
                'cost' => 46.00,
                'colors' => ['BLK', 'BUT'],
                'sizes' => ['S', 'M', 'L'],
            ],
            [
                'cat' => 'accessories-scarves',
                'name' => 'KhmeRiel Embroidered Monogram Silk Scarf',
                'desc' => 'Square 90x90cm luxury silk twill scarf with hand-rolled edges.',
                'price' => 55.00,
                'cost' => 22.00,
                'colors' => ['BLK', 'IVR'],
                'sizes' => ['OS'],
            ],
        ];

        foreach ($productsData as $p) {
            $catId = $categories[$p['cat']]->category_id;
            $product = Product::firstOrCreate(
                ['product_name' => $p['name'], 'category_id' => $catId],
                [
                    'brand' => 'KhmeRiel',
                    'description' => $p['desc'],
                    'status' => 'ACTIVE',
                ]
            );

            // Generate variants for selected sizes & colors
            foreach ($p['colors'] as $cCode) {
                foreach ($p['sizes'] as $sCode) {
                    $color = $colors[$cCode];
                    $size = $sizes[$sCode];

                    $sku = 'KR-'.strtoupper(Str::slug(substr($p['name'], 9, 8)))."-{$cCode}-{$sCode}";
                    $barcode = '885'.str_pad((string) rand(1000000000, 9999999999), 10, '0', STR_PAD_LEFT);

                    ProductVariant::firstOrCreate(
                        [
                            'product_id' => $product->product_id,
                            'size_id' => $size->size_id,
                            'color_id' => $color->color_id,
                        ],
                        [
                            'sku' => $sku,
                            'barcode' => $barcode,
                            'cost_price' => $p['cost'],
                            'sale_price' => $p['price'],
                            'quantity' => rand(10, 45),
                            'reorder_level' => 5,
                        ]
                    );
                }
            }
        }
    }
}
