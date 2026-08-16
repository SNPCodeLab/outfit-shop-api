# Store Stock and Point-of-Sale MIS API

RESTful API backend for retail clothing store inventory, supplier purchasing, and point-of-sale operations.

---

## Tech Stack

- Backend: Laravel 11 / PHP 8.3
- Database: PostgreSQL (Neon DB / Local PostgreSQL)
- Authentication: Laravel Sanctum Bearer Tokens
- Deployment: Serverless Gateway (Vercel)

---

## Database Architecture

### 1. Catalog and Inventory Domain
```mermaid
erDiagram
    CATEGORIES ||--o{ PRODUCTS : "contains"
    PRODUCTS ||--|{ PRODUCT_VARIANTS : "has variants"
    CLOTHING_SIZES ||--o{ PRODUCT_VARIANTS : "defines size"
    COLORS ||--o{ PRODUCT_VARIANTS : "defines color"
    PRODUCT_VARIANTS ||--o{ STOCK_MOVEMENTS : "tracks changes"

    CATEGORIES {
        bigint id PK
        string category_name
        text description
    }

    CLOTHING_SIZES {
        bigint id PK
        string size_code
        string size_name
        integer sort_order
    }

    COLORS {
        bigint id PK
        string color_name
        string hex_code
    }

    PRODUCTS {
        bigint id PK
        bigint category_id FK
        string product_name
        string brand
        text description
        boolean is_active
    }

    PRODUCT_VARIANTS {
        bigint id PK
        bigint product_id FK
        bigint size_id FK
        bigint color_id FK
        string sku
        string barcode
        decimal cost_price
        decimal sale_price
        integer quantity
        integer reorder_level
        boolean is_active
    }

    STOCK_MOVEMENTS {
        bigint id PK
        bigint variant_id FK
        bigint employee_id FK
        string movement_type
        integer quantity
        integer previous_qty
        integer new_qty
        string reference_type
        bigint reference_id
        text reason
    }
```

### 2. Point-of-Sale and Purchasing Domain
```mermaid
erDiagram
    SUPPLIERS ||--o{ PURCHASE_HEADERS : "supplies"
    EMPLOYEES ||--o{ PURCHASE_HEADERS : "creates PO"
    PURCHASE_HEADERS ||--|{ PURCHASE_DETAILS : "contains lines"
    PRODUCT_VARIANTS ||--o{ PURCHASE_DETAILS : "restocked in"

    CUSTOMERS ||--o{ SALE_HEADERS : "places"
    EMPLOYEES ||--o{ SALE_HEADERS : "processes"
    SALE_HEADERS ||--|{ SALE_DETAILS : "contains lines"
    PRODUCT_VARIANTS ||--o{ SALE_DETAILS : "sold in"
    SALE_HEADERS ||--|{ PAYMENTS : "settled with"

    SUPPLIERS {
        bigint id PK
        string supplier_name
        string contact_person
        string phone
        string email
        text address
        string status
    }

    CUSTOMERS {
        bigint id PK
        string customer_name
        string gender
        string phone
        string email
        text address
        integer total_points
    }

    PURCHASE_HEADERS {
        bigint id PK
        bigint supplier_id FK
        bigint employee_id FK
        string reference_number
        date purchase_date
        decimal total_amount
        string status
    }

    PURCHASE_DETAILS {
        bigint id PK
        bigint purchase_id FK
        bigint variant_id FK
        integer quantity
        decimal unit_cost
        decimal subtotal
    }

    SALE_HEADERS {
        bigint id PK
        string invoice_number
        bigint customer_id FK
        bigint employee_id FK
        timestamp sale_date
        decimal subtotal
        decimal discount_amount
        decimal tax_amount
        decimal total_amount
        string status
    }

    SALE_DETAILS {
        bigint id PK
        bigint sale_id FK
        bigint variant_id FK
        integer quantity
        decimal unit_price
        decimal discount_amount
        decimal subtotal
    }

    PAYMENTS {
        bigint id PK
        bigint sale_id FK
        string payment_method
        decimal amount_paid
        decimal change_amount
        string payment_status
        timestamp payment_date
    }
```

