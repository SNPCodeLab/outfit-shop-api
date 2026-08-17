# Store Stock and Point-of-Sale Information System (SS-MIS) API

RESTful API backend for retail clothing store inventory, supplier purchasing, POS checkout, omnichannel logistics, and role-based administrative control.

---

## 1. System Classification

* **IS Type**: Transaction Processing System (TPS / OLTP) with embedded Management Information System (MIS) Reporting  
* **Architecture**: Monolithic, Headless REST API Backend (Laravel 11 + PostgreSQL 16)
* **Access Model**: 4-Tier Role-Based Access Control (`ADMIN`, `MANAGER`, `CASHIER`, `STAFF`)
* **Total Database Entities**: 48 Tables across 6 Core Operational Domains

---

## 2. Complete Database Entity Mindmap Diagrams (All 48 Tables)

### 2.0 Master Database Architecture Overview (All 6 Domains)

```mermaid
mindmap
  root((SS-MIS Database<br/>48 Tables))
    Domain 1: Product Catalog
      categories
      brands
      clothing_sizes
      colors
      products
      product_variants
      product_images
      variant_pricing_tiers
      product_batches
      product_reviews
      product_bundles
      bundle_items
    Domain 2: POS & Sales Checkout
      sale_headers
      sale_details
      payments
      pos_shifts
      gift_cards
      promotions
      marketing_banners
    Domain 3: Branches, CRM & Shipping
      store_branches
      store_inventories
      shipping_orders
      customers
      customer_loyalty_logs
      customer_wishlists
    Domain 4: Purchasing & Inventory Ledger
      suppliers
      purchase_headers
      purchase_details
      stock_movements
    Domain 5: RBAC & Workforce
      users
      employees
      roles
      permissions
      model_has_roles
      model_has_permissions
      role_has_permissions
      personal_access_tokens
      password_reset_tokens
    Domain 6: Observability & Infrastructure
      audit_logs
      api_logs
      system_broadcast_alerts
      sessions
      cache
      cache_locks
      jobs
      job_batches
      failed_jobs
      migrations
```

---

### 2.1 Domain 1: Product Catalog & Luxury Apparel Merchandise (12 Tables)

```mermaid
flowchart TB
    subgraph D1["Domain 1: Product Catalog & SKU Matrix (12 Tables)"]
        CATEGORIES["CATEGORIES (1)<br/>- category_id [PK]<br/>- category_name<br/>- department_type<br/>- description"]
        BRANDS["BRANDS (2)<br/>- brand_id [PK]<br/>- brand_name<br/>- country_of_origin<br/>- description"]
        CLOTHING_SIZES["CLOTHING_SIZES (3)<br/>- size_id [PK]<br/>- size_name<br/>- size_code<br/>- sort_order"]
        COLORS["COLORS (4)<br/>- color_id [PK]<br/>- color_name<br/>- hex_code<br/>- description"]
        
        PRODUCTS["PRODUCTS (5)<br/>- product_id [PK]<br/>- category_id [FK]<br/>- brand_id [FK]<br/>- product_name<br/>- product_code<br/>- base_price<br/>- cost_price<br/>- image_url<br/>- season_collection"]
        
        PRODUCT_IMAGES["PRODUCT_IMAGES (6)<br/>- image_id [PK]<br/>- product_id [FK]<br/>- image_url<br/>- is_primary<br/>- sort_order"]
        PRODUCT_REVIEWS["PRODUCT_REVIEWS (7)<br/>- review_id [PK]<br/>- product_id [FK]<br/>- customer_name<br/>- rating<br/>- comment"]
        
        PRODUCT_VARIANTS["PRODUCT_VARIANTS (8)<br/>- variant_id [PK]<br/>- product_id [FK]<br/>- size_id [FK]<br/>- color_id [FK]<br/>- sku<br/>- barcode<br/>- unit_price<br/>- stock_quantity<br/>- reorder_level"]
        
        VARIANT_PRICING_TIERS["VARIANT_PRICING_TIERS (9)<br/>- tier_id [PK]<br/>- variant_id [FK]<br/>- min_quantity<br/>- tier_price<br/>- label"]
        PRODUCT_BATCHES["PRODUCT_BATCHES (10)<br/>- batch_id [PK]<br/>- variant_id [FK]<br/>- batch_number<br/>- quantity<br/>- expiry_date"]
        
        PRODUCT_BUNDLES["PRODUCT_BUNDLES (11)<br/>- bundle_id [PK]<br/>- bundle_name<br/>- bundle_code<br/>- bundle_price"]
        BUNDLE_ITEMS["BUNDLE_ITEMS (12)<br/>- item_id [PK]<br/>- bundle_id [FK]<br/>- variant_id [FK]<br/>- quantity"]
    end

    CATEGORIES --> PRODUCTS
    BRANDS --> PRODUCTS
    PRODUCTS --> PRODUCT_IMAGES
    PRODUCTS --> PRODUCT_REVIEWS
    PRODUCTS --> PRODUCT_VARIANTS
    CLOTHING_SIZES --> PRODUCT_VARIANTS
    COLORS --> PRODUCT_VARIANTS
    PRODUCT_VARIANTS --> VARIANT_PRICING_TIERS
    PRODUCT_VARIANTS --> PRODUCT_BATCHES
    PRODUCT_BUNDLES --> BUNDLE_ITEMS
    PRODUCT_VARIANTS --> BUNDLE_ITEMS
```

