# CSMS-API: Formal System Architecture Report

> **Official System Classification**:
> * **Formal Term (Academic & Professional Reports)**: A Monolithic, Headless REST API Backend — Transaction Processing System (TPS/OLTP) with Management Information System (MIS) Reporting
> * **Casual Term (Daily Communication)**: A REST API backend for a retail clothing store

---

## Executive Summary

The **Store Stock & Point-of-Sale Information System (SS-MIS / CSMS-API)** is an enterprise-grade web application backend designed to manage store stock, product catalog variants, sales transactions, supplier purchases, audit logs, and user authorization for a retail clothing store.

The system is engineered as a **Monolithic, Headless REST API Backend** using **Laravel 11** and **PHP 8.3**, connected to a **24/7 Managed Serverless PostgreSQL Database on Neon Cloud**, with documented compatibility for **Oracle SQL Database** via `yajra/laravel-oracle` as an alternative engine.

---

## 1. Architectural Classification & Design Principles

```
                  ┌─────────────────────────────────────────┐
                  │          Frontend Clients               │
                  │  (Web App / Mobile POS / Desktop GUI)   │
                  └────────────────────┬────────────────────┘
                                       │
                                       │ HTTPS / JSON
                                       ▼
                  ┌─────────────────────────────────────────┐
                  │        CSMS REST API Gateway            │
                  │   (Monolithic, Headless Backend)        │
                  └────────────────────┬────────────────────┘
                                       │
                         ┌─────────────┴─────────────┐
                         ▼                           ▼
            ┌─────────────────────────┐ ┌─────────────────────────┐
            │   Neon Cloud PostgreSQL │ │ Oracle SQL Database XE  │
            │   (Active Production)   │ │ (Alternative Engine)    │
            └─────────────────────────┘ └─────────────────────────┘
```

### 1.1 Monolithic Architecture
CSMS-API adopts a **monolithic backend design**. All core domain services — including authentication, product catalog management, sales checkouts, inventory stock movements, and audit logging — reside within a single unified codebase.

* **Advantages**:
  * Simplified deployment and single-repository governance.
  * ACID-compliant relational transactions across sales and inventory stock.
  * Zero inter-service network latency compared to microservice architectures.

### 1.2 Headless Architecture
The backend is strictly **headless**. It contains zero coupled HTML view templates or user interface rendering logic.

* **Client Decoupling**: The backend strictly consumes and outputs standard `application/json` payloads. Any client technology (React, Next.js, Vue, iOS, Android, or desktop POS hardware) can interface seamlessly with the API.

### 1.3 RESTful API Design
All resource endpoints follow standard REST conventions:
* Standard HTTP Verbs: `GET` (read), `POST` (create), `PUT`/`PATCH` (update), `DELETE` (remove).
* Versioned URI Namespace: All API endpoints are isolated under the `/api/v1/` URI path.

---

## 2. Technology Stack & Database Engine

| Layer | Component | Specification |
| :--- | :--- | :--- |
| **Framework** | Laravel 11 | Modern PHP framework with built-in ORM (Eloquent) & Routing |
| **Runtime** | PHP 8.3 | High-performance server runtime with strict type enforcement |
| **Primary DB** | Neon Cloud PostgreSQL | Managed 24/7 serverless PostgreSQL — **active production engine** |
| **Alt DB Engine** | Oracle SQL | Fully configured via `yajra/laravel-oracle` (`DB_CONNECTION=oracle`) — alternative engine for on-premise deployment |
| **Security** | Laravel Sanctum | Stateless HTTP Bearer Token authentication |
| **Authorization** | Spatie Laravel-Permission | Role-Based Access Control (RBAC) with `{resource}.{action}` permissions |
| **Deployment** | Vercel Serverless | PHP runtime via Vercel serverless functions |

> **Database Consistency Note**: PostgreSQL on Neon Cloud is the **canonical production database**. Oracle SQL is a fully supported alternative for on-premise enterprise deployments that require Oracle licensing (e.g., government agencies or clients with existing Oracle infrastructure). All migrations, schema definitions, and queries are written in standard ANSI SQL and are compatible with both engines.

---

## 3. Information Systems (IS) Framework

This section classifies SS-MIS using the classic **IS Framework** model: Input → Process → Output, with Feedback and Control loops.

### 3.1 IS Framework Diagram

