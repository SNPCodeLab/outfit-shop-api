---
name: csms-backend-enterprise-delivery
description: >
  Comprehensive Enterprise API Architecture, Development, Testing, and Frontend Handover Workflow
  for Retail Clothing Store MIS & POS Systems (CSMS / SS-MIS). Covers 4-Tier RBAC, distributed tracing (request_id),
  idempotent transactional checkout with pessimistic row-locking, stock before/after audit ledger,
  automated load/stress testing benchmarks, disaster recovery SLAs, and full frontend delivery packaging.
---

# CSMS / SS-MIS Enterprise Backend Architecture & Handover Workflow

Authoritative architectural reference and standard operating procedure for the Retail Clothing Store Management System (SS-MIS / CSMS) backend API.

---

## 🏛️ 1. Core Architecture & System Profile

- **System Type**: Transaction Processing System (TPS / OLTP) with integrated Management Information System (MIS) reporting.
- **Architecture**: Monolithic, Headless RESTful API Backend (100% JSON).
- **Technology Stack**: Laravel 11 + PostgreSQL 16 (Neon Cloud) + Redis 7 + Cloudinary CDN.
- **Production Gateway URL**: `https://api.kesararamwithdigital.tech/api/v1`
- **Database Footprint**: 48 Normalized Relational Tables across 6 Core Operational Domains:
  1. Inventory & Matrix Management (12 tables)
  2. Supplier & Purchasing Pipeline (8 tables)
  3. POS & Cash Register Shifts (10 tables)
  4. Omnichannel Logistics & Transfers (8 tables)
  5. Role-Based Access Control (RBAC) & Audit Logs (6 tables)
  6. Financial Valuation & MIS Reporting (4 tables)

---

## 🔐 2. Security, Access Tiers & Distributed Tracing

### 2.1 4-Tier Role-Based Access Control (RBAC)
- **Level 0 (PUBLIC / GUEST)**: Read-only catalog discovery (`/products`, `/categories`, `/variants`, `/brands`).
- **Level 1 (STAFF)**: Floor & warehouse stock lookup, barcode scanner queries (`/stock-movements`, `/variants/barcode/{code}`).
- **Level 2 (CASHIER)**: Touch POS register, checkout transactions, customer creation, shift open/close with Z-Reports (`/sales/checkout`, `/shifts/open`, `/shifts/close`).
- **Level 3 (MANAGER)**: Full catalog CRUD, purchase orders, restock forecasting, supplier management, void sales, stock adjustments (`/purchases`, `/suppliers`, `/stock-movements/adjust`).
- **Level 4 (ADMIN)**: Master console, staff accounts, system telemetry, security audit trail (`/employees`, `/audit-logs`, `/dashboard/stats`).

### 2.2 Standard Response Envelope with Correlation ID
Every JSON response contains a unique UUID v4 `request_id` for distributed log tracing:

```json
{
  "success": true,
  "message": "Human readable action summary",
  "data": { ... },
  "request_id": "9b1deb4d-3b7d-4bad-9bdd-2b0d7b3dcb6d"
}
```

```json
{
  "success": false,
  "message": "Insufficient stock for SKU [SHIRT-BLK-M].",
  "error_code": "ERR_INSUFFICIENT_STOCK",
  "errors": { "items.0.quantity": ["Quantity exceeds current physical availability"] },
  "request_id": "9b1deb4d-3b7d-4bad-9bdd-2b0d7b3dcb6d"
}
```

---

## 💳 3. Financial & Inventory Business Logic Rules

1. **Tax Policy (10.00% Tax-Exclusive VAT)**:
   - `Net Subtotal = Items Total − Discount`
   - `Tax Amount = round(Net Subtotal * 0.10, 2)`
   - `Grand Total = Net Subtotal + Tax Amount`
2. **Fixed Exchange Rate**: `1 USD = 4,100 KHR` for dual-currency cash tendered and change calculations.
3. **Pessimistic Row-Locking**: `POST /sales/checkout` wraps operations in `DB::transaction()` using `ProductVariant::where(...)->lockForUpdate()` to eliminate overselling and race conditions.
4. **Idempotency Key Guard**: Accepts `X-Idempotency-Key` or `idempotency_key` payload. Duplicate network retries return the existing sale (`HTTP 200`) without deducting stock twice.
5. **Stock Movement Ledger**: Every sale, void, purchase receipt, and manual adjustment stamps `stock_before` and `stock_after` quantities in `stock_movements`.
6. **Graceful Audit Logging**: `AuditLogService::log()` uses a `try/catch` fallback so logging glitches never crash financial transactions.

---

## ⚡ 4. Telemetry, APM & Load Testing Standards

### 4.1 Production Performance Targets
- **Read Operations**: `< 200 ms` (Current average: `28.45 ms`).
- **Write Operations**: `< 500 ms` (Current average: `46.10 ms`).
- **Global Error Rate**: `< 1.00%` (Current: `0.12%`).
- **Rate Limits**:
  - `/auth/login`: `10 requests / minute` (anti-brute force).
  - General Authenticated APIs: `120 requests / minute`.

### 4.2 Telemetry Query Endpoint
Admins can monitor live metrics via `GET /api/v1/dashboard/stats`:
- Average / max latency duration.
- Auth failure counter (`auth_failures_401`).
- Rate limit hit counter (`rate_limit_hits_429`).
- Most called endpoints & slowest endpoints ranking.

---

## 📦 5. Frontend Integration Delivery Package

Whenever handing off this API to frontend teams, provide the standardized 7-document bundle:

```text
📁 API-Delivery-Package/
├── 📄 README.md                # Environment setup & business rules guide
├── 📄 postman_collection.json  # Complete Postman v2.1 test collection
├── 📄 error_codes.md           # Machine-readable error code dictionary
├── 📄 auth_flow.md             # 4-Tier RBAC & Sanctum token lifecycle
├── 📄 test_credentials.md      # Demo employee credentials across all roles
└── 📄 example_requests.md      # Copyable cURL & TypeScript/Axios code snippets
```

---

## 🔄 6. Disaster Recovery & Incident SLAs

| Severity | Response Time | Target Resolution | Escalation Path |
|---|:---:|:---:|---|
| **P0 (Critical)** | **15 min** | **< 2 hours** | Lead Architect $\rightarrow$ CTO $\rightarrow$ Executive |
| **P1 (High)** | **30 min** | **< 4 hours** | Backend Lead $\rightarrow$ DBA |
| **P2 (Medium)** | **2 hours** | **< 24 hours** | Assigned On-Call Engineer |
| **P3 (Low)** | **1 business day** | **< 3 business days** | Standard Sprint Backlog |

### Emergency Rollback Steps:
1. `git reset --hard <STABLE_COMMIT_HASH>`
2. `php artisan migrate:rollback --step=1 --force` (if schema was altered)
3. `php artisan optimize:clear && php artisan config:cache && php artisan route:cache`
4. `curl -I https://api.kesararamwithdigital.tech/api/v1/health` (must return `HTTP 200 OK`)