### 3. Administration and Audit Domain
```mermaid
erDiagram
    EMPLOYEES ||--o{ PURCHASE_HEADERS : "creates"
    EMPLOYEES ||--o{ SALE_HEADERS : "processes"
    EMPLOYEES ||--o{ STOCK_MOVEMENTS : "adjusts"
    USERS ||--o{ AUDIT_LOGS : "triggers"
    USERS ||--o{ PERSONAL_ACCESS_TOKENS : "owns"

    EMPLOYEES {
        bigint id PK
        string username
        string password_hash
        string employee_name
        string email
        string phone
        string gender
        string position
        string role
        decimal salary
        string status
    }

    USERS {
        bigint id PK
        string name
        string email
        string password
        boolean is_admin
    }

    PERSONAL_ACCESS_TOKENS {
        bigint id PK
        string tokenable_type
        bigint tokenable_id
        string name
        string token
        text abilities
    }

    AUDIT_LOGS {
        bigint id PK
        bigint user_id FK
        string action
        string entity_type
        bigint entity_id
        json old_values
        json new_values
        string ip_address
        text user_agent
    }
```

---

## Role-Based Access Control

| Role | Access Level | Description |
|---|---|---|
| Public / Guest | Level 1 | Read-only catalog, sizes, colors, and stock lookup |
| Cashier | Level 2 | POS checkout, customer management, invoice viewing |
| Manager | Level 3 | Catalog CRUD, suppliers, purchase orders, stock adjustment, dashboard |
| Admin | Level 4 | Employee administration, developer accounts, audit logs |

---

## Core Endpoints

### Public (Level 0 and 1)
- GET / - API route index
- GET /api/v1/health - System and database status
- POST /api/v1/auth/login - User authentication
- GET /api/v1/categories - List product categories
- GET /api/v1/products - List products with variant info
- GET /api/v1/variants - List inventory variants
- GET /api/v1/variants/barcode/{barcode} - Scan barcode lookup

### Cashier (Level 2)
- GET /api/v1/auth/me - Authenticated user profile
- POST /api/v1/auth/logout - Invalidate current token
- GET /api/v1/customers - Search customer records
- POST /api/v1/customers - Register new customer
- POST /api/v1/sales/checkout - Process sale transaction
- GET /api/v1/sales - List sales receipts

### Manager (Level 3)
- GET /api/v1/dashboard/stats - Sales and request metrics
- POST, PUT, DELETE /api/v1/products - Manage products
- POST, PUT, DELETE /api/v1/variants - Manage SKU variants
- POST, PUT, DELETE /api/v1/categories - Manage categories
- GET, POST, PUT /api/v1/suppliers - Manage suppliers
- GET, POST /api/v1/purchases - Purchase orders and restock
- GET, POST /api/v1/stock-movements - Inventory tracking and manual adjustment
- POST /api/v1/sales/{id}/void - Void sale transaction

### Admin (Level 4)
- GET, POST, PUT, DELETE /api/v1/employees - Manage employee accounts
- POST /api/v1/auth/register - Register developer access

---

## Error Response Format

All error responses follow standard REST API schema with Khmer localization text:

```json
{
  "message": "Custom message in Khmer",
  "status": "401"
}
```

Common status codes:
- 401: Unauthorized (missing or invalid Bearer token)
- 403: Forbidden (insufficient role permissions)
- 404: Not Found (resource does not exist)
- 422: Unprocessable Content (validation failure)
- 429: Too Many Requests (rate limit exceeded)
- 500: Internal Server Error

---

## Installation and Local Setup

1. Clone repository and install dependencies:
```bash
composer install
```

2. Copy environment file and generate application key:
```bash
cp .env.example .env
php artisan key:generate
```

3. Configure database settings in .env:
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=ssmis_db
DB_USERNAME=postgres
DB_PASSWORD=your_password
```

4. Run migrations and seed default data:
```bash
php artisan migrate --seed
```

5. Start local development server:
```bash
php artisan serve
```
