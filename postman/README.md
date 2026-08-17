# KhmeRiel MIS & POS — Complete 100% Master Postman Collection

This directory contains the **complete, zero-endpoint-skipped** Postman workspace for the **KhmeRiel MIS & POS REST API (124 Endpoints)** with full support for both **Local Development** and **Real Product (Production)** testing.

---

## 📁 Files Included

1. **[`production.json`](file:///Users/Apple16/Desktop/SS_MIS/postman/production.json)** *(⭐ Real Product / Production Collection)*
   - Target URL: `https://api.kesararamwithdigital.tech/api/v1`
   - Pre-configured directly for live production testing. 1-click import and test without having to set up environment variables!
   - Contains all **124 endpoints** across 13 categories.

2. **[`local.json`](file:///Users/Apple16/Desktop/SS_MIS/postman/local.json)** *(💻 Local Development Collection)*
   - Target URL: `http://127.0.0.1:8000/api/v1`
   - Pre-configured for local server testing (`php artisan serve`).

3. **[`khmeriel_ssmis_postman_environment_production.json`](file:///Users/Apple16/Desktop/SS_MIS/postman/khmeriel_ssmis_postman_environment_production.json)**
   - Production environment variables.

4. **[`khmeriel_ssmis_postman_environment_local.json`](file:///Users/Apple16/Desktop/SS_MIS/postman/khmeriel_ssmis_postman_environment_local.json)**
   - Local environment variables.

---

## 🚀 How to Import into Postman

1. Open **Postman**.
2. Click **Import** (top left).
3. Drag & drop the following 3 files into Postman:
   - `postman/khmeriel_ssmis_postman_collection.json`
   - `postman/khmeriel_ssmis_postman_environment_local.json`
   - `postman/khmeriel_ssmis_postman_environment_production.json`

---

## ⚡ How to Switch Between Local & Real Product

In Postman, look at the **Environment Dropdown** in the top right corner:

| Target | Select Environment | Base URL |
| :--- | :--- | :--- |
| 💻 **Local Dev** | `KhmeRiel MIS & POS — 1. Local Development` | `http://127.0.0.1:8000/api/v1` |
| 🌐 **Real Product** | `KhmeRiel MIS & POS — 2. Real Product (Production API)` | `https://api.kesararamwithdigital.tech/api/v1` |

---

## 🔐 1-Click Authentication Workflow

You don't need to manually copy-paste tokens:
1. Open folder **`01. System & Authentication`**.
2. Click **`Auth - Login (Superadmin / Admin)`** $\rightarrow$ Click **Send**.
3. Postman's built-in test script will automatically extract the Bearer token and set:
   - `{{token}}`
   - `{{admin_token}}`
4. Now, every single protected endpoint in all 13 folders will work immediately without manual authorization steps!

---

## 📊 Complete Directory of 13 Modules (124 Endpoints)

- **`01. System & Authentication`** (9 requests): Health check, status & branding, POS audio cues, Login (Admin/Manager/Cashier), Current user (`/me`), Logout, Staff registration.
- **`02. Categories, Brands, Sizes & Colors`** (20 requests): Full CRUD for categories, brands, clothing sizes, and color swatches.
- **`03. Products & 2D Matrix`** (14 requests): Products CRUD, 2D size/color matrix, Ralph Lauren-style colorways, digital lookbook download, images, and reviews.
- **`04. Product Variants, Tiers & Batches`** (14 requests): SKU inventory, barcode scanner lookup, low stock thresholds, wholesale pricing tiers, FIFO batches, and thermal barcode printing.
- **`05. Product Bundles & Combos`** (4 requests): Bundled sets, gift sets, and multi-item combos.
- **`06. POS Cash Register Shifts (Z-Report)`** (4 requests): Active shift status, opening drawer float, midday cash drops, and closing Z-Report calculation.
- **`07. POS Sales, Checkout, Receipts & KHQR`** (9 requests): 10% tax-exclusive POS checkout, sales history, Bakong dynamic KHQR payment, thermal 80mm ESC/POS receipt, invoice voiding, gift card checking & issuing.
- **`08. Customers, Loyalty & Wishlist`** (8 requests): Customer profiles, VIP phone search, loyalty tier & points redemption, guest wishlists.
- **`09. Inventory Forecasting, Suppliers & Purchasing`** (12 requests): Velocity restock recommendations, auto-generated POs, stock movement audit trail, physical cycle counts, supplier CRUD, and purchase orders.
- **`10. Multi-Branch & Omnichannel Shipping`** (6 requests): Multi-store locations, per-branch stock levels, and dispatch courier tracking.
- **`11. Marketing, Banners & Promotions`** (8 requests): Hero banners, active promos, coupon validation, and promo code CRUD.
- **`12. Cloudinary Media Gallery & Uploads`** (4 requests): Media gallery inspection, single file upload, batch multi-file upload, and asset removal.
- **`13. Admin Master Analytics, Broadcast & Audit Logs`** (12 requests): Role-pulse analytics, admin waterfall command center, system broadcast alerts, employees CRUD, and immutable audit logs.
