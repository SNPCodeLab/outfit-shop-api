# Store Stock and Point-of-Sale Information System (SS-MIS) API

RESTful API backend for retail clothing store inventory, supplier purchasing, POS checkout, and role-based administrative control.

---

## 1. System Classification

IS Type: Transaction Processing System (TPS / OLTP) with embedded Management Information System (MIS) Reporting  
Architecture: Monolithic, Headless REST API Backend  
Access Model: 4-Tier Role-Based Access Control (Admin, Manager, Cashier, Staff)

SS-MIS processes routine retail transactions in real-time (POS checkout, stock deduction, purchase receiving) qualifying it as a TPS/OLTP system. It also exposes structured management analytics (staff timesheets, financial waterfall, low-stock forecasts, audit logs), functioning as an MIS control layer.

---

## 2. Complete Database Entity Mindmap Diagram

```mermaid
flowchart TB
    root["SS-MIS Database Architecture"]

    subgraph D1["Catalog and Product Domain"]
        CATEGORIES["CATEGORIES<br/>- category_id PK<br/>- category_name<br/>- department_type<br/>- description"]
        BRANDS["BRANDS<br/>- brand_id PK<br/>- brand_name<br/>- country_of_origin<br/>- description"]
        PRODUCTS["PRODUCTS<br/>- product_id PK<br/>- category_id FK<br/>- brand_id FK<br/>- product_name<br/>- product_type<br/>- gender<br/>- material_fabric<br/>- season_collection<br/>- featured_badge<br/>- status"]
        PRODUCT_IMAGES["PRODUCT_IMAGES<br/>- image_id PK<br/>- product_id FK<br/>- image_url<br/>- is_primary<br/>- sort_order"]
        CLOTHING_SIZES["CLOTHING_SIZES<br/>- size_id PK<br/>- size_name<br/>- description"]
        COLORS["COLORS<br/>- color_id PK<br/>- color_name<br/>- description"]
        PRODUCT_VARIANTS["PRODUCT_VARIANTS<br/>- variant_id PK<br/>- product_id FK<br/>- size_id FK<br/>- color_id FK<br/>- sku<br/>- barcode<br/>- cost_price<br/>- sale_price<br/>- quantity<br/>- volume_or_weight"]
    end

    subgraph D2["Sales and POS Domain"]
        STORE_BRANCHES["STORE_BRANCHES<br/>- branch_id PK<br/>- branch_name<br/>- branch_code<br/>- address"]
        CUSTOMERS["CUSTOMERS<br/>- customer_id PK<br/>- customer_name<br/>- phone<br/>- customer_type<br/>- loyalty_points"]
        POS_SHIFTS["POS_SHIFTS<br/>- shift_id PK<br/>- employee_id FK<br/>- branch_id FK<br/>- opening_float_usd<br/>- status"]
        SALE_HEADERS["SALE_HEADERS<br/>- sale_id PK<br/>- branch_id FK<br/>- employee_id FK<br/>- customer_id FK<br/>- total_amount<br/>- tax_rate (10%)<br/>- tax_amount<br/>- grand_total"]
        SALE_DETAILS["SALE_DETAILS<br/>- detail_id PK<br/>- sale_id FK<br/>- variant_id FK<br/>- quantity<br/>- unit_price<br/>- line_total"]
        PAYMENTS["PAYMENTS<br/>- payment_id PK<br/>- sale_id FK<br/>- payment_method<br/>- amount<br/>- status"]
    end

    subgraph D3["Purchasing and Inventory Domain"]
        SUPPLIERS["SUPPLIERS<br/>- supplier_id PK<br/>- supplier_name<br/>- phone<br/>- address"]
        PURCHASE_HEADERS["PURCHASE_HEADERS<br/>- purchase_id PK<br/>- supplier_id FK<br/>- employee_id FK<br/>- total_amount<br/>- status"]
        PURCHASE_DETAILS["PURCHASE_DETAILS<br/>- detail_id PK<br/>- purchase_id FK<br/>- variant_id FK<br/>- quantity<br/>- cost_price"]
        STOCK_MOVEMENTS["STOCK_MOVEMENTS<br/>- movement_id PK<br/>- variant_id FK<br/>- movement_type<br/>- quantity<br/>- new_quantity"]
    end

    subgraph D4["Administration and Security Domain"]
        USERS["USERS<br/>- id PK<br/>- name<br/>- email"]
        ROLES["ROLES<br/>- id PK<br/>- name<br/>- guard_name"]
        PERMISSIONS["PERMISSIONS<br/>- id PK<br/>- name<br/>- guard_name"]
        EMPLOYEES["EMPLOYEES<br/>- employee_id PK<br/>- branch_id FK<br/>- employee_name<br/>- position<br/>- status"]
        SYSTEM_BROADCAST_ALERTS["SYSTEM_BROADCAST_ALERTS<br/>- alert_id PK<br/>- title<br/>- message<br/>- priority<br/>- target_role"]
        AUDIT_LOGS["AUDIT_LOGS<br/>- log_id PK<br/>- user_id FK<br/>- action<br/>- table_name<br/>- ip_address"]
    end

    root --> D1
    root --> D2
    root --> D3
    root --> D4

    CATEGORIES --> PRODUCTS
    BRANDS --> PRODUCTS
    PRODUCTS --> PRODUCT_VARIANTS
    PRODUCTS --> PRODUCT_IMAGES
    CLOTHING_SIZES --> PRODUCT_VARIANTS
    COLORS --> PRODUCT_VARIANTS

    STORE_BRANCHES --> SALE_HEADERS
    CUSTOMERS --> SALE_HEADERS
    EMPLOYEES --> SALE_HEADERS
    SALE_HEADERS --> SALE_DETAILS
    SALE_HEADERS --> PAYMENTS
    PRODUCT_VARIANTS --> SALE_DETAILS

    SUPPLIERS --> PURCHASE_HEADERS
    EMPLOYEES --> PURCHASE_HEADERS
    PURCHASE_HEADERS --> PURCHASE_DETAILS
    PRODUCT_VARIANTS --> PURCHASE_DETAILS
    PRODUCT_VARIANTS --> STOCK_MOVEMENTS

    USERS --> ROLES
    ROLES --> PERMISSIONS
    EMPLOYEES --> POS_SHIFTS
```