```
┌────────────────────────────────────────────────────────────────────────────────┐
│                        INFORMATION SYSTEMS FRAMEWORK                           │
│                                                                                │
│  ┌──────────────┐    ┌────────────────────────┐    ┌─────────────────────────┐│
│  │    INPUT     │    │       PROCESS          │    │        OUTPUT           ││
│  │              │───►│                        │───►│                         ││
│  │ • Barcode    │    │ • POS Checkout         │    │ • Sale Invoice (JSON)   ││
│  │   Scan       │    │   (POSService)         │    │ • Stock Level           ││
│  │ • Sale Items │    │ • Stock Deduction      │    │ • Audit Trail           ││
│  │ • Customer   │    │   (DB Transaction)     │    │ • Payment Record        ││
│  │   Data       │    │ • Loyalty Points       │    │ • Low-Stock Alert       ││
│  │ • Payment    │    │   Calculation          │    │ • Dashboard Stats       ││
│  │   Amount     │    │ • Auth/RBAC Check      │    │ • API Response (JSON)   ││
│  │ • Purchase   │    │ • Inventory Adjustment │    │ • Purchase Confirmation ││
│  │   Order      │    │ • Audit Logging        │    │ • Error Codes (422/403) ││
│  └──────────────┘    └────────────────────────┘    └─────────────────────────┘│
│           ▲                                                     │              │
│           │              FEEDBACK LOOP                         │              │
│           └────────── Stock alerts trigger reorder ────────────┘              │
│                        Dashboard metrics drive decisions                       │
│                                                                                │
│  ┌──────────────────────────────────────────────────────────────────────────┐ │
│  │                         CONTROL MECHANISMS                               │ │
│  │  • RBAC Middleware (role:MANAGER,ADMIN) — restricts write operations     │ │
│  │  • Sanctum Bearer Tokens — enforces authenticated sessions               │ │
│  │  • Rate Limiting (10 req/min auth, 120 req/min API) — prevents abuse     │ │
│  │  • DB Transactions + lockForUpdate() — prevents race conditions          │ │
│  │  • Input Validation (Laravel Rules) — rejects malformed data             │ │
│  │  • Audit Log Service — records all state changes for accountability      │ │
│  └──────────────────────────────────────────────────────────────────────────┘ │
└────────────────────────────────────────────────────────────────────────────────┘
```

### 3.2 IS Framework Mapped to Real Endpoints

| IS Component | Description | Real API Endpoint(s) |
| :--- | :--- | :--- |
| **INPUT — Raw Data** | Cashier scans barcode; enters quantity, payment | `GET /api/v1/variants/barcode/{barcode}` |
| **INPUT — Transaction Data** | Customer identity, items, discount, payment method | `POST /api/v1/sales/checkout` (request body) |
| **INPUT — Restock Data** | Supplier ID, product variant, quantity, unit cost | `POST /api/v1/purchases` (request body) |
| **PROCESS — Sales Logic** | `POSService::checkout()` validates stock, computes totals, deducts inventory in a single DB transaction | Internal: `POSService.php` |
| **PROCESS — Inventory Logic** | `InventoryService::receivePurchase()` increments variant stock and creates stock movement log | Internal: `InventoryService.php` |
| **PROCESS — Auth/RBAC** | `CheckRole` middleware verifies token bearer has the required role before execution | Internal: `CheckRole.php` |
| **OUTPUT — Structured JSON** | Sale confirmation, invoice number, payment receipt | `POST /api/v1/sales/checkout` response |
| **OUTPUT — Analytics** | Requests today, top endpoints, error counts, recent traffic | `GET /api/v1/dashboard/stats` |
| **OUTPUT — Stock Status** | Variants with quantity ≤ reorder level | `GET /api/v1/variants/low-stock` |
| **OUTPUT — Audit Trail** | Log of every CREATE/UPDATE/DELETE/SALE/VOID action | `GET /api/v1/audit-logs` |
| **FEEDBACK — Control Signal** | Low-stock endpoint signals manager to create purchase order | `GET /api/v1/variants/low-stock` → `POST /api/v1/purchases` |
| **FEEDBACK — Error Response** | 422 Validation / 403 Forbidden responses drive client correction | All write endpoints |
| **CONTROL — Access Gate** | RBAC middleware blocks unauthorized writes system-wide | `middleware('role:MANAGER,ADMIN')` |
| **CONTROL — Rate Limiter** | Throttle prevents brute-force login and API flooding | `middleware('throttle:10,1')` on auth |

---

## 4. System Classification: TPS/OLTP, MIS, DSS & EIS

### 4.1 Primary Classification — Transaction Processing System (TPS / OLTP)

SS-MIS is **primarily classified as a Transaction Processing System (TPS)** with OLTP (Online Transaction Processing) characteristics.

A TPS is an information system that processes routine, structured, high-frequency business transactions in real-time. SS-MIS fulfills this classification because:

| TPS Criterion | Implementation in SS-MIS |
| :--- | :--- |
| **High-volume routine transactions** | POS sales processed per customer visit, multiple times per day |
| **ACID compliance** | DB transactions wrap all checkout, void, and purchase operations via `DB::transaction()` |
| **Real-time processing** | No batching — every `POST /sales/checkout` commits immediately |
| **Concurrency control** | `lockForUpdate()` prevents negative stock from concurrent cashiers |
| **Structured data** | All inputs/outputs are strongly typed (validated Laravel Request → PostgreSQL) |
| **Operational data store** | Primary data exists in normalized relational tables (3NF) |

