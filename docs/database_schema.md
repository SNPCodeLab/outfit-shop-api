# SS-MIS Database Schema & Entity-Relationship (ER) Diagram

Authoritative reference for the database tables, fields, constraints, and relationships for the Store Stock & Point-of-Sale Information System (SS-MIS).

## Mermaid Entity-Relationship Diagram

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
