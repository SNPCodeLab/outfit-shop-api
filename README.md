# CSMS-API (Store Stock & Point-of-Sale Information System)

[![Laravel Version](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![Database](https://img.shields.io/badge/Database-Neon%20Cloud%20PostgreSQL-00E599?style=for-the-badge&logo=postgresql&logoColor=white)](https://neon.tech)
[![Oracle Ready](https://img.shields.io/badge/Oracle%20SQL-Yajra%20Supported-F80000?style=for-the-badge&logo=oracle&logoColor=white)](https://github.com/yajra/laravel-oracle)
[![Live API Status](https://img.shields.io/badge/API-Live%20Online-00C853?style=for-the-badge&logo=fastapi&logoColor=white)](https://api.kesararamwithdigital.tech/api/v1/status)

---

## 📌 Project Overview

**CSMS-API (SS-MIS)** is an enterprise-grade RESTful API Gateway built with **Laravel 11**. It serves as the secure backend for Point-of-Sale (POS) applications, inventory tracking, clothing size and color variant management, supplier purchases, sales checkouts, audit logging, and role-based access control.

* **GitHub Repository**: [https://github.com/SNPbuilds/csms-api](https://github.com/SNPbuilds/csms-api)
* **Live API Base URL**: `https://api.kesararamwithdigital.tech/api/v1`

---

## 🗄️ Database Architecture & Cloud Infrastructure

* **Database Hosting**: **Managed 24/7 on Neon Cloud** with zero server maintenance.
* **Database Host**: `ep-dawn-union-awnp9sve-pooler.c-12.us-east-1.aws.neon.tech` (Neon Cloud PostgreSQL)
* **Live Database Tables (22 Total)**:
  * `users` & `personal_access_tokens` (Sanctum Authentication)
  * `employees`, `customers`, `suppliers`
  * `categories`, `clothing_sizes`, `colors`, `products`, `product_variants`
  * `purchase_headers`, `purchase_details`
  * `sale_headers`, `sale_details`, `payments`
  * `stock_movements`, `audit_logs`, `api_logs`
  * `permissions`, `roles`, `model_has_roles`, `model_has_permissions`, `role_has_permissions` (Spatie RBAC)

### 🔴 Dual Database Engine Support (PostgreSQL + Oracle SQL)
* **Active Production Engine**: PostgreSQL on Neon Cloud (`DB_CONNECTION=pgsql`).
* **Oracle SQL Engine**: Fully configured with `yajra/laravel-oracle` (`DB_CONNECTION=oracle`). Switching to Oracle SQL requires changing only 1 line in `.env`.

---

## 👑 Role-Based Access Control (RBAC)

Managed via **Spatie Laravel-Permission** using granular `{resource}.{action}` permission scoping:

| Role | Access Level | Responsibilities |
| :--- | :--- | :--- |
| **`admin`** | Full System Control | Unrestricted CRUD on catalog, sales, suppliers, purchases, employees, & audit logs |
| **`manager`** | Inventory & Sales Operations | Full catalog, sales checkout, void sales, stock adjustment, & supplier management |
| **`cashier`** | POS Operator | Process sales checkout, register customers, & read-only catalog access |
| **`viewer`** | Read-Only | Read-only inspection across products, sales, and inventory stock |

---

## 🔐 Authentication & Security

* **Authentication Strategy**: **Laravel Sanctum Bearer Tokens** (`/api/v1/auth/login`, `/api/v1/auth/register`, `/api/v1/auth/me`, `/api/v1/auth/logout`).
* **Rate Limiting**: `throttle:10,1` on login/register endpoints, `throttle:60,1` on protected resource routes.
* **CORS Configuration**: Explicitly permits requests from live web domains and local frontend dev servers (`https://app.kesararamwithdigital.tech`, `http://localhost:3000`, `http://localhost:5173`, `http://localhost:8080`, `http://localhost:4200`).
* **Standardized JSON Error Payload**:
  ```json
  {
    "success": false,
    "message": "Human readable error description",
    "error_code": "ERR_FORBIDDEN",
    "errors": null
  }
  ```

---

## 📡 Core API Routes Summary (`/api/v1/`)

```text
POST      /api/v1/auth/login .......................... Authenticate & issue Sanctum Bearer Token
POST      /api/v1/auth/register ....................... Register new user account
GET       /api/v1/auth/me ............................. Get current authenticated profile
POST      /api/v1/auth/logout ......................... Revoke active Sanctum token
GET       /api/v1/status .............................. System health & status check
GET       /api/v1/products ............................ Read product catalog (Public)
POST      /api/v1/products ............................ Create product (Admin / Manager)
POST      /api/v1/sales/checkout ...................... Process POS sale transaction & stock deduction
POST      /api/v1/sales/{id}/void ..................... Void sale transaction & restore stock
POST      /api/v1/stock-movements/adjust ............. Adjust variant inventory stock
```

---

## 📄 Project Documentation & Deliverables

* **[API_DOCS.md](file:///Users/Apple16/Desktop/SS_MIS/API_DOCS.md)**: Full REST API Documentation & Request/Response Contracts.
* **[SS_MIS.postman_collection.json](file:///Users/Apple16/Desktop/SS_MIS/SS_MIS.postman_collection.json)**: Ready-to-import Postman Collection for testing endpoints.
* **[.agents/skills/](file:///Users/Apple16/Desktop/SS_MIS/.agents/skills/)**: Agent skills for Oracle DB schema, SS-MIS entity definitions, custom domain setup, and DBeaver SQL querying.

---

## 🛠️ Local Development Setup

```bash
# 1. Clone Repository
git clone https://github.com/SNPbuilds/csms-api.git
cd csms-api

# 2. Install PHP Dependencies
composer install

# 3. Environment Setup
cp .env.example .env
php artisan key:generate

# 4. Run Migrations & Seeders
php artisan migrate --force
php artisan db:seed --class=RolesAndPermissionsSeeder

# 5. Start Development Server
php artisan serve
```