**TPS Processes in SS-MIS:**

```
POS CHECKOUT TRANSACTION
  POST /api/v1/sales/checkout
  ├── Validate stock availability     (TPS: Data Validation)
  ├── Lock variant rows               (TPS: Concurrency Control)
  ├── Create SaleHeader               (TPS: Master Record Creation)
  ├── Create SaleDetail[] per item    (TPS: Detail Record Creation)
  ├── Decrement variant.quantity      (TPS: File/Record Update)
  ├── Create StockMovement audit log  (TPS: Transaction Log)
  └── Create Payment record           (TPS: Financial Settlement)

PURCHASE RECEIVING TRANSACTION
  POST /api/v1/purchases
  ├── Validate supplier & variants    (TPS: Data Validation)
  ├── Create PurchaseHeader           (TPS: Master Record Creation)
  ├── Create PurchaseDetail[] per SKU (TPS: Detail Record Creation)
  └── Increment variant.quantity      (TPS: Inventory Update)
```

### 4.2 Secondary Classification — Management Information System (MIS) Reporting

SS-MIS also implements **MIS-level reporting** features that convert raw TPS transaction data into structured summaries for management decision-making:

| MIS Feature | Endpoint | Insight Provided |
| :--- | :--- | :--- |
| **Traffic Analytics** | `GET /api/v1/dashboard/stats` | API usage trends, top endpoints, user activity |
| **Low-Stock Report** | `GET /api/v1/variants/low-stock` | Which SKUs need restocking |
| **Sales History** | `GET /api/v1/sales` | Complete transaction record for review |
| **Stock Movement Log** | `GET /api/v1/stock-movements` | Full inventory change history |
| **Audit Log** | `GET /api/v1/audit-logs` | Security and compliance trail |
| **Purchase History** | `GET /api/v1/purchases` | Supplier spending and receiving history |

### 4.3 Future Scope — DSS & EIS (Not Currently Implemented)

| System Type | Description | Future Implementation Path |
| :--- | :--- | :--- |
| **Decision Support System (DSS)** | Semi-structured analysis to support management decisions (e.g., "Should we reorder this SKU?") | AI insight endpoints: `/api/v1/ai/insights/dead-stock`, `/api/v1/ai/insights/sales-forecast` |
| **Executive Information System (EIS)** | Strategic-level dashboards for executives (trend analysis, market positioning, profitability) | Sales revenue by category/period, margin analysis, YoY comparison endpoints |
| **Expert System** | Rule-based or ML-based automated recommendations | Dynamic reorder trigger based on historical sales velocity |

> **Summary**: SS-MIS is a **TPS/OLTP system with embedded MIS reporting**. DSS and EIS capabilities are on the product roadmap and can be layered on top of the existing normalized data model without schema changes.

---

## 5. Database Architecture — Relational Model, Schema & Subschema

### 5.1 Relational Model Overview

The SS-MIS database follows the **relational model** as defined by E.F. Codd:
* Data is organized into **relations (tables)** with rows (tuples) and columns (attributes).
* All relationships between entities are expressed via **foreign key constraints**.
* The schema is normalized to **Third Normal Form (3NF)** — no transitive dependencies.
* **Referential integrity** is enforced at the database engine level.

### 5.2 Database Schema (Global Conceptual Schema)

The **global schema** defines all 22 normalized tables visible to the database administrator. The system is organized into four logical domains:

```
DOMAIN 1 — CATALOG & INVENTORY
  categories          → products → product_variants
  clothing_sizes      →          → product_variants
  colors              →          → product_variants
  product_variants    →          → stock_movements

DOMAIN 2 — POINT-OF-SALE & PURCHASING
  customers     → sale_headers    → sale_details  → product_variants
  employees     → sale_headers    → payments
  suppliers     → purchase_headers → purchase_details → product_variants

DOMAIN 3 — ADMINISTRATION & SECURITY
  users                 → audit_logs
  users                 → personal_access_tokens
  employees             → (Sanctum tokenable via HasApiTokens)
  roles                 → model_has_roles → users/employees
  permissions           → model_has_permissions → roles

DOMAIN 4 — OBSERVABILITY
  api_logs              (HTTP request/response logging)
  audit_logs            (business event logging)
```

### 5.3 Subschema — RBAC-Scoped Data Visibility

A **subschema** (also called an external schema or user view) defines the subset of the full database schema that a specific user role can see and interact with. In SS-MIS, subschemas are enforced by:
1. **RBAC Middleware** (`CheckRole.php`) — blocks entire endpoints by role.
2. **Eloquent `$hidden` array** — removes sensitive fields from JSON output per model.
3. **API route grouping** — routes are organized in access tiers so that unauthorized roles never reach the controller.

#### Subschema Matrix — What Each Role Can See