---

### 2.2 Domain 2: Point-of-Sale (POS), Cash Register & Financial Transactions (7 Tables)

```mermaid
flowchart TB
    subgraph D2["Domain 2: Point-of-Sale & Financial Transactions (7 Tables)"]
        POS_SHIFTS["POS_SHIFTS (13)<br/>- shift_id [PK]<br/>- employee_id [FK]<br/>- branch_id [FK]<br/>- opening_float<br/>- closing_cash_counted<br/>- cash_variance<br/>- status"]
        
        PROMOTIONS["PROMOTIONS (14)<br/>- promo_id [PK]<br/>- title<br/>- promo_code<br/>- discount_type<br/>- discount_value<br/>- is_active"]
        
        MARKETING_BANNERS["MARKETING_BANNERS (15)<br/>- banner_id [PK]<br/>- title<br/>- image_url<br/>- link_url<br/>- is_active"]
        
        GIFT_CARDS["GIFT_CARDS (16)<br/>- card_id [PK]<br/>- customer_id [FK]<br/>- card_code<br/>- current_balance<br/>- expires_at"]
        
        SALE_HEADERS["SALE_HEADERS (17)<br/>- sale_id [PK]<br/>- invoice_number<br/>- branch_id [FK]<br/>- employee_id [FK]<br/>- customer_id [FK]<br/>- promo_id [FK]<br/>- subtotal<br/>- discount_amount<br/>- tax_amount (10% VAT)<br/>- grand_total<br/>- status"]
        
        SALE_DETAILS["SALE_DETAILS (18)<br/>- detail_id [PK]<br/>- sale_id [FK]<br/>- variant_id [FK]<br/>- quantity<br/>- unit_price (Historical Stamped)<br/>- discount_amount<br/>- line_total"]
        
        PAYMENTS["PAYMENTS (19)<br/>- payment_id [PK]<br/>- sale_id [FK]<br/>- payment_method (KHQR/Cash/Card)<br/>- amount<br/>- transaction_reference<br/>- status"]
    end

    POS_SHIFTS -.-> SALE_HEADERS
    PROMOTIONS --> SALE_HEADERS
    GIFT_CARDS -.-> PAYMENTS
    SALE_HEADERS --> SALE_DETAILS
    SALE_HEADERS --> PAYMENTS
```

---

### 2.3 Domain 3: Omnichannel Branches, Logistics & Customer CRM (6 Tables)

```mermaid
flowchart TB
    subgraph D3["Domain 3: Branches, CRM & Shipping Logistics (6 Tables)"]
        STORE_BRANCHES["STORE_BRANCHES (20)<br/>- branch_id [PK]<br/>- branch_name<br/>- branch_code<br/>- address<br/>- phone"]
        
        STORE_INVENTORIES["STORE_INVENTORIES (21)<br/>- inventory_id [PK]<br/>- branch_id [FK]<br/>- variant_id [FK]<br/>- quantity<br/>- reorder_point"]
        
        CUSTOMERS["CUSTOMERS (22)<br/>- customer_id [PK]<br/>- customer_name<br/>- phone<br/>- email<br/>- customer_type<br/>- loyalty_tier<br/>- loyalty_points"]
        
        CUSTOMER_LOYALTY_LOGS["CUSTOMER_LOYALTY_LOGS (23)<br/>- log_id [PK]<br/>- customer_id [FK]<br/>- points_change<br/>- balance_after<br/>- reason"]
        
        CUSTOMER_WISHLISTS["CUSTOMER_WISHLISTS (24)<br/>- wishlist_id [PK]<br/>- customer_id [FK/Nullable]<br/>- product_id [FK]<br/>- session_id"]
        
        SHIPPING_ORDERS["SHIPPING_ORDERS (25)<br/>- shipping_id [PK]<br/>- sale_id [FK]<br/>- recipient_name<br/>- shipping_address<br/>- courier_service<br/>- tracking_number<br/>- status"]
    end

    STORE_BRANCHES --> STORE_INVENTORIES
    CUSTOMERS --> CUSTOMER_LOYALTY_LOGS
    CUSTOMERS --> CUSTOMER_WISHLISTS
    CUSTOMERS --> SHIPPING_ORDERS
```

