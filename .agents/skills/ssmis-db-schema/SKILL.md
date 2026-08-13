---
name: ssmis-db-schema
description: Authoritative ER diagram and database schema definition for Store Stock & Point-of-Sale Information System (SS-MIS). Defines tables for CATEGORY, PRODUCT, CLOTHING_SIZE, COLOR, PRODUCT_VARIANT, SUPPLIER, EMPLOYEE, CUSTOMER, PURCHASE_HEADER, PURCHASE_DETAIL, SALE_HEADER, SALE_DETAIL, PAYMENT, and STOCK_MOVEMENT along with primary keys, foreign keys, constraints, and relationships. Use this skill whenever writing, designing, migrating, or querying database models, tables, relationships, or API endpoints for SS-MIS.
---

# SS-MIS Database Schema & Entity-Relationship (ER) Diagram

Authoritative reference for the database tables, fields, constraints, and relationships for the Store Stock & Point-of-Sale Information System (SS-MIS).

## Entity-Relationship Diagram

```mermaid
erDiagram

    CATEGORY ||--o{ PRODUCT : contains
    PRODUCT ||--o{ PRODUCT_VARIANT : has
    CLOTHING_SIZE ||--o{ PRODUCT_VARIANT : defines
    COLOR ||--o{ PRODUCT_VARIANT : defines

    SUPPLIER ||--o{ PURCHASE_HEADER : supplies
    EMPLOYEE ||--o{ PURCHASE_HEADER : creates
    PURCHASE_HEADER ||--|{ PURCHASE_DETAIL : contains
    PRODUCT_VARIANT ||--o{ PURCHASE_DETAIL : purchased

    CUSTOMER ||--o{ SALE_HEADER : makes
    EMPLOYEE ||--o{ SALE_HEADER : processes
    SALE_HEADER ||--|{ SALE_DETAIL : contains
    PRODUCT_VARIANT ||--o{ SALE_DETAIL : sold

    SALE_HEADER ||--o{ PAYMENT : receives

    PRODUCT_VARIANT ||--o{ STOCK_MOVEMENT : tracks


    CATEGORY {
        NUMBER category_id PK
        VARCHAR2 category_name UK
        VARCHAR2 description
    }

    PRODUCT {
        NUMBER product_id PK
        NUMBER category_id FK
        VARCHAR2 product_name
        VARCHAR2 brand
        VARCHAR2 description
        VARCHAR2 status
        TIMESTAMP created_at
    }

    CLOTHING_SIZE {
        NUMBER size_id PK
        VARCHAR2 size_name UK
        VARCHAR2 description
    }

    COLOR {
        NUMBER color_id PK
        VARCHAR2 color_name UK
        VARCHAR2 description
    }

    PRODUCT_VARIANT {
        NUMBER variant_id PK
        NUMBER product_id FK
        NUMBER size_id FK
        NUMBER color_id FK
        VARCHAR2 sku UK
        NUMBER cost_price
        NUMBER sale_price
        NUMBER quantity
        NUMBER reorder_level
    }

    SUPPLIER {
        NUMBER supplier_id PK
        VARCHAR2 supplier_name
        VARCHAR2 phone
        VARCHAR2 email
        VARCHAR2 address
        VARCHAR2 status
    }

    EMPLOYEE {
        NUMBER employee_id PK
        VARCHAR2 employee_name
        VARCHAR2 gender
        VARCHAR2 phone
        VARCHAR2 email
        VARCHAR2 position
        VARCHAR2 username UK
        VARCHAR2 password_hash
        VARCHAR2 status
    }

    CUSTOMER {
        NUMBER customer_id PK
        VARCHAR2 customer_name
        VARCHAR2 gender
        VARCHAR2 phone
        VARCHAR2 email
        VARCHAR2 address
    }

    PURCHASE_HEADER {
        NUMBER purchase_id PK
        NUMBER supplier_id FK
        NUMBER employee_id FK
        TIMESTAMP purchase_date
        NUMBER total_amount
        VARCHAR2 status
    }

    PURCHASE_DETAIL {
        NUMBER purchase_detail_id PK
        NUMBER purchase_id FK
        NUMBER variant_id FK
        NUMBER quantity
        NUMBER cost_price
        NUMBER sub_total
    }

    SALE_HEADER {
        NUMBER sale_id PK
        NUMBER customer_id FK
        NUMBER employee_id FK
        TIMESTAMP sale_date
        NUMBER total_amount
        NUMBER discount
        NUMBER grand_total
        VARCHAR2 status
    }

    SALE_DETAIL {
        NUMBER sale_detail_id PK
        NUMBER sale_id FK
        NUMBER variant_id FK
        NUMBER quantity
        NUMBER unit_price
        NUMBER discount
        NUMBER sub_total
    }

    PAYMENT {
        NUMBER payment_id PK
        NUMBER sale_id FK
        TIMESTAMP payment_date
        NUMBER amount
        VARCHAR2 payment_method
        VARCHAR2 payment_status
        VARCHAR2 reference_number
    }

    STOCK_MOVEMENT {
        NUMBER movement_id PK
        NUMBER variant_id FK
        VARCHAR2 movement_type
        NUMBER quantity
        TIMESTAMP movement_date
        VARCHAR2 reference_type
        NUMBER reference_id
        VARCHAR2 note
    }
```

## Entities Summary

1. **CATEGORY**: Product category classification (`category_id`, `category_name`, `description`).
2. **PRODUCT**: Base product catalog (`product_id`, `category_id`, `product_name`, `brand`, `description`, `status`, `created_at`).
3. **CLOTHING_SIZE**: Size master data (`size_id`, `size_name`, `description`).
4. **COLOR**: Color master data (`color_id`, `color_name`, `description`).
5. **PRODUCT_VARIANT**: Specific product SKU variant (`variant_id`, `product_id`, `size_id`, `color_id`, `sku`, `cost_price`, `sale_price`, `quantity`, `reorder_level`).
6. **SUPPLIER**: Supplier directory (`supplier_id`, `supplier_name`, `phone`, `email`, `address`, `status`).
7. **EMPLOYEE**: Employee & system user directory (`employee_id`, `employee_name`, `gender`, `phone`, `email`, `position`, `username`, `password_hash`, `status`).
8. **CUSTOMER**: Customer directory (`customer_id`, `customer_name`, `gender`, `phone`, `email`, `address`).
9. **PURCHASE_HEADER**: Supplier purchase orders (`purchase_id`, `supplier_id`, `employee_id`, `purchase_date`, `total_amount`, `status`).
10. **PURCHASE_DETAIL**: Items inside purchase order (`purchase_detail_id`, `purchase_id`, `variant_id`, `quantity`, `cost_price`, `sub_total`).
11. **SALE_HEADER**: POS sale transactions (`sale_id`, `customer_id`, `employee_id`, `sale_date`, `total_amount`, `discount`, `grand_total`, `status`).
12. **SALE_DETAIL**: Line items in sale transaction (`sale_detail_id`, `sale_id`, `variant_id`, `quantity`, `unit_price`, `discount`, `sub_total`).
13. **PAYMENT**: Payment transaction details (`payment_id`, `sale_id`, `payment_date`, `amount`, `payment_method`, `payment_status`, `reference_number`).
14. **STOCK_MOVEMENT**: Inventory audit log (`movement_id`, `variant_id`, `movement_type`, `quantity`, `movement_date`, `reference_type`, `reference_id`, `note`).