| Table / Resource | ADMIN | MANAGER | CASHIER | STAFF/GUEST |
| :--- | :---: | :---: | :---: | :---: |
| `categories` | ✅ CRUD | ✅ CRUD | 👁 Read | 👁 Read |
| `products` | ✅ CRUD | ✅ CRUD | 👁 Read | 👁 Read |
| `product_variants` | ✅ CRUD | ✅ CRUD | 👁 Read | 👁 Read |
| `clothing_sizes` | ✅ CRUD | ✅ CRUD | 👁 Read | 👁 Read |
| `colors` | ✅ CRUD | ✅ CRUD | 👁 Read | 👁 Read |
| `customers` | ✅ CRUD | ✅ CRUD | ✅ CR | ❌ Hidden |
| `sale_headers` | ✅ CRUD | ✅ R + Void | ✅ R + Checkout | ❌ Hidden |
| `sale_details` | ✅ Read | ✅ Read | ✅ Read | ❌ Hidden |
| `payments` | ✅ Read | ✅ Read | ✅ Read | ❌ Hidden |
| `suppliers` | ✅ CRUD | ✅ CRUD | ❌ Hidden | ❌ Hidden |
| `purchase_headers` | ✅ CRUD | ✅ CR | ❌ Hidden | ❌ Hidden |
| `stock_movements` | ✅ CRUD | ✅ CR | ❌ Hidden | ❌ Hidden |
| `employees` | ✅ CRUD | ❌ Hidden | ❌ Hidden | ❌ Hidden |
| `audit_logs` | ✅ Read | ✅ Read | ❌ Hidden | ❌ Hidden |
| `api_logs` | ✅ Read | ❌ Hidden | ❌ Hidden | ❌ Hidden |
| `users` (system) | ✅ CR | ❌ Hidden | ❌ Hidden | ❌ Hidden |
| `roles/permissions` | ✅ Assign | ❌ Hidden | ❌ Hidden | ❌ Hidden |

> **Legend**: ✅ = Accessible | 👁 = Read-only | ❌ = Blocked by middleware | CR = Create & Read | CRUD = Full Create/Read/Update/Delete

#### Sensitive Field Filtering (Model-Level Subschema)

Certain fields are removed from all API responses regardless of role:

| Table | Hidden Fields | Reason |
| :--- | :--- | :--- |
| `employees` | `password_hash` | Password is never transmitted in plaintext |
| `users` | `password` | Laravel default — hashed, never returned |
| `personal_access_tokens` | `token` (hashed value) | SHA-256 hash is stored, never returned |

---

## 6. Three-Tier Network Architecture

SS-MIS follows the **three-tier client-server model** — the industry standard for web applications:

```
┌──────────────────────────────────────────────────────────────────────────────┐
│                         THREE-TIER NETWORK DIAGRAM                           │
│                                                                              │
│  TIER 1 — PRESENTATION (Thin Client)                                         │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────────────┐│
│  │  Web Browser│  │ Mobile App  │  │ POS Terminal │  │  Postman / API      ││
│  │  (React /   │  │ (iOS/Android│  │ (Barcode     │  │  Testing Client     ││
│  │   Next.js)  │  │  Flutter)   │  │  Scanner)    │  │                     ││
│  └──────┬──────┘  └──────┬──────┘  └──────┬───────┘  └──────────┬──────────┘│
│         │                │                │                      │           │
│         └────────────────┴────────────────┴──────────────────────┘           │
│                                    │                                         │
│         ◄── HTTPS / TLS 1.3  (JSON payload over port 443) ──►               │
│                                    │                                         │
│  TIER 2 — APPLICATION (App Server)                                           │
│  ┌─────────────────────────────────┴──────────────────────────────┐          │
│  │              Laravel 11 / PHP 8.3 (Vercel Serverless)          │          │
│  │                                                                │          │
│  │  ┌──────────────┐  ┌──────────────┐  ┌───────────────────┐   │          │
│  │  │  Router      │  │  Controllers │  │  Service Layer    │   │          │
│  │  │  /api/v1/*   │─►│  (14 V1)     │─►│  POSService       │   │          │
│  │  └──────────────┘  └──────────────┘  │  InventoryService │   │          │
│  │                                      │  AuditLogService  │   │          │
│  │  ┌──────────────┐  ┌──────────────┐  └───────────────────┘   │          │
│  │  │  Middleware  │  │  Eloquent    │                           │          │
│  │  │  Auth/RBAC   │  │  ORM Models  │                           │          │
│  │  │  Rate Limit  │  │  (17 models) │                           │          │
│  │  │  API Logger  │  └──────────────┘                           │          │
│  │  └──────────────┘                                             │          │
│  └─────────────────────────────────┬──────────────────────────────┘          │
│                                    │                                         │
│         ◄── PostgreSQL Wire Protocol (TCP, TLS encrypted) ──►                │
│                                    │                                         │
│  TIER 3 — DATA (Database Server)                                             │
│  ┌─────────────────────────────────┴──────────────────────────────┐          │
│  │          Neon Cloud — Managed Serverless PostgreSQL            │          │
│  │                                                                │          │
│  │  ┌──────────────┐  ┌──────────────┐  ┌───────────────────┐   │          │
│  │  │  22 Relational│  │ Connection   │  │  ACID Transactions│   │          │
│  │  │  Tables (3NF) │  │ Pooling      │  │  Row-Level Locks  │   │          │
│  │  └──────────────┘  └──────────────┘  └───────────────────┘   │          │
│  │                  [Alternative: Oracle SQL on-premise]          │          │
│  └────────────────────────────────────────────────────────────────┘          │
└──────────────────────────────────────────────────────────────────────────────┘
```

