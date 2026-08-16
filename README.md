# Store Stock & Point-of-Sale MIS API (SS-MIS)

Production Base URL: `https://api.kesararamwithdigital.tech`  
Repository: `https://github.com/SNPbuilds/csms-api`  
Framework: Laravel 11.55.1 / PHP 8.3 / PostgreSQL / Vercel Serverless  

---

## 1. System Overview

SS-MIS (Store Stock & Point-of-Sale Management Information System) is an enterprise-grade RESTful API Gateway designed for retail and fashion apparel operations. It provides role-based access control (RBAC), multi-tier catalog management (sizes, colors, SKU variants), automated inventory tracking, purchasing and supplier workflows, point-of-sale checkouts, traffic analytics, and administrative audit trails.

---

## 2. System Architecture

```text
Client Applications (Web / Mobile POS / Admin Portal)
                        │
                        ▼ (HTTPS / TLS 1.3)
      Vercel Serverless API Gateway (api/index.php)
                        │
        ┌───────────────┴───────────────┐
        ▼                               ▼
Public API Routes              Protected API Routes
(Catalog / Health / Login)    (Sanctum Bearer Token Auth)
                                        │
                         ┌──────────────┴──────────────┐
                         ▼                             ▼
                 Role Middleware               Admin Middleware
             (Cashier, Manager, Admin)           (Admin Only)
                         │                             │
                         └──────────────┬──────────────┘
                                        ▼
                        PostgreSQL Database Engine
                 (Neon / AWS RDS / Local PostgreSQL)
```

---

## 3. Role-Based Access Control (RBAC) Hierarchy

The system defines 4 access tiers:

| Tier | Role | Level | Accessible Modules | Default Credentials |
|---|---|---|---|---|
| Level 1 | `STAFF` / `GUEST` | Public | Read-only product catalog, price lookup, stock availability, barcode scanner. | No token required |
| Level 2 | `CASHIER` | Authenticated | POS sales checkout, customer registration and search, receipt generation. | `cashier` / `Cashier@123456` |
| Level 3 | `MANAGER` | Management | Full catalog write operations (CRUD), suppliers, purchase orders, inventory recount adjustments, sales void, dashboard metrics. | `manager` / `Manager@123456` |
| Level 4 | `ADMIN` | Super Admin | Full system control, HR employee administration (salaries, roles), developer registration, audit logs. | `admin` / `Admin@123456` |

---

## 4. API Endpoints Directory

### Level 0: System Discovery & Health (Public)
* `GET /` - API Gateway root directory and endpoint index.
* `GET /api/v1/health` - Health check endpoint (Database connectivity and system uptime).
* `GET /api/v1/status` - Live server status and runtime metrics.

### Level 1: Public Catalog & Authentication (Public)
* `POST /api/v1/auth/login` - Authenticate with username and password to receive Bearer token.
* `GET /api/v1/categories` - List all product categories.
* `GET /api/v1/categories/{id}` - Get category details with associated products and variants.
* `GET /api/v1/clothing-sizes` - List all standard clothing sizes.
* `GET /api/v1/clothing-sizes/{id}` - Get single clothing size detail.
* `GET /api/v1/colors` - List all clothing color codes and descriptions.
* `GET /api/v1/colors/{id}` - Get single color detail.
* `GET /api/v1/products` - Search and paginate active product catalog.
* `GET /api/v1/products/{id}` - Get product details, category, and SKU variants.
* `GET /api/v1/products/{id}/variants` - Get only size and color variants for a product.
* `GET /api/v1/variants` - List all inventory SKU variants.
* `GET /api/v1/variants/{id}` - Get single SKU variant detail.
* `GET /api/v1/variants/barcode/{barcode}` - Lookup product variant by barcode scanner value.
* `GET /api/v1/variants/low-stock` - List all inventory items below reorder threshold.

### Level 2: Cashier & POS Operations (Cashier, Manager, Admin)
* `GET /api/v1/auth/me` - Get profile and permissions of authenticated user.
* `POST /api/v1/auth/logout` - Revoke and destroy active session Bearer token.
* `GET /api/v1/customers` - List and search registered store customers.
* `GET /api/v1/customers/{id}` - View customer profile and previous purchase history.
* `POST /api/v1/customers` - Register a new customer at checkout.
* `PUT /api/v1/customers/{id}` - Update customer contact details.
* `POST /api/v1/sales/checkout` - Process sales checkout (deducts stock and records payment).
* `GET /api/v1/sales` - List all completed sales invoices.
* `GET /api/v1/sales/{id}` - View invoice receipt details and item line breakdown.