---

## 3. Entity Domain Breakdown

### 3.1 Catalog and Product Domain
The foundation of merchandise categorization, luxury sizing, color palettes, and stockable Stock Keeping Units (SKUs).

- Categories: Organizes physical apparel and FMCG merchandise into Tops, Dresses, Pants, Jackets, Skirts, Polos, Bags, Shoes, and Beer.
- Brands: Tracks manufacturers and luxury fashion labels.
- Products: Master item records holding high-level product descriptions, fabric composition, and season collections.
- Clothing Sizes: Standardized luxury apparel sizes (S, M, L, XL, OS).
- Colors: Luxury palette definitions with hex color representations (Black, White, Gold).
- Product Variants: Atomic matrix intersections of Product, Size, and Color holding exact inventory counts, costs, retail prices, and barcodes.
- Product Images: CDN photo gallery records associated with product headers.

### 3.2 Sales and Point-of-Sale Domain
Manages real-time cash register shifts, retail transactions, customer loyalty balances, and payment processing.

- Store Branches: Retail store and warehouse facility records.
- Customers: Walk-in and registered VIP clientele tracking accumulated loyalty points and credit accounts.
- POS Shifts: Daily cash drawer reconciliation, opening cash float, safe drops, and closing Z-reports.
- Sale Headers: Immutable financial invoice master records calculating Net Amount, 10 percent VAT Tax Amount, and Grand Total.
- Sale Details: Individual line items preserving the historical unit sale price at the exact second of checkout.
- Payments: Multi-tender payment transactions (Cash USD, Cash KHR, ABA KHQR, Card).

### 3.3 Purchasing and Inventory Domain
Controls warehouse replenishment, vendor purchase orders, and stock audit ledgers.

- Suppliers: Master records of fabric mills, apparel distributors, and beverage suppliers.
- Purchase Headers and Details: Procurement transactions tracking cost prices, order quantities, and delivery statuses.
- Stock Movements: Append-only audit ledger tracking every stock addition (Purchase Receipt, Adjustment In) and deduction (POS Sale, Adjustment Out).

### 3.4 Administration and Security Domain
Manages role-based authentication, staff timesheets, emergency broadcast announcements, and audit inspection.

- Users, Roles, and Permissions: Granular access control defining permissions across 4 user hierarchy levels.
- Employees: Staff profiles assigned to specific store branches and job positions.
- System Broadcast Alerts: Real-time broadcast messages dispatched by Admin to all users or specific roles.
- Audit Logs: Immutable system activity trail recording payload changes, timestamps, and IP addresses.

---

## 4. Tax Calculation Standard

The system enforces a 10.00 percent Tax-Exclusive Value Added Tax (VAT) formula:

1. Net Amount = Total Amount - Overall Discount
2. Tax Amount (10 percent VAT) = Round(Net Amount * 0.10, 2)
3. Grand Total = Net Amount + Tax Amount

Historical Unit Price Rule: Unit prices stored in Sale Details are stamped permanently at checkout from Product Variants and are never modified by subsequent price changes in the product catalog.