### Tier Responsibilities

| Tier | Component | Responsibility |
| :--- | :--- | :--- |
| **Tier 1 — Presentation** | Web/Mobile/POS client | Renders UI, captures user input, displays API responses. Contains zero business logic. |
| **Tier 2 — Application** | Laravel 11 on Vercel | All business logic, authentication, authorization, input validation, transaction orchestration. |
| **Tier 3 — Data** | Neon PostgreSQL | Persistent storage, referential integrity, ACID transactions, row-level locking. |

> **Why Thin Client?** The backend is **headless** — the presentation tier has no privileged access to the database. All data flows through the application tier, which enforces RBAC and business rules. This is the standard enterprise security model.

---

## 7. Business Value — Porter's Value Chain Analysis

Michael Porter's **Value Chain** framework identifies the activities through which a business creates value. SS-MIS digitizes and automates both primary and support activities of a retail clothing store:

### 7.1 Primary Activities

#### Inbound Logistics — Supplier & Purchase Order Management
> *"Receiving, storing, and disseminating inputs to the product"*

| Activity | SS-MIS Implementation |
| :--- | :--- |
| Receive goods from suppliers | `POST /api/v1/purchases` — creates purchase order and auto-increments stock |
| Track supplier relationships | `GET/POST/PUT /api/v1/suppliers` — maintains supplier registry |
| Monitor restock needs | `GET /api/v1/variants/low-stock` — identifies variants below reorder level |
| Cost-price tracking | `cost_price` field on `PRODUCT_VARIANT` updated on every receive |
| **Business Value** | Eliminates manual stockbook entries; prevents stockouts through real-time low-stock visibility |

#### Operations — Inventory & Catalog Management
> *"Transforming inputs into the final product"*

| Activity | SS-MIS Implementation |
| :--- | :--- |
| Organize product catalog | `GET/POST/PUT/DELETE /api/v1/categories` and `/api/v1/products` |
| Manage size/color variants | `GET/POST/PUT /api/v1/variants` — SKU-level inventory tracking |
| Manual stock adjustments | `POST /api/v1/stock-movements/adjust` — ADJUSTMENT/RETURN_IN/RETURN_OUT types |
| Barcode registration | `barcode` field on variant for EAN scanner lookup |
| **Business Value** | Real-time stock accuracy; one source of truth for all sales channels |

#### Outbound Logistics — POS Sales & Payment
> *"Collecting, storing, and distributing the product to buyers"*

| Activity | SS-MIS Implementation |
| :--- | :--- |
| Process sale at counter | `POST /api/v1/sales/checkout` — multi-item, multi-discount, multi-payment-method |
| Print invoice | Response includes invoice number, itemized details, totals, change due |
| Accept multiple payment types | `CASH`, `CARD`, `QR`, `ABA` payment methods on `PAYMENT` table |
| Void erroneous transactions | `POST /api/v1/sales/{id}/void` — reverses stock and marks sale VOIDED |
| **Business Value** | Sub-second checkout processing; automatic stock deduction eliminates manual counting |

#### Marketing & Sales — Customer & Loyalty Management
> *"Inducing buyers to purchase the product"*

| Activity | SS-MIS Implementation |
| :--- | :--- |
| Register customers | `POST /api/v1/customers` — name, phone, email, address |
| Track loyalty points | `total_points` column on `CUSTOMER` table |
| Apply per-item discounts | `discount_amount` on `SALE_DETAIL` — supports SKU-level promotions |
| Apply order-level discounts | `discount_amount` on `SALE_HEADER` — supports coupon/blanket discounts |
| **Business Value** | Builds customer database for targeted promotions; loyalty program drives repeat visits |

#### Customer Service — After-Sale Support
> *"Maintaining and enhancing the product's value after purchase"*

| Activity | SS-MIS Implementation |
| :--- | :--- |
| Access sale history | `GET /api/v1/sales/{id}` — full invoice with payment details |
| Process return/void | `POST /api/v1/sales/{id}/void` — automatic stock restoration |
| Resolve disputes | `audit_logs` table provides complete change history per entity |
| **Business Value** | Eliminates paper receipts; any cashier can retrieve any past transaction in seconds |

### 7.2 Support Activities

