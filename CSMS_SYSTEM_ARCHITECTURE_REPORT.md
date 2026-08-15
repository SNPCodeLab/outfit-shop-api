# CSMS-API: Formal System Architecture Report

> **Official System Classification**:  
> * **Formal Term (for Academic & Professional Reports)**: A Monolithic, Headless REST API Backend  
> * **Casual Term (for Daily Communication)**: A REST API Backend  

---

## Executive Summary

The **Store Stock & Point-of-Sale Information System (SS-MIS / CSMS-API)** is an enterprise-grade web application backend designed to manage store stock, product catalog variants, sales transactions, supplier purchases, audit logs, and user authorization.

The system is engineered as a **Monolithic, Headless REST API Backend** using **Laravel 11** and **PHP 8.3**, connected to a **24/7 Managed Serverless PostgreSQL Database on Neon Cloud**, with full architectural compatibility for **Oracle SQL Database** via `yajra/laravel-oracle`.

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
            │   (Active Production)   │ │  (Supported Engine)     │
            └─────────────────────────┘ └─────────────────────────┘
```

### 1.1 Monolithic Architecture
CSMS-API adopts a **monolithic backend design**. All core domain services—including authentication, product catalog management, sales checkouts, inventory stock movements, and audit logging—reside within a single unified codebase.

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
| **Active DB** | Neon Cloud PostgreSQL | Managed 24/7 serverless PostgreSQL database |
| **Dual DB Engine** | Oracle SQL | Fully configured with `yajra/laravel-oracle` (`DB_CONNECTION=oracle`) |
| **Security** | Laravel Sanctum | State-less HTTP Bearer Token authentication |
| **Authorization** | Spatie Laravel-Permission | Role-Based Access Control (RBAC) with `{resource}.{action}` permissions |

---

## 3. Database Schema & Entity Structure

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

## 4. Security & Role-Based Access Control (RBAC)

### 4.1 Authentication
Authentication is enforced using **Laravel Sanctum Bearer Tokens**:
* `POST /api/v1/auth/login`: Authenticates user credentials and issues a cryptographic Bearer token.
* `POST /api/v1/auth/logout`: Revokes active tokens.
* `GET  /api/v1/auth/me`: Returns current user profile and assigned roles.

### 4.2 Seeded Role Matrix

| Role | Permissions Scoping | Target Users |
| :--- | :--- | :--- |
| **`admin`** | Unrestricted system-wide CRUD | System Administrators & Executives |
| **`manager`** | Catalog CRUD, POS checkout, void sales, stock adjustment, suppliers | Store Managers & Supervisors |
| **`cashier`** | POS checkout, customer creation, read-only catalog | POS Counter Operators |
| **`viewer`** | Read-only inspection across catalog and sales | Auditors & Trainees |

---

## 5. API Gateway Endpoints (`/api/v1/`)

```text
POST   /api/v1/auth/register .................. Register new user account
POST   /api/v1/auth/login ..................... Issue Sanctum Bearer Token
GET    /api/v1/auth/me ........................ Return profile & assigned roles
POST   /api/v1/auth/logout .................... Revoke active token

GET    /api/v1/categories ..................... List product categories
GET    /api/v1/products ....................... List products with pagination
POST   /api/v1/products ....................... Create new product (Admin / Manager)
GET    /api/v1/variants ....................... List size/color variants & stock levels

POST   /api/v1/sales/checkout ................. Process POS sale & auto-deduct stock
POST   /api/v1/sales/{id}/void ................ Void sale transaction & restore stock
POST   /api/v1/stock-movements/adjust ......... Manual inventory stock adjustment
```

---

## 6. Conclusion & Terminology Summary

When describing this system in technical writing, academic reports, or project presentations:

* **Formal Description**:
  > *"CSMS-API is engineered as a **Monolithic, Headless REST API Backend** built with Laravel 11 and PostgreSQL on Neon Cloud, supporting multi-role RBAC authorization and dual-engine Oracle SQL database compatibility."*

* **Casual Description**:
  > *"I built a REST API backend for the Store Stock and Point-of-Sale system."*