---

### 2.4 Domain 4: Purchasing, Suppliers & Stock Movement Ledger (4 Tables)

```mermaid
flowchart TB
    subgraph D4["Domain 4: Purchasing, Suppliers & Inventory Ledger (4 Tables)"]
        SUPPLIERS["SUPPLIERS (26)<br/>- supplier_id [PK]<br/>- supplier_name<br/>- contact_name<br/>- phone<br/>- email<br/>- address"]
        
        PURCHASE_HEADERS["PURCHASE_HEADERS (27)<br/>- purchase_id [PK]<br/>- po_number<br/>- supplier_id [FK]<br/>- employee_id [FK]<br/>- total_amount<br/>- status"]
        
        PURCHASE_DETAILS["PURCHASE_DETAILS (28)<br/>- detail_id [PK]<br/>- purchase_id [FK]<br/>- variant_id [FK]<br/>- quantity<br/>- cost_price<br/>- subtotal"]
        
        STOCK_MOVEMENTS["STOCK_MOVEMENTS (29)<br/>- movement_id [PK]<br/>- variant_id [FK]<br/>- branch_id [FK]<br/>- movement_type (SALE/PURCHASE/ADJUST)<br/>- quantity_delta<br/>- balance_before<br/>- balance_after<br/>- reference_id"]
    end

    SUPPLIERS --> PURCHASE_HEADERS
    PURCHASE_HEADERS --> PURCHASE_DETAILS
    PURCHASE_DETAILS -.-> STOCK_MOVEMENTS
```

---

### 2.5 Domain 5: Identity, Spatie RBAC & Staff Workforce (9 Tables)

```mermaid
flowchart TB
    subgraph D5["Domain 5: Identity, RBAC & Staff Workforce (9 Tables)"]
        USERS["USERS (30)<br/>- id [PK]<br/>- name<br/>- email<br/>- password<br/>- is_admin"]
        
        EMPLOYEES["EMPLOYEES (31)<br/>- employee_id [PK]<br/>- user_id [FK]<br/>- branch_id [FK]<br/>- first_name<br/>- last_name<br/>- position<br/>- salary<br/>- hire_date"]
        
        ROLES["ROLES (32)<br/>- id [PK]<br/>- name (ADMIN/MANAGER/CASHIER/STAFF)<br/>- guard_name"]
        
        PERMISSIONS["PERMISSIONS (33)<br/>- id [PK]<br/>- name<br/>- guard_name"]
        
        MODEL_HAS_ROLES["MODEL_HAS_ROLES (34)<br/>- role_id [FK]<br/>- model_type<br/>- model_id [FK]"]
        
        MODEL_HAS_PERMISSIONS["MODEL_HAS_PERMISSIONS (35)<br/>- permission_id [FK]<br/>- model_type<br/>- model_id [FK]"]
        
        ROLE_HAS_PERMISSIONS["ROLE_HAS_PERMISSIONS (36)<br/>- permission_id [FK]<br/>- role_id [FK]"]
        
        PERSONAL_ACCESS_TOKENS["PERSONAL_ACCESS_TOKENS (37)<br/>- id [PK]<br/>- tokenable_id [FK]<br/>- tokenable_type<br/>- name<br/>- token<br/>- abilities"]
        
        PASSWORD_RESET_TOKENS["PASSWORD_RESET_TOKENS (38)<br/>- email [PK]<br/>- token<br/>- created_at"]
    end

    USERS --> EMPLOYEES
    USERS --> MODEL_HAS_ROLES
    ROLES --> MODEL_HAS_ROLES
    ROLES --> ROLE_HAS_PERMISSIONS
    PERMISSIONS --> ROLE_HAS_PERMISSIONS
    USERS --> PERSONAL_ACCESS_TOKENS
```

---

