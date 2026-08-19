---
name: postman-collection-protocol
description: >
  Authoritative skill for rebuilding and maintaining the OutfitShop SS-MIS Postman
  collection. Defines structure, naming, real IDs, folder layout, token automation,
  and sync targets. Use whenever the user says "rewrite the collection", "update
  postman", or "add endpoint to collection".
---

# Postman Collection Protocol — OutfitShop SS-MIS

## Single Source of Truth

One collection file only:

```
postman/OutfitShop_Master_Collection.json
```

All other locations are copies synced from this file. Never edit them directly.

Sync targets (copy after every rebuild):
- `API-Delivery-Package/postman_collection.json`
- `docs/postman_collection.json`
- `postman_collection.json` (root)
- `public/SS_MIS.postman_collection.json`

Delete any other `.json` collection or environment file found in `postman/` or `docs/`.

---

## Real Production IDs (always use these, never placeholder 1/2/3)

| Variable        | Real Value        | Notes                        |
|-----------------|-------------------|------------------------------|
| `product_id`    | `182`             | Gucci Oxford Shirt           |
| `variant_id`    | `174`             | SKU-GUC-0182                 |
| `barcode`       | `SKU-GUC-0182`    | Barcode for variant 174      |
| `brand_id`      | `3`               | Gucci                        |
| `category_id`   | `14`              | Men's Ready-to-Wear          |
| `size_id`       | `1`               | STD (Standard One Size)      |
| `color_id`      | `1`               | Default                      |
| `employee_id`   | `1`               | admin                        |
| `order_id`      | `1`               | First completed sale         |
| `sale_id`       | `1`               | Legacy alias of order 1      |
| `base_url`      | `https://api.kesararamwithdigital.tech/api/v1` | Production |

When the user runs a rewrite command, always call the live API first to confirm
the latest IDs before writing the collection.

---

## Folder Naming Convention

```
00 - Login (Run First)
01 - Health & System
02 - Currency
03 - Products (Public Read)
04 - Variants (Public Read)
05 - Categories (Public Read)
06 - Brands (Public Read)
07 - Sizes & Colors (Public Read)
08 - Bundles & Promotions (Public Read)
09 - Cart (Public)
10 - Wishlist (Public)
11 - Inventory Stats & Branches (Public)
12 - Payments, KHQR & Receipts (Public)
13 - Banners & Settings (Public)
14 - Session & Token Management [TIER 2]
15 - Customers & Loyalty [TIER 2]
16 - POS Shifts / Z-Report [TIER 2]
17 - Orders & POS Checkout [TIER 2]
18 - Invoices, Estimates & Gift Cards [TIER 2]
19 - Shipping & Offline Sync [TIER 2]
20 - Dashboard & Alerts [TIER 2]
21 - Analytics & Forecasting [TIER 3]
22 - Categories Write [TIER 3]
23 - Brands Write [TIER 3]
24 - Sizes & Colors Write [TIER 3]
25 - Products Write [TIER 3]
26 - Variants Write [TIER 3]
27 - Bundles Write [TIER 3]
28 - Promotions Write [TIER 3]
29 - Suppliers [TIER 3]
30 - Purchases [TIER 3]
31 - Stock Movements & Adjustments [TIER 3]
32 - Stock Transfers (5-Stage) [TIER 3]
33 - Inventory Batches (FIFO) [TIER 3]
34 - Branches Write [TIER 3]
35 - Bulk Operations [TIER 3]
36 - Image Uploads (Cloudinary) [TIER 3]
37 - Order Void & Audit Logs [TIER 3]
38 - Marketing Banners Write [TIER 3]
39 - File Exports [TIER 3]
40 - MIS Reports [TIER 3]
41 - AI Intelligence [TIER 3]
42 - GDPR Compliance [TIER 3]
43 - Webhooks [TIER 3]
44 - Employees [TIER 4 — ADMIN ONLY]
45 - User Account Management [TIER 4 — ADMIN ONLY]
46 - Admin Monitoring [TIER 4 — ADMIN ONLY]
```

---

## Request Naming Convention

Format: `METHOD Description (ID if applicable)`

Examples:
- `GET All Products`
- `GET Product by ID (182)`
- `POST Create Product`
- `PUT Update Product (182 — Gucci Oxford Shirt)`
- `DELETE Product (182)`
- `POST POS Checkout (orders)`
- `GET Sale by ID (1) [legacy]`

---

## Token Auto-Capture

Every Login request in folder `00 - Login` MUST have a test script:

```javascript
const r = pm.response.json();
if (r.success && r.data && r.data.access_token) {
  pm.collectionVariables.set('token', r.data.access_token);
}
```

The Refresh Token request MUST also update `{{token}}` on success.

---

## Collection-Level Auth

```json
"auth": {
  "type": "bearer",
  "bearer": [{"key": "token", "value": "{{token}}", "type": "string"}]
}
```

All TIER 2/3/4 requests use explicit `Authorization: Bearer {{token}}` header.
TIER 1 (public) requests have no Authorization header.

---

## Total Endpoint Count

| Tier | Folders | Requests |
|------|---------|----------|
| Login | 1 | 5 |
| TIER 1 PUBLIC | 13 | 64 |
| TIER 2 AUTHENTICATED | 7 | 38 |
| TIER 3 MANAGER | 23 | 81 |
| TIER 4 ADMIN | 3 | 13 |
| **Total** | **47** | **199** |

---

## Rewrite Trigger

When the user says any of the following, fully rebuild the collection from the
live routes/api.php and real API IDs:
- "rewrite the collection"
- "rewrite postman"
- "update postman"
- "rebuild collection"

Steps:
1. Fetch real IDs from live API (login, get products, variants, etc.)
2. Delete `postman/OutfitShop_Master_Collection.json`
3. Rebuild from scratch following this skill
4. Validate JSON: `python3 -m json.tool <file> > /dev/null`
5. Sync to all copy targets
6. Commit and `pm`
