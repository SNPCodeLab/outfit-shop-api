# SS-MIS Database Schema & Entity-Relationship (ER) Diagram (Standard Enterprise SQL)

Authoritative reference for the database tables, data types, constraints, and relationships for the Store Stock & Point-of-Sale Information System (SS-MIS).

Designed according to **worldwide enterprise SQL standards**, incorporating:
- Standard ANSI SQL types (`DECIMAL(12,2)`, `BIGINT`, `VARCHAR`, `TIMESTAMPTZ`, `BOOLEAN`).
- Financial accuracy for POS transactions using exact fixed-point decimal arithmetic.
- Soft delete support (`deleted_at`) for audit compliance and historical integrity.
- Multi-store / multi-branch readiness (`store_id`).
- Barcode / EAN scanner integration (`barcode`).
- Comprehensive audit timestamps (`created_at`, `updated_at`, `deleted_at`).
- Detailed stock movements with pre/post balance audit trail (`stock_before`, `stock_after`).

## Entity-Relationship Diagram (Mermaid)

```mermaid
erDiagram

    STORE ||--o{ EMPLOYEE : employs
    STORE ||--o{ STOCK_MOVEMENT : holds

    CATEGORY ||--o{ PRODUCT : categorizes
    PRODUCT ||--o{ PRODUCT_VARIANT : has
    CLOTHING_SIZE ||--o{ PRODUCT_VARIANT : sizes
    COLOR ||--o{ PRODUCT_VARIANT : colors

    SUPPLIER ||--o{ PURCHASE_HEADER : supplies
    EMPLOYEE ||--o{ PURCHASE_HEADER : creates
    PURCHASE_HEADER ||--|{ PURCHASE_DETAIL : contains
    PRODUCT_VARIANT ||--o{ PURCHASE_DETAIL : purchased

    CUSTOMER ||--o{ SALE_HEADER : places
    EMPLOYEE ||--o{ SALE_HEADER : processes
    SALE_HEADER ||--|{ SALE_DETAIL : contains
    PRODUCT_VARIANT ||--o{ SALE_DETAIL : sold

    SALE_HEADER ||--o{ PAYMENT : settles

    PRODUCT_VARIANT ||--o{ STOCK_MOVEMENT : tracks


    STORE {
        BIGINT store_id PK
        VARCHAR store_name
        VARCHAR code UK
        VARCHAR tax_id
        VARCHAR phone
        VARCHAR email
        TEXT address
        BOOLEAN is_active
        TIMESTAMP created_at
        TIMESTAMP updated_at
    }

    CATEGORY {
        BIGINT category_id PK
        VARCHAR category_name UK
        VARCHAR slug UK
        TEXT description
        BOOLEAN is_active
        TIMESTAMP created_at
        TIMESTAMP updated_at
        TIMESTAMP deleted_at
    }

    PRODUCT {
        BIGINT product_id PK
        BIGINT category_id FK
        VARCHAR product_name
        VARCHAR brand
        TEXT description
        VARCHAR status
        TIMESTAMP created_at
        TIMESTAMP updated_at
        TIMESTAMP deleted_at
    }

    CLOTHING_SIZE {
        BIGINT size_id PK
        VARCHAR size_code UK
        VARCHAR size_name
        TEXT description
    }

    COLOR {
        BIGINT color_id PK
        VARCHAR color_code UK
        VARCHAR color_name
        VARCHAR hex_code
    }

    PRODUCT_VARIANT {
        BIGINT variant_id PK
        BIGINT product_id FK
        BIGINT size_id FK
        BIGINT color_id FK
        VARCHAR sku UK
        VARCHAR barcode UK
        DECIMAL cost_price
        DECIMAL sale_price
        DECIMAL wholesale_price
        INT stock_quantity
        INT reorder_level
        BOOLEAN is_active
        TIMESTAMP created_at
        TIMESTAMP updated_at
        TIMESTAMP deleted_at
    }

    SUPPLIER {
        BIGINT supplier_id PK
        VARCHAR supplier_name
        VARCHAR contact_name
        VARCHAR phone
        VARCHAR email
        TEXT address
        VARCHAR tax_id
        VARCHAR status
        TIMESTAMP created_at
        TIMESTAMP updated_at
        TIMESTAMP deleted_at
    }

    EMPLOYEE {
        BIGINT employee_id PK
        BIGINT store_id FK
        VARCHAR employee_name
        VARCHAR gender
        VARCHAR phone
        VARCHAR email
        VARCHAR position
        VARCHAR username UK
        VARCHAR password_hash
        VARCHAR status
        TIMESTAMP created_at
        TIMESTAMP updated_at
        TIMESTAMP deleted_at
    }

    CUSTOMER {
        BIGINT customer_id PK
        VARCHAR customer_name
        VARCHAR gender
        VARCHAR phone UK
        VARCHAR email
        TEXT address
        INT loyalty_points
        TIMESTAMP created_at
        TIMESTAMP updated_at
        TIMESTAMP deleted_at
    }

    PURCHASE_HEADER {
        BIGINT purchase_id PK
        VARCHAR reference_no UK
        BIGINT supplier_id FK
        BIGINT employee_id FK
        BIGINT store_id FK
        TIMESTAMP purchase_date
        DECIMAL total_amount
        DECIMAL tax_amount
        DECIMAL grand_total
        VARCHAR status
        TEXT notes
        TIMESTAMP created_at
        TIMESTAMP updated_at
    }

    PURCHASE_DETAIL {
        BIGINT purchase_detail_id PK
        BIGINT purchase_id FK
        BIGINT variant_id FK
        INT quantity
        DECIMAL unit_cost
        DECIMAL sub_total
    }

    SALE_HEADER {
        BIGINT sale_id PK
        VARCHAR invoice_no UK
        BIGINT store_id FK
        BIGINT customer_id FK
        BIGINT employee_id FK
        TIMESTAMP sale_date
        DECIMAL sub_total
        DECIMAL discount_amount
        DECIMAL tax_amount
        DECIMAL grand_total
        VARCHAR payment_status
        VARCHAR status
        TEXT notes
        TIMESTAMP created_at
        TIMESTAMP updated_at
    }

    SALE_DETAIL {
        BIGINT sale_detail_id PK
        BIGINT sale_id FK
        BIGINT variant_id FK
        INT quantity
        DECIMAL unit_price
        DECIMAL discount_amount
        DECIMAL sub_total
    }

    PAYMENT {
        BIGINT payment_id PK
        BIGINT sale_id FK
        TIMESTAMP payment_date
        DECIMAL amount
        DECIMAL amount_tendered
        DECIMAL change_due
        VARCHAR payment_method
        VARCHAR payment_status
        VARCHAR transaction_ref
        TIMESTAMP created_at
    }

    STOCK_MOVEMENT {
        BIGINT movement_id PK
        BIGINT store_id FK
        BIGINT variant_id FK
        VARCHAR movement_type
        INT quantity
        INT stock_before
        INT stock_after
        TIMESTAMP movement_date
        VARCHAR reference_type
        BIGINT reference_id
        TEXT note
        BIGINT created_by FK
        TIMESTAMP created_at
    }
```

## Professional Database Design Highlights

| Domain Feature | Worldwide Standard Practice | Implementation in SS-MIS |
|---|---|---|
| **Monetary Values** | Fixed-point `DECIMAL(12,2)` | Prevents IEEE 754 floating point rounding errors in cash registers & receipts |
| **Audit Trails** | Timestamps & Audit Log | `created_at`, `updated_at`, `deleted_at` on all main entities |
| **Barcode Scanning** | Global EAN/UPC Code Indexing | `barcode` UNIQUE column on `PRODUCT_VARIANT` for instant POS optical scanner lookup |
| **Stock Integrity** | Balance Tracking | `stock_before` and `stock_after` on `STOCK_MOVEMENT` for immutable inventory audit logs |
| **Soft Deletes** | Regulatory Compliance | Soft deletes on Products, Customers, Employees, and Suppliers to preserve historical sales reports |
| **Multi-Store Scaling**| Multi-tenant / Multi-location | `store_id` foreign keys allow effortless expansion from single shop to multi-branch chains |