### Level 3: Store & Inventory Management (Manager, Admin)
* `GET /api/v1/dashboard/stats` - Traffic metrics, daily request volume, top endpoints.
* `POST /api/v1/categories` - Create new product category.
* `PUT /api/v1/categories/{id}` - Update category name and description.
* `DELETE /api/v1/categories/{id}` - Delete category.
* `POST /api/v1/clothing-sizes` - Create clothing size.
* `PUT /api/v1/clothing-sizes/{id}` - Update clothing size.
* `DELETE /api/v1/clothing-sizes/{id}` - Delete clothing size.
* `POST /api/v1/colors` - Create color option.
* `PUT /api/v1/colors/{id}` - Update color option.
* `DELETE /api/v1/colors/{id}` - Delete color option.
* `POST /api/v1/products` - Create product master record.
* `PUT /api/v1/products/{id}` - Update product master record.
* `DELETE /api/v1/products/{id}` - Soft-delete product.
* `POST /api/v1/variants` - Create SKU variant.
* `PUT /api/v1/variants/{id}` - Update variant price, barcode, quantity.
* `DELETE /api/v1/variants/{id}` - Delete variant.
* `GET /api/v1/suppliers` - List all suppliers.
* `GET /api/v1/suppliers/{id}` - View supplier details.
* `POST /api/v1/suppliers` - Register new supplier.
* `PUT /api/v1/suppliers/{id}` - Update supplier profile.
* `DELETE /api/v1/suppliers/{id}` - Remove supplier.
* `GET /api/v1/purchases` - List purchase orders and restock shipments.
* `GET /api/v1/purchases/{id}` - View purchase order details.
* `POST /api/v1/purchases` - Create purchase order (automatically increments stock).
* `GET /api/v1/stock-movements` - View inventory stock audit movement log.
* `POST /api/v1/stock-movements/adjust` - Manual inventory correction (damage, recount).
* `POST /api/v1/sales/{id}/void` - Void sale transaction and restore inventory.
* `GET /api/v1/audit-logs` - View system change audit trails.
* `GET /api/v1/audit-logs/{id}` - View specific audit record diff.

### Level 4: Super Administration (Admin Only)
* `GET /api/v1/employees` - List all employee accounts.
* `GET /api/v1/employees/{id}` - View single employee profile.
* `POST /api/v1/employees` - Create employee account (Cashier, Manager, Admin).
* `PUT /api/v1/employees/{id}` - Update employee position, salary, status, or role.
* `DELETE /api/v1/employees/{id}` - Deactivate or remove employee account.
* `POST /api/v1/auth/register` - Create new developer / admin portal user account.

---

## 5. Standard Error Handling Schema

All API error responses follow GitHub's REST API specification with clean Khmer status text:

### 401 Unauthorized
```json
{
  "message": "សូមអភ័យទោស លោកអ្នកត្រូវចូលប្រព័ន្ធ (Login) ជាមុនសិន ទើបអាចដំណើរការបាន",
  "documentation_url": "https://github.com/SNPbuilds/csms-api",
  "status": "401"
}
```

### 403 Forbidden
```json
{
  "message": "សូមអភ័យទោស គណនីរបស់លោកអ្នកមិនមានសិទ្ធិគ្រប់គ្រាន់ដើម្បីដំណើរការផ្នែកនេះទេ",
  "documentation_url": "https://github.com/SNPbuilds/csms-api",
  "status": "403"
}
```

### 404 Not Found
```json
{
  "message": "រកមិនឃើញទិន្នន័យដែលលោកអ្នកកំពុងស្វែងរកទេ សូមពិនិត្យមើល URL ឡើងវិញម្តងទៀត",
  "documentation_url": "https://github.com/SNPbuilds/csms-api",
  "status": "404"
}
```

### 422 Validation Error
```json
{
  "message": "ទិន្នន័យដែលបានបញ្ជូនមកមិនត្រឹមត្រូវតាមទម្រង់កំណត់ទេ សូមពិនិត្យមើលព័ត៌មានដែលបានបំពេញឡើងវិញ",
  "errors": {
    "category_name": ["The category_name field is required."]
  },
  "documentation_url": "https://github.com/SNPbuilds/csms-api",
  "status": "422"
}
```

---

## 6. Postman Collections

Pre-configured Postman collections are located in `postman_collections/`:

1. `Level_0_System_and_Health.postman_collection.json`
2. `Level_1_Public_Catalog_and_Auth.postman_collection.json`
3. `Level_2_Cashier_POS_and_Customers.postman_collection.json`
4. `Level_3_Manager_Inventory_and_Purchasing.postman_collection.json`
5. `Level_4_Admin_Employees_and_User_Management.postman_collection.json`
6. `SS_MIS_All_In_One_Complete.postman_collection.json` (Master Suite)
7. `SS_MIS_Production.postman_environment.json` (Production Environment)

---

## 7. Local Development Setup

### Prerequisites
* PHP 8.2 or 8.3
* Composer
* PostgreSQL (or SQLite for local testing)

### Installation
```bash
# 1. Clone repository
git clone https://github.com/SNPbuilds/csms-api.git
cd csms-api

# 2. Install dependencies
composer install

# 3. Setup environment configuration
cp .env.example .env
php artisan key:generate

# 4. Configure database in .env and run migrations with seed data
php artisan migrate --seed

# 5. Start local server
php artisan serve
```

---

## 8. Security & Public Repository Guidelines

* No sensitive production credentials or private keys are stored in the repository.
* All live environment configurations must be supplied via serverless environment variables on Vercel or cloud host.
* Database connections must enforce SSL mode (`DB_SSLMODE=require`).