### 2.6 Domain 6: Observability, Telemetry & Infrastructure Queues (10 Tables)

```mermaid
flowchart TB
    subgraph D6["Domain 6: Observability, Telemetry & Infrastructure (10 Tables)"]
        AUDIT_LOGS["AUDIT_LOGS (39)<br/>- audit_id [PK]<br/>- user_id [FK/Nullable]<br/>- action (CREATE/UPDATE/DELETE/VOID)<br/>- entity<br/>- entity_id<br/>- old_values [JSON]<br/>- new_values [JSON]<br/>- ip_address<br/>- user_agent"]
        
        API_LOGS["API_LOGS (40)<br/>- id [PK]<br/>- user_id [Nullable]<br/>- method<br/>- path<br/>- status<br/>- duration_ms<br/>- response_size<br/>- ip"]
        
        SYSTEM_BROADCAST_ALERTS["SYSTEM_BROADCAST_ALERTS (41)<br/>- alert_id [PK]<br/>- user_id [FK]<br/>- title<br/>- message<br/>- severity<br/>- is_active"]
        
        SESSIONS["SESSIONS (42)<br/>- id [PK]<br/>- user_id [Nullable]<br/>- ip_address<br/>- user_agent<br/>- payload<br/>- last_activity"]
        
        CACHE["CACHE (43)<br/>- key [PK]<br/>- value<br/>- expiration"]
        
        CACHE_LOCKS["CACHE_LOCKS (44)<br/>- key [PK]<br/>- owner<br/>- expiration"]
        
        JOBS["JOBS (45)<br/>- id [PK]<br/>- queue<br/>- payload<br/>- attempts<br/>- reserved_at<br/>- available_at"]
        
        JOB_BATCHES["JOB_BATCHES (46)<br/>- id [PK]<br/>- name<br/>- total_jobs<br/>- pending_jobs<br/>- failed_jobs<br/>- created_at"]
        
        FAILED_JOBS["FAILED_JOBS (47)<br/>- id [PK]<br/>- uuid<br/>- connection<br/>- queue<br/>- payload<br/>- exception<br/>- failed_at"]
        
        MIGRATIONS["MIGRATIONS (48)<br/>- id [PK]<br/>- migration<br/>- batch"]
    end

    USERS -.-> AUDIT_LOGS
    USERS -.-> API_LOGS
    USERS --> SYSTEM_BROADCAST_ALERTS
    JOBS --> FAILED_JOBS
    JOB_BATCHES -.-> JOBS
```

---

## 3. Comprehensive 48-Table Reference Matrix

