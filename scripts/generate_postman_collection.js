const fs = require('fs');
const path = require('path');

// Base URLs
const LOCAL_URL = 'http://127.0.0.1:8000/api/v1';
const PROD_URL = 'https://api.kesararamwithdigital.tech/api/v1';

// Shared headers helper
const authHeader = (tokenVar = 'token') => [
  { key: 'Authorization', value: `Bearer {{${tokenVar}}}`, type: 'text' },
  { key: 'Accept', value: 'application/json', type: 'text' }
];

const jsonHeaders = (tokenVar = null) => {
  const headers = [
    { key: 'Content-Type', value: 'application/json', type: 'text' },
    { key: 'Accept', value: 'application/json', type: 'text' }
  ];
  if (tokenVar) {
    headers.unshift({ key: 'Authorization', value: `Bearer {{${tokenVar}}}`, type: 'text' });
  }
  return headers;
};

// URL builder helper
const makeUrl = (urlPath, queryParams = []) => {
  const pathParts = urlPath.replace(/^\//, '').split('/');
  const query = queryParams.map(q => ({
    key: q.key,
    value: q.value,
    description: q.description || '',
    disabled: q.disabled || false
  }));
  return {
    raw: `{{base_url}}/${pathParts.join('/')}${query.length ? '?' + query.map(q => `${q.key}=${encodeURIComponent(q.value)}`).join('&') : ''}`,
    host: ['{{base_url}}'],
    path: pathParts,
    query: query.length ? query : undefined
  };
};

// Login test script to automatically persist token across tests
const loginTestScript = [
  "// Auto-save auth token to environment & collection variables",
  "if (pm.response.code === 200) {",
  "    var json = pm.response.json();",
  "    var token = json.token || (json.data && json.data.token);",
  "    if (token) {",
  "        pm.environment.set('token', token);",
  "        pm.collectionVariables.set('token', token);",
  "        console.log('Saved token:', token);",
  "        ",
  "        // Auto-detect role and save specific role tokens",
  "        var user = json.user || (json.data && json.data.user);",
  "        if (user && user.role) {",
  "            var role = user.role.toUpperCase();",
  "            if (role === 'ADMIN') pm.environment.set('admin_token', token);",
  "            if (role === 'MANAGER') pm.environment.set('manager_token', token);",
  "            if (role === 'CASHIER') pm.environment.set('cashier_token', token);",
  "            if (role === 'STAFF') pm.environment.set('staff_token', token);",
  "        }",
  "    }",
  "}"
];

// Definition of All 122+ Endpoints grouped into 9 structured modules
const collectionItems = [
  {
    name: '01. System & Authentication',
    description: 'System health check, status, logo, sound cues, login, current profile, and user registration.',
    item: [
      {
        name: 'Health Check',
        request: {
          method: 'GET',
          header: [{ key: 'Accept', value: 'application/json', type: 'text' }],
          url: makeUrl('health'),
          description: 'Checks server and database connection status.'
        }
      },
      {
        name: 'System Status & Logo',
        request: {
          method: 'GET',
          header: [{ key: 'Accept', value: 'application/json', type: 'text' }],
          url: makeUrl('status'),
          description: 'Returns system branding, version, and active status.'
        }
      },
      {
        name: 'POS Audio Cue Sounds',
        request: {
          method: 'GET',
          header: [{ key: 'Accept', value: 'application/json', type: 'text' }],
          url: makeUrl('settings/audio-cues'),
          description: 'Returns pre-configured audio cues for cash register barcode beeps and checkout chimes.'
        }
      },
      {
        name: 'Auth - Login (Superadmin / Admin)',
        event: [
          {
            listen: 'test',
            script: { type: 'text/javascript', exec: loginTestScript }
          }
        ],
        request: {
          method: 'POST',
          header: jsonHeaders(),
          body: {
            mode: 'raw',
            raw: JSON.stringify({
              email: 'superadmin@ssmis.local',
              password: 'password123'
            }, null, 2)
          },
          url: makeUrl('auth/login'),
          description: 'Logs in as Superadmin and automatically stores Bearer token.'
        }
      },
      {
        name: 'Auth - Login (Manager)',
        event: [
          {
            listen: 'test',
            script: { type: 'text/javascript', exec: loginTestScript }
          }
        ],
        request: {
          method: 'POST',
          header: jsonHeaders(),
          body: {
            mode: 'raw',
            raw: JSON.stringify({
              email: 'manager@ssmis.local',
              password: 'password123'
            }, null, 2)
          },
          url: makeUrl('auth/login'),
          description: 'Logs in as Store Manager.'
        }
      },
      {
        name: 'Auth - Login (Cashier)',
        event: [
          {
            listen: 'test',
            script: { type: 'text/javascript', exec: loginTestScript }
          }
        ],
        request: {
          method: 'POST',
          header: jsonHeaders(),
          body: {
            mode: 'raw',
            raw: JSON.stringify({
              email: 'cashier@ssmis.local',
              password: 'password123'
            }, null, 2)
          },
          url: makeUrl('auth/login'),
          description: 'Logs in as Cashier.'
        }
      },
      {
        name: 'Auth - Get Current User (/me)',
        request: {
          method: 'GET',
          header: authHeader('token'),
          url: makeUrl('auth/me'),
          description: 'Returns authenticated user info and active permissions.'
        }
      },
      {
        name: 'Auth - Logout',
        request: {
          method: 'POST',
          header: authHeader('token'),
          url: makeUrl('auth/logout'),
          description: 'Revokes the current Sanctum access token.'
        }
      },
      {
        name: 'Admin - Register Staff Account',
        request: {
          method: 'POST',
          header: jsonHeaders('admin_token'),
          body: {
            mode: 'raw',
            raw: JSON.stringify({
              name: 'Sokha Chan',
              email: 'sokha.pos@ssmis.local',
              password: 'password123',
              password_confirmation: 'password123',
              role: 'CASHIER'
            }, null, 2)
          },
          url: makeUrl('auth/register'),
          description: 'Admin endpoint to provision user credentials for frontend staff.'
        }
      }
    ]
  },
  {
    name: '02. Categories, Brands, Sizes & Colors',
    description: 'CRUD operations for product taxomony and visual attributes.',
    item: [
      // Categories
      {
        name: 'Categories - List All',
        request: {
          method: 'GET',
          header: [{ key: 'Accept', value: 'application/json' }],
          url: makeUrl('categories')
        }
      },
      {
        name: 'Categories - Get By ID',
        request: {
          method: 'GET',
          header: [{ key: 'Accept', value: 'application/json' }],
          url: makeUrl('categories/1')
        }
      },
      {
        name: 'Categories - Create (Manager/Admin)',
        request: {
          method: 'POST',
          header: jsonHeaders('manager_token'),
          body: {
            mode: 'raw',
            raw: JSON.stringify({
              category_name: 'Traditional Khmer Silk',
              description: 'Handwoven artisan Cambodian Silk apparel and scarves'
            }, null, 2)
          },
          url: makeUrl('categories')
        }
      },
      {
        name: 'Categories - Update (Manager/Admin)',
        request: {
          method: 'PUT',
          header: jsonHeaders('manager_token'),
          body: {
            mode: 'raw',
            raw: JSON.stringify({
              category_name: 'Premium Khmer Silk & Cotton',
              description: 'Updated premium collection'
            }, null, 2)
          },
          url: makeUrl('categories/1')
        }
      },
      {
        name: 'Categories - Delete (Manager/Admin)',
        request: {
          method: 'DELETE',
          header: authHeader('manager_token'),
          url: makeUrl('categories/1')
        }
      },
      // Brands
      {
        name: 'Brands - List All',
        request: {
          method: 'GET',
          header: [{ key: 'Accept', value: 'application/json' }],
          url: makeUrl('brands')
        }
      },
      {
        name: 'Brands - Get By ID',
        request: {
          method: 'GET',
          header: [{ key: 'Accept', value: 'application/json' }],
          url: makeUrl('brands/1')
        }
      },
      {
        name: 'Brands - Create (Manager/Admin)',
        request: {
          method: 'POST',
          header: jsonHeaders('manager_token'),
          body: {
            mode: 'raw',
            raw: JSON.stringify({
              brand_name: 'KhmeRiel Originals',
              country_of_origin: 'Cambodia',
              description: 'Flagship streetwear and luxury apparel brand'
            }, null, 2)
          },
          url: makeUrl('brands')
        }
      },
      {
        name: 'Brands - Update (Manager/Admin)',
        request: {
          method: 'PUT',
          header: jsonHeaders('manager_token'),
          body: {
            mode: 'raw',
            raw: JSON.stringify({
              brand_name: 'KhmeRiel Haute Couture',
              country_of_origin: 'Cambodia'
            }, null, 2)
          },
          url: makeUrl('brands/1')
        }
      },
      {
        name: 'Brands - Delete (Manager/Admin)',
        request: {
          method: 'DELETE',
          header: authHeader('manager_token'),
          url: makeUrl('brands/1')
        }
      },
      // Sizes
      {
        name: 'Clothing Sizes - List All',
        request: {
          method: 'GET',
          header: [{ key: 'Accept', value: 'application/json' }],
          url: makeUrl('clothing-sizes')
        }
      },
      {
        name: 'Clothing Sizes - Get By ID',
        request: {
          method: 'GET',
          header: [{ key: 'Accept', value: 'application/json' }],
          url: makeUrl('clothing-sizes/1')
        }
      },
      {
        name: 'Clothing Sizes - Create (Manager/Admin)',
        request: {
          method: 'POST',
          header: jsonHeaders('manager_token'),
          body: {
            mode: 'raw',
            raw: JSON.stringify({
              size_code: 'XXL',
              size_name: 'Extra Extra Large',
              sort_order: 6
            }, null, 2)
          },
          url: makeUrl('clothing-sizes')
        }
      },
      {
        name: 'Clothing Sizes - Update (Manager/Admin)',
        request: {
          method: 'PUT',
          header: jsonHeaders('manager_token'),
          body: {
            mode: 'raw',
            raw: JSON.stringify({
              size_name: 'Double Extra Large',
              sort_order: 6
            }, null, 2)
          },
          url: makeUrl('clothing-sizes/1')
        }
      },
      {
        name: 'Clothing Sizes - Delete (Manager/Admin)',
        request: {
          method: 'DELETE',
          header: authHeader('manager_token'),
          url: makeUrl('clothing-sizes/1')
        }
      },
      // Colors
      {
        name: 'Colors - List All',
        request: {
          method: 'GET',
          header: [{ key: 'Accept', value: 'application/json' }],
          url: makeUrl('colors')
        }
      },
      {
        name: 'Colors - Get By ID',
        request: {
          method: 'GET',
          header: [{ key: 'Accept', value: 'application/json' }],
          url: makeUrl('colors/1')
        }
      },
      {
        name: 'Colors - Create (Manager/Admin)',
        request: {
          method: 'POST',
          header: jsonHeaders('manager_token'),
          body: {
            mode: 'raw',
            raw: JSON.stringify({
              color_name: 'Angkor Sunset Gold',
              hex_code: '#E6A117'
            }, null, 2)
          },
          url: makeUrl('colors')
        }
      },
      {
        name: 'Colors - Update (Manager/Admin)',
        request: {
          method: 'PUT',
          header: jsonHeaders('manager_token'),
          body: {
            mode: 'raw',
            raw: JSON.stringify({
              color_name: 'Angkor Royal Gold',
              hex_code: '#FFD700'
            }, null, 2)
          },
          url: makeUrl('colors/1')
        }
      },
      {
        name: 'Colors - Delete (Manager/Admin)',
        request: {
          method: 'DELETE',
          header: authHeader('manager_token'),
          url: makeUrl('colors/1')
        }
      }
    ]
  },
  {
    name: '03. Products & 2D Matrix',
    description: 'Products catalog, matrixes, colorways, reviews, images and digital asset downloads.',
    item: [
      {
        name: 'Products - List All (With Filters)',
        request: {
          method: 'GET',
          header: [{ key: 'Accept', value: 'application/json' }],
          url: makeUrl('products', [
            { key: 'category_id', value: '1', disabled: true },
            { key: 'brand_id', value: '1', disabled: true },
            { key: 'search', value: 'Shirt', disabled: true },
            { key: 'sort', value: 'price_asc', disabled: true },
            { key: 'per_page', value: '15', disabled: false }
          ])
        }
      },
      {
        name: 'Products - Get By ID',
        request: {
          method: 'GET',
          header: [{ key: 'Accept', value: 'application/json' }],
          url: makeUrl('products/19')
        }
      },
      {
        name: 'Products - 2D Variant Matrix',
        request: {
          method: 'GET',
          header: [{ key: 'Accept', value: 'application/json' }],
          url: makeUrl('products/19/matrix'),
          description: 'Returns full 2D grid matrix of Sizes x Colors with barcode & stock level.'
        }
      },
      {
        name: 'Products - Colorways',
        request: {
          method: 'GET',
          header: [{ key: 'Accept', value: 'application/json' }],
          url: makeUrl('products/19/colorways'),
          description: 'Returns available colorways and swatches for Ralph Lauren-style storefront cards.'
        }
      },
      {
        name: 'Products - Download Digital Asset',
        request: {
          method: 'GET',
          header: [{ key: 'Accept', value: 'application/json' }],
          url: makeUrl('products/19/download'),
          description: 'Download digital sizing guide, high-res lookbook, or spec sheet.'
        }
      },
      {
        name: 'Products - List Images',
        request: {
          method: 'GET',
          header: [{ key: 'Accept', value: 'application/json' }],
          url: makeUrl('products/19/images')
        }
      },
      {
        name: 'Products - Attach Image (Manager/Admin)',
        request: {
          method: 'POST',
          header: jsonHeaders('manager_token'),
          body: {
            mode: 'raw',
            raw: JSON.stringify({
              image_url: 'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?w=800',
              is_primary: false,
              sort_order: 2
            }, null, 2)
          },
          url: makeUrl('products/19/images')
        }
      },
      {
        name: 'Products - Delete Image (Manager/Admin)',
        request: {
          method: 'DELETE',
          header: authHeader('manager_token'),
          url: makeUrl('products/19/images/1')
        }
      },
      {
        name: 'Products - Upload Primary Image (Manager/Admin)',
        request: {
          method: 'POST',
          header: jsonHeaders('manager_token'),
          body: {
            mode: 'raw',
            raw: JSON.stringify({
              image_url: 'https://res.cloudinary.com/demo/image/upload/sample.jpg'
            }, null, 2)
          },
          url: makeUrl('products/19/image')
        }
      },
      {
        name: 'Products - List Reviews',
        request: {
          method: 'GET',
          header: [{ key: 'Accept', value: 'application/json' }],
          url: makeUrl('products/19/reviews')
        }
      },
      {
        name: 'Products - Submit Review (Public/Customer)',
        request: {
          method: 'POST',
          header: jsonHeaders(),
          body: {
            mode: 'raw',
            raw: JSON.stringify({
              customer_name: 'Dara Rath',
              rating: 5,
              comment: 'Exceptional tailoring, premium linen fabric, and fits true to size!'
            }, null, 2)
          },
          url: makeUrl('products/19/reviews')
        }
      },
      {
        name: 'Products - Create (Manager/Admin)',
        request: {
          method: 'POST',
          header: jsonHeaders('manager_token'),
          body: {
            mode: 'raw',
            raw: JSON.stringify({
              product_name: 'KhmeRiel Signature Oxford Shirt',
              product_code: 'KHM-OXF-001',
              category_id: 1,
              brand_id: 1,
              base_price: 29.50,
              cost_price: 14.00,
              description: 'Tailored 100% fine cotton oxford button-down shirt.'
            }, null, 2)
          },
          url: makeUrl('products')
        }
      },
      {
        name: 'Products - Update (Manager/Admin)',
        request: {
          method: 'PUT',
          header: jsonHeaders('manager_token'),
          body: {
            mode: 'raw',
            raw: JSON.stringify({
              product_name: 'KhmeRiel Signature Oxford Shirt - 2026 Edition',
              base_price: 32.00,
              cost_price: 15.00
            }, null, 2)
          },
          url: makeUrl('products/19')
        }
      },
      {
        name: 'Products - Delete (Manager/Admin)',
        request: {
          method: 'DELETE',
          header: authHeader('manager_token'),
          url: makeUrl('products/19')
        }
      }
    ]
  },
  {
    name: '04. Product Variants, Tiers & Batches',
    description: 'SKU variant inventory, barcode lookups, wholesale pricing tiers, and FIFO batches.',
    item: [
      {
        name: 'Variants - List All',
        request: {
          method: 'GET',
          header: [{ key: 'Accept', value: 'application/json' }],
          url: makeUrl('variants', [
            { key: 'product_id', value: '19', disabled: true },
            { key: 'in_stock', value: '1', disabled: true }
          ])
        }
      },
      {
        name: 'Variants - Low Stock Alert',
        request: {
          method: 'GET',
          header: [{ key: 'Accept', value: 'application/json' }],
          url: makeUrl('variants/low-stock', [
            { key: 'threshold', value: '10' }
          ])
        }
      },
      {
        name: 'Variants - Barcode Lookup (Scanner)',
        request: {
          method: 'GET',
          header: [{ key: 'Accept', value: 'application/json' }],
          url: makeUrl('variants/barcode/885000001901'),
          description: 'Instant barcode scan response for POS hardware scanners.'
        }
      },
      {
        name: 'Variants - Get By ID',
        request: {
          method: 'GET',
          header: [{ key: 'Accept', value: 'application/json' }],
          url: makeUrl('variants/1')
        }
      },
      {
        name: 'Variants - Create (Manager/Admin)',
        request: {
          method: 'POST',
          header: jsonHeaders('manager_token'),
          body: {
            mode: 'raw',
            raw: JSON.stringify({
              product_id: 19,
              size_id: 2,
              color_id: 1,
              sku: 'KHM-OXF-BLK-M',
              barcode: '885000001999',
              unit_price: 32.00,
              cost_price: 15.00,
              stock_quantity: 50,
              reorder_level: 10
            }, null, 2)
          },
          url: makeUrl('variants')
        }
      },
      {
        name: 'Variants - Update (Manager/Admin)',
        request: {
          method: 'PUT',
          header: jsonHeaders('manager_token'),
          body: {
            mode: 'raw',
            raw: JSON.stringify({
              unit_price: 34.00,
              reorder_level: 12
            }, null, 2)
          },
          url: makeUrl('variants/1')
        }
      },
      {
        name: 'Variants - Delete (Manager/Admin)',
        request: {
          method: 'DELETE',
          header: authHeader('manager_token'),
          url: makeUrl('variants/1')
        }
      },
      {
        name: 'Variants - Print Barcode Label (SVG/Thermal)',
        request: {
          method: 'GET',
          header: [{ key: 'Accept', value: 'text/html' }],
          url: makeUrl('variants/1/barcode-label')
        }
      },
      {
        name: 'Variants - Upload Variant Image',
        request: {
          method: 'POST',
          header: jsonHeaders('manager_token'),
          body: {
            mode: 'raw',
            raw: JSON.stringify({
              image_url: 'https://res.cloudinary.com/demo/image/upload/variant_black.jpg'
            }, null, 2)
          },
          url: makeUrl('variants/1/image')
        }
      },
      {
        name: 'Pricing Tiers - List By Variant',
        request: {
          method: 'GET',
          header: [{ key: 'Accept', value: 'application/json' }],
          url: makeUrl('variants/1/tiers')
        }
      },
      {
        name: 'Pricing Tiers - Create Tier (Manager/Admin)',
        request: {
          method: 'POST',
          header: jsonHeaders('manager_token'),
          body: {
            mode: 'raw',
            raw: JSON.stringify({
              min_quantity: 10,
              tier_price: 26.00,
              label: 'Wholesale Tier 1 (10+ pcs)'
            }, null, 2)
          },
          url: makeUrl('variants/1/tiers')
        }
      },
      {
        name: 'Batches - List Batches By Variant (FIFO)',
        request: {
          method: 'GET',
          header: authHeader('manager_token'),
          url: makeUrl('variants/1/batches')
        }
      },
      {
        name: 'Batches - Create New Batch (Manager/Admin)',
        request: {
          method: 'POST',
          header: jsonHeaders('manager_token'),
          body: {
            mode: 'raw',
            raw: JSON.stringify({
              batch_number: 'BATCH-2026-08-001',
              quantity: 100,
              cost_price: 14.50,
              received_date: '2026-08-17',
              expiry_date: '2027-08-17'
            }, null, 2)
          },
          url: makeUrl('variants/1/batches')
        }
      },
      {
        name: 'Batches - Expiring Soon (Manager/Admin)',
        request: {
          method: 'GET',
          header: authHeader('manager_token'),
          url: makeUrl('inventory/expiring-soon', [
            { key: 'days', value: '30' }
          ])
        }
      }
    ]
  },
  {
    name: '05. Product Bundles & Combos',
    description: 'Multi-item product combo packages and promotional bundles.',
    item: [
      {
        name: 'Bundles - List All',
        request: {
          method: 'GET',
          header: [{ key: 'Accept', value: 'application/json' }],
          url: makeUrl('bundles')
        }
      },
      {
        name: 'Bundles - Get By ID',
        request: {
          method: 'GET',
          header: [{ key: 'Accept', value: 'application/json' }],
          url: makeUrl('bundles/1')
        }
      },
      {
        name: 'Bundles - Create Bundle (Manager/Admin)',
        request: {
          method: 'POST',
          header: jsonHeaders('manager_token'),
          body: {
            mode: 'raw',
            raw: JSON.stringify({
              bundle_name: 'Executive Silk Tie & Cufflinks Set',
              bundle_code: 'BND-EXEC-01',
              bundle_price: 45.00,
              items: [
                { variant_id: 1, quantity: 1 },
                { variant_id: 2, quantity: 2 }
              ]
            }, null, 2)
          },
          url: makeUrl('bundles')
        }
      },
      {
        name: 'Bundles - Delete Bundle (Manager/Admin)',
        request: {
          method: 'DELETE',
          header: authHeader('manager_token'),
          url: makeUrl('bundles/1')
        }
      }
    ]
  },
  {
    name: '06. POS Cash Register Shifts (Z-Report)',
    description: 'Cash drawer session opening, mid-day cash drops, and closing Z-Report calculation.',
    item: [
      {
        name: 'Shifts - Current Active Shift',
        request: {
          method: 'GET',
          header: authHeader('token'),
          url: makeUrl('shifts/current')
        }
      },
      {
        name: 'Shifts - Open Cash Drawer Shift',
        request: {
          method: 'POST',
          header: jsonHeaders('cashier_token'),
          body: {
            mode: 'raw',
            raw: JSON.stringify({
              opening_float: 100.00,
              notes: 'Morning shift opening float (USD 100 in mixed small bills)'
            }, null, 2)
          },
          url: makeUrl('shifts/open')
        }
      },
      {
        name: 'Shifts - Drop / Withdraw Cash',
        request: {
          method: 'POST',
          header: jsonHeaders('cashier_token'),
          body: {
            mode: 'raw',
            raw: JSON.stringify({
              amount: 50.00,
              reason: 'Midday safe transfer to prevent drawer overflow'
            }, null, 2)
          },
          url: makeUrl('shifts/drop-cash')
        }
      },
      {
        name: 'Shifts - Close Shift & Print Z-Report',
        request: {
          method: 'POST',
          header: jsonHeaders('cashier_token'),
          body: {
            mode: 'raw',
            raw: JSON.stringify({
              closing_cash_counted: 485.50,
              notes: 'Evening shift end. Reconciled accurately.'
            }, null, 2)
          },
          url: makeUrl('shifts/close')
        }
      }
    ]
  },
  {
    name: '07. POS Sales, Checkout, Receipts & KHQR',
    description: 'Point-of-Sale checkout, 10% tax calculation, dynamic Bakong KHQR, ESC/POS receipts, and voiding.',
    item: [
      {
        name: 'Sales - POS Checkout (10% Tax-Exclusive)',
        request: {
          method: 'POST',
          header: jsonHeaders('token'),
          body: {
            mode: 'raw',
            raw: JSON.stringify({
              customer_id: 1,
              items: [
                {
                  variant_id: 1,
                  quantity: 2,
                  unit_price: 32.00,
                  discount_amount: 0.00
                }
              ],
              payment_method: 'KHQR',
              amount_paid: 70.40,
              discount_code: '',
              notes: 'POS in-store retail checkout'
            }, null, 2)
          },
          url: makeUrl('sales/checkout'),
          description: 'Calculates subtotal, applies 10% VAT tax-exclusive, registers payment, updates inventory, and generates audit log.'
        }
      },
      {
        name: 'Sales - List Sales History',
        request: {
          method: 'GET',
          header: authHeader('token'),
          url: makeUrl('sales', [
            { key: 'from_date', value: '2026-08-01', disabled: true },
            { key: 'to_date', value: '2026-08-31', disabled: true },
            { key: 'per_page', value: '20', disabled: false }
          ])
        }
      },
      {
        name: 'Sales - Get Sale By ID',
        request: {
          method: 'GET',
          header: authHeader('token'),
          url: makeUrl('sales/1')
        }
      },
      {
        name: 'Sales - Generate KHQR for Sale',
        request: {
          method: 'GET',
          header: [{ key: 'Accept', value: 'application/json' }],
          url: makeUrl('sales/1/khqr'),
          description: 'Generates real Bakong KHQR dynamic payload with exact invoice amount & currency.'
        }
      },
      {
        name: 'Payments - Generate Custom KHQR',
        request: {
          method: 'GET',
          header: [{ key: 'Accept', value: 'application/json' }],
          url: makeUrl('payments/khqr', [
            { key: 'amount', value: '25.00' },
            { key: 'currency', value: 'USD' }
          ])
        }
      },
      {
        name: 'Sales - Thermal ESC/POS Receipt View',
        request: {
          method: 'GET',
          header: [{ key: 'Accept', value: 'text/html' }],
          url: makeUrl('sales/1/receipt-thermal'),
          description: 'Renders 80mm thermal receipt formatted with tax breakdown and barcode.'
        }
      },
      {
        name: 'Sales - Void Sale Invoice (Manager/Admin)',
        request: {
          method: 'POST',
          header: jsonHeaders('manager_token'),
          body: {
            mode: 'raw',
            raw: JSON.stringify({
              reason: 'Customer mistakenly scanned twice and requested instant refund.'
            }, null, 2)
          },
          url: makeUrl('sales/1/void'),
          description: 'Voids invoice, restores inventory count, and adds audit record.'
        }
      },
      {
        name: 'Invoices - List Sales Orders & Estimates (SalesBinder)',
        request: {
          method: 'GET',
          header: authHeader('token'),
          url: makeUrl('invoices', [
            { key: 'status', value: 'ESTIMATE', disabled: true },
            { key: 'per_page', value: '20', disabled: false }
          ]),
          description: 'SalesBinder-style Billing Dashboard with financial totals and status filters.'
        }
      },
      {
        name: 'Estimates - Create Estimate Quote (SalesBinder)',
        request: {
          method: 'POST',
          header: jsonHeaders('token'),
          body: {
            mode: 'raw',
            raw: JSON.stringify({
              customer_id: 1,
              items: [
                { variant_id: 1, quantity: 2, discount: 0.00 },
                { variant_id: 2, quantity: 1, discount: 0.00 }
              ],
              overall_discount: 5.00,
              tax_rate: 10.00,
              notes: 'Official wholesale quote valid for 14 days'
            }, null, 2)
          },
          url: makeUrl('estimates'),
          description: 'Creates a quotation/estimate and calculates 10% VAT without immediately deducting stock.'
        }
      },
      {
        name: 'Estimates - 1-Click Convert to Invoice (SalesBinder)',
        request: {
          method: 'POST',
          header: jsonHeaders('token'),
          body: {
            mode: 'raw',
            raw: JSON.stringify({
              payment_method: 'ABA'
            }, null, 2)
          },
          url: makeUrl('estimates/1/convert'),
          description: '1-Click converts an approved estimate into an active invoice, validates & deducts inventory.'
        }
      },
      {
        name: 'Invoices - SalesBinder A4 Printable PDF/HTML Invoice View',
        request: {
          method: 'GET',
          header: [{ key: 'Accept', value: 'text/html' }],
          url: makeUrl('sales/1/invoice-pdf'),
          description: 'Renders a beautiful, high-res SalesBinder-style A4 invoice ready to save as PDF or print.'
        }
      },
      {
        name: 'Gift Cards - Check Balance',
        request: {
          method: 'POST',
          header: jsonHeaders(),
          body: {
            mode: 'raw',
            raw: JSON.stringify({
              card_code: 'GIFT-2026-9999'
            }, null, 2)
          },
          url: makeUrl('gift-cards/check')
        }
      },
      {
        name: 'Gift Cards - Issue New Card',
        request: {
          method: 'POST',
          header: jsonHeaders('token'),
          body: {
            mode: 'raw',
            raw: JSON.stringify({
              customer_id: 1,
              initial_balance: 50.00,
              expires_at: '2027-12-31'
            }, null, 2)
          },
          url: makeUrl('gift-cards/issue')
        }
      }
    ]
  },
  {
    name: '08. Customers, Loyalty & Wishlist',
    description: 'Customer profiles, VIP loyalty points redemption, and customer storefront wishlists.',
    item: [
      {
        name: 'Customers - List All (Search by Phone/Name)',
        request: {
          method: 'GET',
          header: authHeader('token'),
          url: makeUrl('customers', [
            { key: 'search', value: '012888999', disabled: false }
          ])
        }
      },
      {
        name: 'Customers - Get Customer Details',
        request: {
          method: 'GET',
          header: authHeader('token'),
          url: makeUrl('customers/1')
        }
      },
      {
        name: 'Customers - Register Customer',
        request: {
          method: 'POST',
          header: jsonHeaders('token'),
          body: {
            mode: 'raw',
            raw: JSON.stringify({
              customer_name: 'Bopha Vong',
              phone: '012888999',
              email: 'bopha.vong@example.com',
              address: '#45, St 214, Phnom Penh'
            }, null, 2)
          },
          url: makeUrl('customers')
        }
      },
      {
        name: 'Customers - Update Profile',
        request: {
          method: 'PUT',
          header: jsonHeaders('token'),
          body: {
            mode: 'raw',
            raw: JSON.stringify({
              customer_name: 'Bopha Vong (VIP)',
              address: '#45B, St 214, Daun Penh, Phnom Penh'
            }, null, 2)
          },
          url: makeUrl('customers/1')
        }
      },
      {
        name: 'Loyalty - View Customer Loyalty Points & Tier',
        request: {
          method: 'GET',
          header: authHeader('token'),
          url: makeUrl('customers/1/loyalty')
        }
      },
      {
        name: 'Loyalty - Redeem Loyalty Points',
        request: {
          method: 'POST',
          header: jsonHeaders('token'),
          body: {
            mode: 'raw',
            raw: JSON.stringify({
              points_to_redeem: 100,
              discount_value: 5.00
            }, null, 2)
          },
          url: makeUrl('customers/1/redeem-points')
        }
      },
      {
        name: 'Wishlist - View Customer Wishlist',
        request: {
          method: 'GET',
          header: [{ key: 'Accept', value: 'application/json' }],
          url: makeUrl('wishlist', [
            { key: 'session_id', value: 'guest_session_123', disabled: false }
          ])
        }
      },
      {
        name: 'Wishlist - Toggle Item In Wishlist',
        request: {
          method: 'POST',
          header: jsonHeaders(),
          body: {
            mode: 'raw',
            raw: JSON.stringify({
              product_id: 19,
              session_id: 'guest_session_123'
            }, null, 2)
          },
          url: makeUrl('wishlist/toggle')
        }
      }
    ]
  },
  {
    name: '09. Inventory Forecasting, Suppliers & Purchasing',
    description: 'Smart restock forecasting, auto purchase orders, stock movement adjustments, and supplier management.',
    item: [
      {
        name: 'Inventory - SalesBinder Financial Valuation & Statistics',
        request: {
          method: 'GET',
          header: [{ key: 'Accept', value: 'application/json' }],
          url: makeUrl('inventory/statistics', [
            { key: 'only_in_stock', value: 'true', disabled: true },
            { key: 'category_id', value: '1', disabled: true }
          ]),
          description: 'SalesBinder-style Total Valuation (Purchased Value vs Resale Value), Gross Margin, and On-Hand / Reserved / Available / Incoming unit breakdown.'
        }
      },
      {
        name: 'Forecasting - Restock Recommendations (AI/Velocity)',
        request: {
          method: 'GET',
          header: authHeader('manager_token'),
          url: makeUrl('inventory/restock-recommendations')
        }
      },
      {
        name: 'Purchasing - Auto-Generate Purchase Order',
        request: {
          method: 'POST',
          header: jsonHeaders('manager_token'),
          body: {
            mode: 'raw',
            raw: JSON.stringify({
              supplier_id: 1,
              order_notes: 'Auto-replenishment based on weekly velocity'
            }, null, 2)
          },
          url: makeUrl('purchases/auto-generate')
        }
      },
      {
        name: 'Inventory - Stock Movements Audit Feed',
        request: {
          method: 'GET',
          header: authHeader('manager_token'),
          url: makeUrl('stock-movements', [
            { key: 'variant_id', value: '1', disabled: true },
            { key: 'movement_type', value: 'SALE', disabled: true }
          ])
        }
      },
      {
        name: 'Inventory - Manual Stock Adjustment',
        request: {
          method: 'POST',
          header: jsonHeaders('manager_token'),
          body: {
            mode: 'raw',
            raw: JSON.stringify({
              variant_id: 1,
              adjustment_type: 'CORRECTION',
              quantity: 5,
              reason: 'Physical cycle count adjustment'
            }, null, 2)
          },
          url: makeUrl('stock-movements/adjust')
        }
      },
      {
        name: 'Suppliers - List All',
        request: {
          method: 'GET',
          header: authHeader('manager_token'),
          url: makeUrl('suppliers')
        }
      },
      {
        name: 'Suppliers - Get Supplier By ID',
        request: {
          method: 'GET',
          header: authHeader('manager_token'),
          url: makeUrl('suppliers/1')
        }
      },
      {
        name: 'Suppliers - Create Supplier',
        request: {
          method: 'POST',
          header: jsonHeaders('manager_token'),
          body: {
            mode: 'raw',
            raw: JSON.stringify({
              supplier_name: 'Battambang Artisan Weavers Co.',
              contact_name: 'Vannak Heng',
              phone: '012999000',
              email: 'vannak@battambangweavers.com',
              address: 'National Road 5, Battambang'
            }, null, 2)
          },
          url: makeUrl('suppliers')
        }
      },
      {
        name: 'Suppliers - Update Supplier',
        request: {
          method: 'PUT',
          header: jsonHeaders('manager_token'),
          body: {
            mode: 'raw',
            raw: JSON.stringify({
              phone: '012999111',
              address: 'Updated Battambang HQ Office'
            }, null, 2)
          },
          url: makeUrl('suppliers/1')
        }
      },
      {
        name: 'Suppliers - Delete Supplier',
        request: {
          method: 'DELETE',
          header: authHeader('manager_token'),
          url: makeUrl('suppliers/1')
        }
      },
      {
        name: 'Purchases - List Purchase Orders',
        request: {
          method: 'GET',
          header: authHeader('manager_token'),
          url: makeUrl('purchases')
        }
      },
      {
        name: 'Purchases - Get Purchase Order By ID',
        request: {
          method: 'GET',
          header: authHeader('manager_token'),
          url: makeUrl('purchases/1')
        }
      },
      {
        name: 'Purchases - Create Purchase Order',
        request: {
          method: 'POST',
          header: jsonHeaders('manager_token'),
          body: {
            mode: 'raw',
            raw: JSON.stringify({
              supplier_id: 1,
              purchase_date: '2026-08-17',
              items: [
                {
                  variant_id: 1,
                  quantity: 50,
                  cost_price: 14.00
                }
              ],
              notes: 'Restock order for upcoming holiday sales event'
            }, null, 2)
          },
          url: makeUrl('purchases')
        }
      }
    ]
  },
  {
    name: '10. Multi-Branch & Omnichannel Shipping',
    description: 'Store branch stock isolation, click-and-collect, and dispatch delivery tracking.',
    item: [
      {
        name: 'Branches - List All Store Locations',
        request: {
          method: 'GET',
          header: [{ key: 'Accept', value: 'application/json' }],
          url: makeUrl('branches')
        }
      },
      {
        name: 'Branches - Create Store Branch (Manager/Admin)',
        request: {
          method: 'POST',
          header: jsonHeaders('manager_token'),
          body: {
            mode: 'raw',
            raw: JSON.stringify({
              branch_name: 'KhmeRiel Siem Reap Flagship',
              location: 'Pub Street / Old Market Area, Siem Reap',
              phone: '063999888'
            }, null, 2)
          },
          url: makeUrl('branches')
        }
      },
      {
        name: 'Branches - Get Stock by Branch ID',
        request: {
          method: 'GET',
          header: authHeader('manager_token'),
          url: makeUrl('branches/1/stock')
        }
      },
      {
        name: 'Shipping - List Shipping Orders',
        request: {
          method: 'GET',
          header: authHeader('token'),
          url: makeUrl('shipping/orders')
        }
      },
      {
        name: 'Shipping - Create Dispatch Order',
        request: {
          method: 'POST',
          header: jsonHeaders('token'),
          body: {
            mode: 'raw',
            raw: JSON.stringify({
              sale_id: 1,
              recipient_name: 'Chanthou Seng',
              recipient_phone: '098112233',
              shipping_address: 'Toul Kork, Phnom Penh',
              courier_service: 'J&T Express / Grab Express',
              tracking_number: 'JT-KH-2026-8877'
            }, null, 2)
          },
          url: makeUrl('shipping/create')
        }
      },
      {
        name: 'Shipping - Update Order Status',
        request: {
          method: 'POST',
          header: jsonHeaders('token'),
          body: {
            mode: 'raw',
            raw: JSON.stringify({
              status: 'DELIVERED',
              notes: 'Delivered safely to recipient front desk'
            }, null, 2)
          },
          url: makeUrl('shipping/1/status')
        }
      }
    ]
  },
  {
    name: '11. Marketing, Banners & Promotions',
    description: 'Hero banners, promo codes, percentage discounts, and flash sales.',
    item: [
      {
        name: 'Banners - List Marketing Banners',
        request: {
          method: 'GET',
          header: [{ key: 'Accept', value: 'application/json' }],
          url: makeUrl('marketing/banners')
        }
      },
      {
        name: 'Banners - Create Banner (Manager/Admin)',
        request: {
          method: 'POST',
          header: jsonHeaders('manager_token'),
          body: {
            mode: 'raw',
            raw: JSON.stringify({
              title: 'Khmer New Year Heritage Collection',
              subtitle: 'Up to 30% OFF artisan woven garments',
              image_url: 'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?w=1200',
              link_url: '/category/silk',
              is_active: true,
              display_order: 1
            }, null, 2)
          },
          url: makeUrl('marketing/banners')
        }
      },
      {
        name: 'Banners - Delete Banner (Manager/Admin)',
        request: {
          method: 'DELETE',
          header: authHeader('manager_token'),
          url: makeUrl('marketing/banners/1')
        }
      },
      {
        name: 'Promotions - List All Promotions',
        request: {
          method: 'GET',
          header: authHeader('manager_token'),
          url: makeUrl('promotions')
        }
      },
      {
        name: 'Promotions - List Active Promotions (Public)',
        request: {
          method: 'GET',
          header: [{ key: 'Accept', value: 'application/json' }],
          url: makeUrl('promotions/active')
        }
      },
      {
        name: 'Promotions - Verify Coupon Code',
        request: {
          method: 'POST',
          header: jsonHeaders(),
          body: {
            mode: 'raw',
            raw: JSON.stringify({
              coupon_code: 'WELCOME10',
              order_amount: 50.00
            }, null, 2)
          },
          url: makeUrl('promotions/verify-coupon')
        }
      },
      {
        name: 'Promotions - Create Promotion (Manager/Admin)',
        request: {
          method: 'POST',
          header: jsonHeaders('manager_token'),
          body: {
            mode: 'raw',
            raw: JSON.stringify({
              title: 'VIP 15% OFF',
              promo_code: 'VIP15',
              discount_type: 'PERCENTAGE',
              discount_value: 15,
              min_purchase_amount: 30.00,
              start_date: '2026-08-01',
              end_date: '2026-12-31',
              is_active: true
            }, null, 2)
          },
          url: makeUrl('promotions')
        }
      },
      {
        name: 'Promotions - Delete Promotion (Manager/Admin)',
        request: {
          method: 'DELETE',
          header: authHeader('manager_token'),
          url: makeUrl('promotions/1')
        }
      }
    ]
  },
  {
    name: '12. Cloudinary Media Gallery & Uploads',
    description: 'Cloudinary CDN media management, direct upload, and gallery inspection.',
    item: [
      {
        name: 'Media - Gallery Explorer',
        request: {
          method: 'GET',
          header: authHeader('manager_token'),
          url: makeUrl('uploads/gallery')
        }
      },
      {
        name: 'Media - Upload Single Image (Multipart/File)',
        request: {
          method: 'POST',
          header: [
            { key: 'Authorization', value: 'Bearer {{manager_token}}' },
            { key: 'Accept', value: 'application/json' }
          ],
          body: {
            mode: 'formdata',
            formdata: [
              { key: 'image', type: 'file', src: '' },
              { key: 'folder', value: 'khmeriel_products', type: 'text' }
            ]
          },
          url: makeUrl('uploads/image')
        }
      },
      {
        name: 'Media - Upload Batch Images',
        request: {
          method: 'POST',
          header: [
            { key: 'Authorization', value: 'Bearer {{manager_token}}' },
            { key: 'Accept', value: 'application/json' }
          ],
          body: {
            mode: 'formdata',
            formdata: [
              { key: 'images[]', type: 'file', src: '' },
              { key: 'folder', value: 'khmeriel_products', type: 'text' }
            ]
          },
          url: makeUrl('uploads/batch')
        }
      },
      {
        name: 'Media - Delete Image from Cloudinary',
        request: {
          method: 'DELETE',
          header: jsonHeaders('manager_token'),
          body: {
            mode: 'raw',
            raw: JSON.stringify({
              public_id: 'khmeriel_products/sample_image'
            }, null, 2)
          },
          url: makeUrl('uploads/image')
        }
      }
    ]
  },
  {
    name: '13. Admin Master Analytics, Broadcast & Audit Logs',
    description: 'Role-Pulse graphs, Admin Master Command Waterfall, Employees CRUD, and Audit Logs.',
    item: [
      {
        name: 'Analytics - Role-Pulse Live Analytics',
        request: {
          method: 'GET',
          header: authHeader('token'),
          url: makeUrl('dashboard/role-pulse'),
          description: 'Dynamic pie chart metrics customized per logged-in role (Cashier, Manager, Admin).'
        }
      },
      {
        name: 'Analytics - High-Level Dashboard Stats (Manager/Admin)',
        request: {
          method: 'GET',
          header: authHeader('manager_token'),
          url: makeUrl('dashboard/stats')
        }
      },
      {
        name: 'Admin - Master Controller Pulse (Staff Timesheets & Waterfall)',
        request: {
          method: 'GET',
          header: authHeader('admin_token'),
          url: makeUrl('admin/master-pulse')
        }
      },
      {
        name: 'Admin - Send System Broadcast Alert',
        request: {
          method: 'POST',
          header: jsonHeaders('admin_token'),
          body: {
            mode: 'raw',
            raw: JSON.stringify({
              title: 'Flash Sale Starting in 1 Hour!',
              message: 'Please ensure all thermal receipt printers have extra paper rolls and barcode scanners are synced.',
              severity: 'WARNING'
            }, null, 2)
          },
          url: makeUrl('admin/broadcast-alert')
        }
      },
      {
        name: 'Alerts - Get Active Broadcast Feed (Staff/Cashier)',
        request: {
          method: 'GET',
          header: authHeader('token'),
          url: makeUrl('alerts/active')
        }
      },
      // Employees
      {
        name: 'Employees - List All Staff',
        request: {
          method: 'GET',
          header: authHeader('admin_token'),
          url: makeUrl('employees')
        }
      },
      {
        name: 'Employees - Get Employee Details',
        request: {
          method: 'GET',
          header: authHeader('admin_token'),
          url: makeUrl('employees/1')
        }
      },
      {
        name: 'Employees - Create Employee Profile',
        request: {
          method: 'POST',
          header: jsonHeaders('admin_token'),
          body: {
            mode: 'raw',
            raw: JSON.stringify({
              first_name: 'Chanthy',
              last_name: 'Pich',
              phone: '012555666',
              email: 'chanthy.pich@ssmis.local',
              role: 'CASHIER',
              salary: 350.00,
              hire_date: '2026-08-17'
            }, null, 2)
          },
          url: makeUrl('employees')
        }
      },
      {
        name: 'Employees - Update Employee Profile',
        request: {
          method: 'PUT',
          header: jsonHeaders('admin_token'),
          body: {
            mode: 'raw',
            raw: JSON.stringify({
              salary: 400.00,
              role: 'MANAGER'
            }, null, 2)
          },
          url: makeUrl('employees/1')
        }
      },
      {
        name: 'Employees - Delete Employee Record',
        request: {
          method: 'DELETE',
          header: authHeader('admin_token'),
          url: makeUrl('employees/1')
        }
      },
      // Audit Logs
      {
        name: 'Audit Logs - Inspect Immutable Trail',
        request: {
          method: 'GET',
          header: authHeader('manager_token'),
          url: makeUrl('audit-logs', [
            { key: 'action', value: 'SALE_CHECKOUT', disabled: true },
            { key: 'per_page', value: '25', disabled: false }
          ])
        }
      },
      {
        name: 'Audit Logs - Get Single Audit Log',
        request: {
          method: 'GET',
          header: authHeader('manager_token'),
          url: makeUrl('audit-logs/1')
        }
      }
    ]
  }
];

// Count total requests across all items
let totalEndpoints = 0;
collectionItems.forEach(group => {
  totalEndpoints += group.item.length;
});

// Master Collection JSON
const masterCollection = {
  info: {
    _postman_id: 'khmeriel-ssmis-master-full-api-v1',
    name: `KhmeRiel MIS & POS — Complete 100% API Collection (${totalEndpoints} Endpoints)`,
    description: `Complete Master Postman Collection containing 100% of all ${totalEndpoints} API endpoints across all RBAC Access Tiers (Public/Storefront, Cashier, Manager, Admin). Includes automated token management, 10% VAT tax calculations, KHQR, shifts, inventory FIFO batches, and media uploads.`,
    schema: 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json'
  },
  variable: [
    {
      key: 'base_url',
      value: LOCAL_URL,
      type: 'string',
      description: 'Active API Base URL (Local or Production)'
    },
    {
      key: 'token',
      value: '',
      type: 'string',
      description: 'Current active Bearer Token (automatically set by Login)'
    },
    {
      key: 'admin_token',
      value: '',
      type: 'string',
      description: 'Admin Bearer Token'
    },
    {
      key: 'manager_token',
      value: '',
      type: 'string',
      description: 'Manager Bearer Token'
    },
    {
      key: 'cashier_token',
      value: '',
      type: 'string',
      description: 'Cashier Bearer Token'
    },
    {
      key: 'staff_token',
      value: '',
      type: 'string',
      description: 'Staff Bearer Token'
    }
  ],
  item: collectionItems
};

// Master Local Collection JSON
const masterLocalCollection = {
  ...masterCollection,
  info: {
    ...masterCollection.info,
    _postman_id: 'khmeriel-ssmis-master-local-api-v1',
    name: `KhmeRiel MIS & POS — Local Dev (127.0.0.1:8000) [${totalEndpoints} Endpoints]`
  },
  variable: masterCollection.variable.map(v => v.key === 'base_url' ? { ...v, value: LOCAL_URL } : v)
};

// Master Production Collection JSON (Pre-configured directly with Real Product URL)
const masterProdCollection = {
  ...masterCollection,
  info: {
    ...masterCollection.info,
    _postman_id: 'khmeriel-ssmis-master-production-api-v1',
    name: `KhmeRiel MIS & POS — Production Real Product (api.kesararamwithdigital.tech) [${totalEndpoints} Endpoints]`
  },
  variable: masterCollection.variable.map(v => v.key === 'base_url' ? { ...v, value: PROD_URL } : v)
};

// 1. Local Environment File
const localEnv = {
  id: 'khmeriel-env-local',
  name: 'KhmeRiel MIS & POS — 1. Local Development (127.0.0.1:8000)',
  values: [
    { key: 'base_url', value: LOCAL_URL, type: 'default', enabled: true },
    { key: 'token', value: '', type: 'secret', enabled: true },
    { key: 'admin_token', value: '', type: 'secret', enabled: true },
    { key: 'manager_token', value: '', type: 'secret', enabled: true },
    { key: 'cashier_token', value: '', type: 'secret', enabled: true },
    { key: 'staff_token', value: '', type: 'secret', enabled: true }
  ],
  _postman_variable_scope: 'environment'
};

// 2. Production Environment File
const prodEnv = {
  id: 'khmeriel-env-production',
  name: 'KhmeRiel MIS & POS — 2. Real Product (Production API)',
  values: [
    { key: 'base_url', value: PROD_URL, type: 'default', enabled: true },
    { key: 'token', value: '', type: 'secret', enabled: true },
    { key: 'admin_token', value: '', type: 'secret', enabled: true },
    { key: 'manager_token', value: '', type: 'secret', enabled: true },
    { key: 'cashier_token', value: '', type: 'secret', enabled: true },
    { key: 'staff_token', value: '', type: 'secret', enabled: true }
  ],
  _postman_variable_scope: 'environment'
};

// Write files
const postmanDir = path.join(__dirname, '..', 'postman');
if (!fs.existsSync(postmanDir)) {
  fs.mkdirSync(postmanDir, { recursive: true });
}

// 1. Production Dedicated Collection (Instant 1-Click for Real Product)
fs.writeFileSync(
  path.join(postmanDir, 'production.json'),
  JSON.stringify(masterProdCollection, null, 2)
);

// 2. Local Dedicated Collection
fs.writeFileSync(
  path.join(postmanDir, 'local.json'),
  JSON.stringify(masterLocalCollection, null, 2)
);

// 3. Default Collection (also root copy)
fs.writeFileSync(
  path.join(postmanDir, 'khmeriel_ssmis_postman_collection.json'),
  JSON.stringify(masterProdCollection, null, 2)
);
fs.writeFileSync(
  path.join(__dirname, '..', 'postman_collection.json'),
  JSON.stringify(masterProdCollection, null, 2)
);

// 4. Environments
fs.writeFileSync(
  path.join(postmanDir, 'khmeriel_ssmis_postman_environment_production.json'),
  JSON.stringify(prodEnv, null, 2)
);
fs.writeFileSync(
  path.join(postmanDir, 'khmeriel_ssmis_postman_environment_local.json'),
  JSON.stringify(localEnv, null, 2)
);
fs.writeFileSync(
  path.join(postmanDir, 'khmeriel_ssmis_postman_environment.json'),
  JSON.stringify(prodEnv, null, 2)
);

console.log(`✅ Successfully generated Complete Postman Collections with ${totalEndpoints} endpoints!`);
console.log('✅ Generated PRODUCTION Collection: postman/production.json (Target: https://api.kesararamwithdigital.tech/api/v1)');
console.log('✅ Generated LOCAL Collection: postman/local.json (Target: http://127.0.0.1:8000/api/v1)');
console.log('✅ Generated Production Environment: postman/khmeriel_ssmis_postman_environment_production.json');
console.log('✅ Generated Local Environment: postman/khmeriel_ssmis_postman_environment_local.json');