#### Infrastructure — Technology & Administration
| Activity | SS-MIS Implementation |
| :--- | :--- |
| System monitoring | `GET /status` — live endpoint status dashboard with real-time probing |
| API traffic oversight | `GET /api/v1/dashboard/stats` — requests/day, top endpoints, error rates |
| Role-based governance | 4-tier RBAC (ADMIN → MANAGER → CASHIER → STAFF) |
| Serverless auto-scaling | Vercel serverless functions scale to demand with zero manual provisioning |

#### Human Resource Management — Employee Administration
| Activity | SS-MIS Implementation |
| :--- | :--- |
| Manage staff accounts | `GET/POST/PUT/DELETE /api/v1/employees` — ADMIN only |
| Role assignment | `ADMIN` creates accounts; roles (CASHIER/MANAGER) define access |
| Accountability tracking | Every sale, void, and adjustment records `employee_id` |
| Login/logout audit | `AuditLogService` logs LOGIN and LOGOUT events by employee |

#### Technology Development — Platform & Innovation
| Activity | SS-MIS Implementation |
| :--- | :--- |
| API versioning | `/api/v1/` prefix ready for future `/v2/` without breaking existing clients |
| Dual database support | PostgreSQL primary + Oracle alternative for enterprise flexibility |
| Open integration | Headless JSON API allows any frontend or mobile app to integrate |
| Security hardening | OWASP Top 10 compliant; dependency vulnerabilities patched continuously |

#### Procurement — Supplier Intelligence
| Activity | SS-MIS Implementation |
| :--- | :--- |
| Supplier registry | Full supplier CRUD with contact, email, address, status |
| Purchase tracking | Every `PURCHASE_HEADER` records supplier, employee, date, and total cost |
| Cost price history | `cost_price` on `PRODUCT_VARIANT` reflects latest received cost |

---

## 8. Ethics & Data Protection

SS-MIS handles sensitive personal and financial data. This section documents the ethical obligations and technical controls that protect individuals whose data is stored.

### 8.1 Personally Identifiable Information (PII) Inventory

| Table | PII Fields | Data Subject | Sensitivity |
| :--- | :--- | :--- | :--- |
| `employees` | `employee_name`, `email`, `phone`, `gender`, `position` | Store employees | 🟡 Internal — restricted to ADMIN |
| `employees` | `password_hash`, `salary` | Store employees | 🔴 Confidential — hidden from all API responses |
| `customers` | `customer_name`, `email`, `phone`, `address`, `gender` | Retail customers | 🟡 Personal — requires consent for collection |
| `users` | `name`, `email`, `password` | System users/staff | 🟡 Internal — Admin-created accounts |
| `audit_logs` | `ip_address`, `user_agent` | All users | 🟡 Behavioral — supports security monitoring |
| `api_logs` | `ip_address`, `user_id` | All users | 🟡 Behavioral — request audit trail |

### 8.2 Password & Credential Security

| Control | Implementation | Standard |
| :--- | :--- | :--- |
| **Employee password hashing** | `bcrypt` (cost factor 12) via `Hash::make()` in `AuthController` | NIST SP 800-63B |
| **User password hashing** | Laravel default `bcrypt` in `User::create()` | NIST SP 800-63B |
| **Token storage** | SHA-256 hashed in `personal_access_tokens.token` — plaintext only transmitted once at login | OWASP ASVS |
| **Token transmission** | `Authorization: Bearer <token>` over HTTPS/TLS 1.3 only | RFC 6750 |
| **No plaintext passwords** | `password_hash` and `password` are in `$hidden` array — never returned in JSON | GDPR Art. 32 |

### 8.3 RBAC as a Data Access Control (DAC) Mechanism

RBAC enforces the **Principle of Least Privilege** — each role can only access data required for their specific job function:

| Ethical Principle | Implementation |
| :--- | :--- |
| **Need-to-know basis** | CASHIER cannot see `employees`, `audit_logs`, or `suppliers` |
| **Separation of duties** | Only ADMIN can create/delete employee accounts — no self-elevation |
| **Accountability** | Every mutating action logs `user_id` in `audit_logs` for non-repudiation |
| **Minimal data exposure** | Sensitive fields (`salary`, `password_hash`) excluded from all response payloads |

### 8.4 Data Collection Ethics & Consent

| Concern | Guidance |
| :--- | :--- |
| **Customer PII** | Customer data (name, phone, email) is collected for loyalty/receipt purposes only. Stores should obtain verbal or written consent per local data protection laws (e.g., PDPA in Thailand/Cambodia). |
| **Data retention** | Soft deletes (`deleted_at`) preserve records for financial audit compliance — implement a retention policy (e.g., 7 years for financial data, 1 year for behavioral logs). |
| **Right to erasure** | GDPR/PDPA "right to be forgotten" — `deleted_at` soft delete satisfies operational needs; hard delete for personal data on formal request. |
| **Employee monitoring** | API logging tracks all requests by `user_id`. Employees should be informed that system actions are logged. |
| **Third-party sharing** | No data is transmitted to third parties beyond Neon Cloud (database host) and Vercel (application host). Both are SOC 2 Type II certified. |