| # | Table Name | Primary Key | Key Foreign Keys | Primary Function |
| :--- | :--- | :--- | :--- | :--- |
| **1** | `categories` | `category_id` | — | Product classification hierarchy & department tagging |
| **2** | `brands` | `brand_id` | — | Fashion labels, manufacturer origins & brand descriptions |
| **3** | `clothing_sizes` | `size_id` | — | Standardized size codes (S, M, L, XL, XXL, OS) & sort orders |
| **4** | `colors` | `color_id` | — | Color palettes & CSS hex codes for UI colorways |
| **5** | `products` | `product_id` | `category_id`, `brand_id` | Master catalog header holding base price and product descriptors |
| **6** | `product_variants` | `variant_id` | `product_id`, `size_id`, `color_id` | Atomic SKU matrix intersection holding unit price & barcode |
| **7** | `product_images` | `image_id` | `product_id` | Cloudinary CDN multi-image product gallery |
| **8** | `variant_pricing_tiers` | `tier_id` | `variant_id` | Wholesale volume pricing discounts (10+ pcs, 50+ pcs) |
| **9** | `product_batches` | `batch_id` | `variant_id` | FMCG FIFO inventory batches with manufacture & expiry dates |
| **10** | `product_reviews` | `review_id` | `product_id` | Customer ratings (1-5 stars) and qualitative feedback |
| **11** | `product_bundles` | `bundle_id` | — | Combo package definitions & gift set headers |
| **12** | `bundle_items` | `item_id` | `bundle_id`, `variant_id` | Line items and quantities comprising a bundle package |
| **13** | `pos_shifts` | `shift_id` | `employee_id`, `branch_id` | Cash register float sessions, drops & closing Z-reports |
| **14** | `promotions` | `promo_id` | — | Promotional coupon campaigns, discounts, and flash sales |
| **15** | `marketing_banners` | `banner_id` | — | Storefront hero carousel banners & seasonal promotions |
| **16** | `gift_cards` | `card_id` | `customer_id` | Digital stored-value gift cards & balance verification |
| **17** | `sale_headers` | `sale_id` | `branch_id`, `employee_id`, `customer_id` | Immutable financial sales invoice (10% VAT tax-exclusive) |
| **18** | `sale_details` | `detail_id` | `sale_id`, `variant_id` | Stamped line item record preserving historical price at sale |
| **19** | `payments` | `payment_id` | `sale_id` | Multi-tender payment records (Cash USD, ABA KHQR, Card) |
| **20** | `store_branches` | `branch_id` | — | Physical retail stores, flagships, and warehouse outlets |
| **21** | `store_inventories` | `inventory_id` | `branch_id`, `variant_id` | Isolated stock quantities segregated per physical branch |
| **22** | `customers` | `customer_id` | — | Client profile directory, loyalty tiers & phone search index |
| **23** | `customer_loyalty_logs` | `log_id` | `customer_id` | Ledger tracking loyalty point accrual and redemptions |
| **24** | `customer_wishlists` | `wishlist_id` | `product_id`, `customer_id` | Saved favorites for registered users and guest sessions |
| **25** | `shipping_orders` | `shipping_id` | `sale_id` | Delivery tracking, courier dispatches, and click-and-collect |
| **26** | `suppliers` | `supplier_id` | — | Wholesale vendor profiles, lead times, and contact information |
| **27** | `purchase_headers` | `purchase_id` | `supplier_id`, `employee_id` | Vendor procurement orders and inbound receiving receipts |
| **28** | `purchase_details` | `detail_id` | `purchase_id`, `variant_id` | Inbound items, negotiated unit cost prices & quantities |
| **29** | `stock_movements` | `movement_id` | `variant_id`, `branch_id` | Immutable audit log of all stock increases & deductions |
| **30** | `users` | `id` | — | System authentication accounts with bcrypt passwords |
| **31** | `employees` | `employee_id` | `user_id`, `branch_id` | Staff HR records, positions, salaries, and branch assignments |
| **32** | `roles` | `id` | — | Spatie RBAC security roles (`ADMIN`, `MANAGER`, `CASHIER`) |
| **33** | `permissions` | `id` | — | Fine-grained API endpoint security capabilities |
| **34** | `model_has_roles` | Composite | `role_id`, `model_id` | Polymorphic relationship assigning roles to users/staff |
| **35** | `model_has_permissions` | Composite | `permission_id`, `model_id` | Direct permission overrides for individual users |
| **36** | `role_has_permissions` | Composite | `permission_id`, `role_id` | Matrix linking permissions to predefined RBAC roles |
| **37** | `personal_access_tokens`| `id` | `tokenable_id` | Laravel Sanctum SHA-256 hashed Bearer API tokens |
| **38** | `password_reset_tokens` | `email` | — | One-time recovery tokens for user password resets |
| **39** | `audit_logs` | `audit_id` | `user_id` | Immutable JSON diffs (`old_values`, `new_values`) & IP audit |
| **40** | `api_logs` | `id` | `user_id` | HTTP request telemetry: latency (ms), status, payload size |
| **41** | `system_broadcast_alerts`| `alert_id` | `user_id` | Push broadcast notification banner messages to active staff |
| **42** | `sessions` | `id` | `user_id` | Web session state payloads and active user tracking |
| **43** | `cache` | `key` | — | High-performance application caching store |
| **44** | `cache_locks` | `key` | — | Atomic locking mechanisms for concurrent transactions |
| **45** | `jobs` | `id` | — | Asynchronous background processing queue |
| **46** | `job_batches` | `id` | — | Distributed queue batch monitoring & execution tracking |
| **47** | `failed_jobs` | `id` | — | Dead-letter queue capturing unhandled async exceptions |
| **48** | `migrations` | `id` | — | Database schema version history and migration audit log |

---

## 4. Tax Calculation Standard

The system enforces a **10.00% Tax-Exclusive Value Added Tax (VAT)** formula:

$$\text{Net Amount} = \text{Subtotal} - \text{Discount Amount}$$

$$\text{Tax Amount (10\% VAT)} = \text{Round}(\text{Net Amount} \times 0.10, 2)$$

$$\text{Grand Total} = \text{Net Amount} + \text{Tax Amount}$$

> [!IMPORTANT]
> **Historical Unit Price Rule**: Unit prices in `sale_details` are permanently stamped at the moment of checkout and are never altered by subsequent updates to product catalog master prices.
