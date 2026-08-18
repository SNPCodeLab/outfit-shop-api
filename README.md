# OutfitShop MIS & POS API

![OutfitShop Logo](https://res.cloudinary.com/od8t271n/image/upload/v1787062663/default-cycle-SNPCodeLab.gif)

**Version:** 1.2.0  
**Status:** Operational (Production)  
**Organization:** SNPCodeLab  
**Gateway Domain**: [https://api.kesararamwithdigital.tech/api/v1](https://api.kesararamwithdigital.tech/api/v1)

---

## 1. Overview
OutfitShop API is an enterprise-grade ecommerce clothing backend platform built with **Laravel 12**. It provides a robust, scalable RESTful environment for managing omnichannel product catalogs, persistent shopping carts, idempotent POS checkout, and multi-currency financial auditing (USD / KHR).

The platform is purpose-built for the Cambodian retail market, featuring a specialized **10% tax-exclusive calculation engine** and a strict **4-tier Role-Based Access Control (RBAC)** architecture.

---

## 2. Key Enterprise Features
- **4-Tier RBAC Architecture**: Granular permission guards across `PUBLIC`, `CASHIER/STAFF`, `MANAGER`, and `ADMIN`.
- **Idempotent POS Engine**: High-concurrency checkout using pessimistic row-locking (`SELECT ... FOR UPDATE`) and `X-Idempotency-Key` headers to prevent duplicate transactions.
- **Dynamic 2D Variant Matrix**: Real-time SKU tracking across Size $\times$ Color dimensions with instant barcode lookup.
- **Financial Intelligence**: Real-time asset valuation formulas (Cost vs. Resale), gross margin analytics, and POS shift reconciliation (Z-Reports).
- **Audit Logging & Tracing**: Distributed request tracing (`request_id` UUID) and immutable audit ledgers for every significant database mutation.
- **Vercel Serverless Optimized**: Hardened boot sequence for serverless environments with redirected bootstrap caching and database-driven sessions.

---

## 3. Technology Stack
- **Framework**: Laravel 12 (Hardened for Vercel)
- **Database**: PostgreSQL 17 (Neon Cloud)
- **Media CDN**: Cloudinary Edge Service
- **Authentication**: Laravel Sanctum (Token-based)
- **Security**: SHA-256 Token Hashing, Rate Limiting, and CORS protection.

---

## 4. API Architecture & Access Tiers

| Tier | Role | Access Level | Primary Responsibilities |
| :--- | :--- | :--- | :--- |
| **Level 1** | **Public / Guest** | Read-Only | Browse catalog, manage cart, wishlist. |
| **Level 2** | **Cashier & Staff** | Transactional | POS checkout, shift management, barcode lookup. |
| **Level 3** | **Manager** | Operational | Catalog CRUD, inventory adjustments, purchase orders. |
| **Level 4** | **Administrator** | Master Command | Employee management, system audits, broadcast alerts. |

---

## 5. Development & Deployment

### 🛡 Git Workflow Protocol
To maintain production stability, the following rules apply:
1.  **Branching**: Work is performed in feature-specific staging branches (e.g., `fix-issue-name`).
2.  **Staging**: Changes are merged into the `docs` branch after validation.
3.  **Production**: The `main` branch is synchronized with `docs` for final deployment.

### 🚀 Production Requirements (Vercel)
Ensure the following variables are configured in the environment:
- `APP_KEY`: Laravel encryption key.
- `DATABASE_URL`: Full PostgreSQL connection string (SSL required).
- `SESSION_DRIVER`: Must be set to `database`.
- `CACHE_STORE`: Must be set to `database`.

---

## 6. Documentation & Resources
- **Developer Guide**: [Help Centre Knowledge Base](/api/v1/guide)
- **API Spec**: [OpenAPI 3.0 / Swagger](/api/v1/openapi.json)
- **Postman Collection**: Located in `/postman/OutfitShop_Master_Collection.json`
- **Entity Specification**: Refer to `PRODUCT_DOCUMENT.md` for the master data matrix.

---

## 7. Support
For technical inquiries or infrastructure access, please contact the **SNPCodeLab Engineering Team** at `support@kesararamwithdigital.tech`.

---
© 2024–2026 SNPCodeLab. All rights reserved.