### 8.5 Security Compliance Summary

| OWASP Top 10 | Control | Status |
| :--- | :--- | :--- |
| A01 Broken Access Control | RBAC middleware on all private endpoints | ✅ COMPLIANT |
| A02 Cryptographic Failures | Bcrypt passwords, SHA-256 tokens, TLS 1.3 | ✅ COMPLIANT |
| A03 Injection | Eloquent ORM parameterized queries exclusively | ✅ COMPLIANT |
| A04 Insecure Design | 4-tier access model, separation of duties | ✅ COMPLIANT |
| A05 Security Misconfiguration | Debug disabled in production, sanitized error responses | ✅ COMPLIANT |
| A06 Vulnerable Components | Zero open High/Moderate CVEs (audited August 2026) | ✅ COMPLIANT |
| A07 Auth Failures | Sanctum Bearer tokens, rate-limited login (10/min) | ✅ COMPLIANT |
| A08 Software Integrity | `composer.lock` integrity verified | ✅ COMPLIANT |
| A09 Logging & Monitoring | Centralized `api_logs` + `audit_logs` | ✅ COMPLIANT |

---

## 9. Enterprise Pattern Classification

### 9.1 What SS-MIS Does Implement

| Enterprise Pattern | Extent | Evidence |
| :--- | :--- | :--- |
| **ERP-Like (Operations Module)** | Partial | Inventory management, supplier purchasing, purchase orders, stock movements — mirrors an ERP's Supply Chain & Inventory module |
| **ERP-Like (Finance Module)** | Partial | Sale revenue tracking, payment recording, cost-price vs. sale-price margin data |
| **CRM-Like** | Partial | Customer registry, loyalty points, purchase history per customer — mirrors a basic CRM's Contact & Transaction module |
| **POS System** | Full | Complete point-of-sale: barcode scan → checkout → payment → receipt → stock deduction |
| **Audit Trail / Compliance** | Full | Immutable audit log of all CREATE/UPDATE/DELETE/SALE/VOID/LOGIN/LOGOUT events |
| **RBAC / Identity Management** | Full | Spatie-backed role system with permission scoping |
| **REST API Gateway** | Full | Versioned `/api/v1/` endpoints following standard HTTP conventions |

### 9.2 What SS-MIS Does NOT Implement (Explicit Scope Boundaries)

| Enterprise Pattern | Status | Clarification |
| :--- | :--- | :--- |
| **Full ERP** | ❌ Not in scope | No accounting/GL module, no payroll, no manufacturing, no project management |
| **Full CRM** | ❌ Not in scope | No email campaigns, no lead pipeline, no sales funnel analytics |
| **SCM (Supply Chain Management)** | ❌ Partial only | Has purchase orders and supplier registry, but no demand forecasting, shipping tracking, or multi-tier supply chain |
| **WMS (Warehouse Management)** | ❌ Not in scope | No bin/shelf location tracking, no pick-pack-ship workflow |
| **Business Intelligence (BI)** | ❌ Not in scope | No OLAP cubes, no data warehouse, no cross-dimensional analysis |
| **eCommerce / Online Storefront** | ❌ Not in scope | Headless API only — no shopping cart UI, no checkout flow for end customers |
| **Accounting / GL** | ❌ Not in scope | Financial figures are recorded but no double-entry bookkeeping, no P&L, no balance sheet |

### 9.3 Correct System Positioning Statement

> For academic reports and professional presentations, use this description:
>
> *"SS-MIS (CSMS-API) is a **Transaction Processing System (TPS)** with embedded **MIS reporting capabilities**, implemented as a Monolithic, Headless RESTful API. It incorporates domain-specific modules analogous to an **ERP Operations module** (inventory, purchasing) and a **basic CRM module** (customers, loyalty), within the scope of a single-store retail clothing POS system. It is not a full ERP, CRM, SCM, or BI platform."*

---

## 10. Database Schema & Entity Structure

The database consists of **22 normalized relational tables**:

```
                       ┌─────────────────┐
                       │     users       │
                       └────────┬────────┘
                                │ 1:N
                                ▼
                       ┌─────────────────┐
                       │  model_has_roles│
                       └────────┬────────┘
                                │ N:1
                                ▼
                       ┌─────────────────┐
                       │     roles       │
                       └─────────────────┘

 ┌──────────────┐      ┌─────────────────┐      ┌─────────────────────┐
 │  categories  ├─────►│    products     ├─────►│  product_variants   │
 └──────────────┘ 1:N  └─────────────────┘ 1:N  └──────────┬──────────┘
                                                           │ 1:N
                                                           ▼
 ┌──────────────┐      ┌─────────────────┐      ┌─────────────────────┐
 │  customers   ├─────►│  sale_headers   ├─────►│    sale_details     │
 └──────────────┘ 1:N  └─────────────────┘ 1:N  └─────────────────────┘
```

