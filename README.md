# OutfitShop API

![OutfitShop Logo](https://res.cloudinary.com/od8t271n/image/upload/v1787062663/default-cycle-SNPCodeLab.gif)

**Version:** 1.0.0  
**Status:** Online  
**Organization:** SNPCodeLab  

Enterprise ecommerce clothing backend API built with Laravel.

---

## Table of Contents

1. [Overview](#1-overview)
2. [Key Features](#2-key-features)
3. [Architecture](#3-architecture)
4. [Requirements](#4-requirements)
5. [API Documentation](#5-api-documentation)
6. [Authentication](#6-authentication)
7. [Security](#7-security)
8. [Monitoring](#8-monitoring)
9. [Contributing](#9-contributing)
10. [License](#10-license)
11. [Support](#11-support)
12. [Acknowledgments](#12-acknowledgments)

---

## 1. Overview

OutfitShop API is an enterprise-grade ecommerce clothing backend platform built with Laravel 11. It provides a robust, scalable RESTful API for managing omnichannel product catalogs, shopping carts, customer wishlists, order processing, inventory tracking, branch store operations, and financial auditing for fashion retail targeting women and men.

The platform is designed to serve the Cambodian retail market with multi-currency (USD / KHR), 10% tax-exclusive calculation engines, and localized branch logistics, while providing full capability for global ecommerce expansion.

---

## 2. Key Features

- **Product Catalog Management**: Comprehensive apparel hierarchies with categories, brands, variants, clothing sizes, colors, and media galleries.
- **Category & Variant System**: Granular SKU tracking across multi-dimensional attributes (size, color, material, fit).
- **Shopping Cart & Wishlist**: Persistent database-driven shopping cart lifecycles and customer wishlist management.
- **Order Processing & Checkout**: Idempotent transactional checkout engine with pessimistic row-locking (`SELECT ... FOR UPDATE`), automated inventory deduction, and payment capture.
- **Inventory & Stock Tracking**: Real-time 4-tier quantity lifecycle (On Hand, Reserved, Available, Incoming) with immutable stock audit ledgers.
- **Supplier & Purchasing Operations**: Complete purchase orders (PO), supplier directories, and goods receiving vouchers.
- **Secure Authentication**: Token-based authentication powered by Laravel Sanctum with strict 4-Tier Role-Based Access Control (`ADMIN`, `MANAGER`, `CASHIER`, `STAFF`).
- **Dashboard & Financial Intelligence**: Real-time asset valuation formulas (Purchased Cost Value vs. Resale Retail Value), gross margin analytics, and POS shift reconciliation.
- **Audit Logging & Tracing**: Distributed request tracing (`request_id` UUID header) across all operational flows and database mutations.

---

## 3. Architecture

OutfitShop API follows a modular three-tier service architecture designed for high throughput, data integrity, and strict separation of concerns:

```mermaid
graph TD
    Client[Web Frontend / POS Terminal / Mobile App] -->|HTTPS JSON Requests| Gateway[HTTP Kernel & Global Middleware]
    
    subgraph Middleware Pipeline
        Gateway --> Tracing[RequestIdTracer Middleware]
        Tracing --> RateLimit[Dynamic Rate Limiting]
        RateLimit --> SanctumAuth[Sanctum Token Authentication]
        SanctumAuth --> RbacCheck[Role & Permission Gatekeeper]
    end
    
    subgraph Controller & Service Layer
        RbacCheck --> OrderCtrl[OrderController]
        RbacCheck --> CartCtrl[CartController]
        RbacCheck --> ProductCtrl[ProductController]
        RbacCheck --> InventoryCtrl[InventoryService]
        RbacCheck --> AuthCtrl[AuthController]
    end
    
    subgraph Data & Storage Layer
        OrderCtrl --> DB[(PostgreSQL 16 Database)]
        CartCtrl --> DB
        ProductCtrl --> DB
        InventoryCtrl --> DB
        AuthCtrl --> DB
        DB --> AuditTrail[(Immutable Audit Ledger)]
    end
    
    subgraph Output Formatting
        OrderCtrl --> ApiResponse[ApiResponse Engine RFC 7807]
        CartCtrl --> ApiResponse
        ProductCtrl --> ApiResponse
        ApiResponse -->|Standardized JSON Envelope| Client
    end
```

### 4-Tier RBAC Access Hierarchy

```mermaid
flowchart LR
    L1[Level 1: Public / Guest] -->|Read-only catalog, cart, wishlist, auth| L2[Level 2: Cashier & Staff]
    L2 -->|POS checkout, shift management, order search| L3[Level 3: Manager]
    L3 -->|Inventory adjustments, purchase orders, pricing tiers| L4[Level 4: Administrator]
    L4 -->|Employee management, system logs, role overrides| Root[Full Access]
```

---

## 4. Requirements

- **PHP**: >= 8.2 (PHP 8.3 recommended)
- **Database**: PostgreSQL >= 15 or MySQL >= 8.0
- **Extensions**: `pdo_pgsql` / `pdo_mysql`, `openssl`, `mbstring`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `curl`
- **Dependency Manager**: Composer >= 2.6
- **Node.js / NPM** (optional, for asset compilation): Node >= 18.x

---

## 5. API Documentation

### Public Catalog Endpoints (Level 1: Public / Guest)

| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `GET` | `/` | Root API discovery gateway |
| `GET` | `/api/v1/health` | Service health status check |
| `GET` | `/api/v1/status` | System version and database connectivity probe |
| `GET` | `/api/v1/products` | Browse product catalog with pagination and filters |
| `GET` | `/api/v1/products/{id}` | Retrieve individual product details and variants |
| `GET` | `/api/v1/categories` | List product categories |
| `GET` | `/api/v1/brands` | List apparel brands |
| `GET` | `/api/v1/clothing-sizes` | List clothing sizes (XS, S, M, L, XL, XXL, etc.) |
| `GET` | `/api/v1/colors` | List product colors and hex codes |
| `GET` | `/api/v1/cart` | Retrieve current shopping cart and items |
| `POST` | `/api/v1/cart/items` | Add product variant to shopping cart |
| `PUT` | `/api/v1/cart/items/{id}` | Update item quantity in cart |
| `DELETE` | `/api/v1/cart/items/{id}` | Remove specific item from cart |
| `DELETE` | `/api/v1/cart/clear` | Empty all items in shopping cart |
| `GET` | `/api/v1/wishlist` | Retrieve customer wishlist |
| `POST` | `/api/v1/wishlist/toggle` | Add or remove product from wishlist |

### Authentication Endpoints

| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `POST` | `/api/v1/auth/login` | Authenticate employee and obtain Sanctum Bearer token |
| `GET` | `/api/v1/auth/me` | Fetch authenticated employee profile and permissions |
| `POST` | `/api/v1/auth/logout` | Revoke active token and destroy session |
| `POST` | `/api/v1/auth/refresh` | Refresh personal access token |

### Orders & POS Checkout (Level 2: Cashier & Staff)

| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `GET` | `/api/v1/orders` | List order transactions with date and customer filters |
| `POST` | `/api/v1/orders/checkout` | Idempotent transactional checkout with stock decrement |
| `GET` | `/api/v1/orders/{id}` | Retrieve order details and receipt line items |
| `POST` | `/api/v1/orders/{id}/void` | Void order and reverse inventory allocations |
| `GET` | `/api/v1/sales/*` | Legacy backward-compatibility aliases to Order endpoints |

---

## 6. Authentication

Authentication is handled via **Laravel Sanctum** Bearer tokens passed in the `Authorization` header:

```http
Authorization: Bearer 1|pYvQ9kM...your_personal_access_token
Accept: application/json
```

### Roles and Permission Levels

1. **`ADMIN` (Level 4)**: Full administrative authority, employee lifecycle management, system-wide audits.
2. **`MANAGER` (Level 3)**: Inventory adjustments, supplier purchase orders, pricing rule configuration.
3. **`CASHIER` & `STAFF` (Level 2)**: POS transaction checkout, customer registration, shift opening/closing.
4. **`PUBLIC / GUEST` (Level 1)**: Catalog browsing, active cart mutations, wishlist management.

---

## 7. Security

- **Strict Prepared Statements**: All database operations use Eloquent ORM or parameterized PDO bindings, eliminating SQL injection.
- **Idempotent Row-Level Locking**: High-concurrency checkouts utilize `DB::transaction()` and pessimistic locking (`lockForUpdate()`) to prevent race conditions.
- **Sanctum Token Hashing**: API tokens are cryptographically hashed using SHA-256 before storage.
- **Rate Limiting**: Intelligent throttling protects public and authentication endpoints against brute-force attacks.
- **CORS Protection**: Cross-Origin Resource Sharing is locked down to verified storefront domains.

---

## 8. Monitoring

- **Distributed Tracing**: Every inbound request is tagged with a unique `X-Request-Id` UUID for end-to-end log correlation.
- **Audit Logs**: Mutations to stock, prices, roles, and order records generate automatic audit log entries.
- **Health Probes**: Automated health and readiness checks accessible via `/api/v1/health` and `/api/v1/status`.

---

## 9. Contributing

We welcome contributions to the OutfitShop API platform:

1. Fork the Project Repository.
2. Create a Feature Branch (`git checkout -b feature/NewFeature`).
3. Commit your Changes (`git commit -m 'feat: add new inventory calculation tier'`).
4. Push to the Branch (`git push origin feature/NewFeature`).
5. Open a Pull Request against the `dev` branch.

---

## 10. License

MIT License

Copyright (c) 2024–2026 SNPCodeLab

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.

---


## 12. Acknowledgments

- **Laravel Framework**: The PHP framework for web artisans.
- **Laravel Sanctum**: Featherweight authentication system for SPAs and mobile APIs.
- **PostgreSQL Global Development Group**: The world's most advanced open-source relational database.
- **SNPCodeLab Engineering Team**: Design and maintenance of the OutfitShop API platform.