### Core Data Models:
1. **Catalog Domain**: `categories`, `clothing_sizes`, `colors`, `products`, `product_variants`.
2. **Sales Domain**: `customers`, `sale_headers`, `sale_details`, `payments`.
3. **Purchasing & Inventory**: `suppliers`, `purchase_headers`, `purchase_details`, `stock_movements`.
4. **Security & Audit**: `users`, `employees`, `roles`, `permissions`, `model_has_roles`, `audit_logs`, `api_logs`.

---

## 11. Security & Role-Based Access Control (RBAC)

### 11.1 Authentication
Authentication is enforced using **Laravel Sanctum Bearer Tokens**:
* `POST /api/v1/auth/login`: Authenticates user credentials and issues a cryptographic Bearer token.
* `POST /api/v1/auth/logout`: Revokes active tokens.
* `GET  /api/v1/auth/me`: Returns current user profile and assigned roles.

### 11.2 Seeded Role Matrix

| Role | Permissions Scoping | Target Users |
| :--- | :--- | :--- |
| **`admin`** | Unrestricted system-wide CRUD | System Administrators & Executives |
| **`manager`** | Catalog CRUD, POS checkout, void sales, stock adjustment, suppliers | Store Managers & Supervisors |
| **`cashier`** | POS checkout, customer creation, read-only catalog | POS Counter Operators |
| **`viewer`** | Read-only inspection across catalog and sales | Auditors & Trainees |

---

## 12. API Gateway Endpoints (`/api/v1/`)

```text
POST   /api/v1/auth/register .................. Register new user account (ADMIN only)
POST   /api/v1/auth/login ..................... Issue Sanctum Bearer Token
GET    /api/v1/auth/me ........................ Return profile & assigned roles
POST   /api/v1/auth/logout .................... Revoke active token

GET    /api/v1/categories ..................... List product categories (Public)
GET    /api/v1/products ....................... List products with pagination (Public)
POST   /api/v1/products ....................... Create new product (Manager / Admin)
GET    /api/v1/variants ....................... List size/color variants & stock levels (Public)
GET    /api/v1/variants/low-stock ............. List variants at or below reorder level (Public)
GET    /api/v1/variants/barcode/{barcode} ..... Barcode/SKU lookup (Public)

GET    /api/v1/customers ...................... List customers (Authenticated)
POST   /api/v1/customers ..................... Create customer (Cashier+)
POST   /api/v1/sales/checkout ................. Process POS sale & auto-deduct stock (Cashier+)
POST   /api/v1/sales/{id}/void ................ Void sale transaction & restore stock (Manager+)
POST   /api/v1/stock-movements/adjust ......... Manual inventory stock adjustment (Manager+)
POST   /api/v1/purchases ...................... Receive purchase order & increment stock (Manager+)

GET    /api/v1/dashboard/stats ................ API traffic analytics (Manager+)
GET    /api/v1/audit-logs ..................... System audit trail (Manager+)

GET    /api/v1/employees ...................... List all employees (Admin only)
POST   /api/v1/employees ..................... Create employee account (Admin only)

GET    /status ................................ System status dashboard, live endpoint probing (Public)
```

---

## 13. Conclusion & Terminology Summary

### For Technical Writing & Academic Reports

| Context | Recommended Description |
| :--- | :--- |
| **Full formal** | *"SS-MIS / CSMS-API is a Monolithic, Headless REST API Backend built with Laravel 11 and PHP 8.3, backed by a managed serverless PostgreSQL database on Neon Cloud. It implements a Transaction Processing System (TPS/OLTP) with embedded MIS reporting, Role-Based Access Control via Spatie Laravel-Permission and Laravel Sanctum, and OWASP Top 10 security compliance."* |
| **Academic short-form** | *"A TPS/OLTP system with MIS reporting capabilities for retail POS and inventory management."* |
| **Casual** | *"A REST API backend for a clothing store's inventory and point-of-sale system."* |

### Key Architectural Decisions Summary

| Decision | Choice | Rationale |
| :--- | :--- | :--- |
| **Database (Primary)** | PostgreSQL on Neon Cloud | ACID compliance, serverless scaling, 24/7 managed, open-source |
| **Database (Alternative)** | Oracle SQL via yajra | Enterprise clients requiring Oracle licensing or on-premise deployment |
| **Architecture** | Monolithic | Single deployment unit, ACID transactions across all domains, zero microservice latency |
| **API Style** | RESTful + JSON | Universal client compatibility — web, mobile, desktop, POS hardware |
| **Auth Method** | Sanctum Bearer Token | Stateless, works for both SPA and mobile clients |
| **Authorization** | Spatie RBAC | Fine-grained permission scoping with standard PHP package |
| **System Class** | TPS/OLTP + MIS | Real-time transaction processing with management reporting layer |
