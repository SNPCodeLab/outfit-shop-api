---
name: ssmis-architecture-docs
description: >
  IS academic/professional documentation standards for the SS-MIS / CSMS-API project.
  Use this skill whenever writing, updating, or improving the CSMS_SYSTEM_ARCHITECTURE_REPORT.md,
  README.md, API_DOCS.md, or any technical documentation for the Store Stock & POS project.
  Covers IS Framework, TPS/MIS classification, relational schema/subschema, three-tier
  networking, Porter's Value Chain, ethics/data-protection, enterprise pattern scoping,
  and academic terminology explanations for students and frontend teams.
---

# SS-MIS Architecture Documentation Skill

---

## 1. Project Identity

| Property | Value |
|---|---|
| **System Name** | Store Stock & Point-of-Sale Information System (SS-MIS / CSMS-API) |
| **IS Classification** | Transaction Processing System (TPS / OLTP) + MIS Reporting |
| **Architecture** | Monolithic, Headless REST API Backend |
| **Framework** | Laravel 11 / PHP 8.3 |
| **Primary Database** | PostgreSQL — Neon Cloud Managed Serverless |
| **Alternative Database** | Oracle SQL — via yajra/laravel-oracle (on-premise enterprise only) |
| **Auth** | Laravel Sanctum Bearer Tokens |
| **RBAC** | Spatie Laravel-Permission (4 roles: ADMIN, MANAGER, CASHIER, STAFF) |
| **Deployment** | Vercel Serverless |
| **Production API** | https://api.kesararamwithdigital.tech |
| **GitHub** | https://github.com/SNPbuilds/csms-backend-api |

---

## 2. Academic Terminology & Positioning — Explanation Rules

This is the most important skill section. Whenever a user asks "what does X mean?", "explain
this section", or "how do I use this in my exam/report/defense?", follow this pattern:

### 2.1 The Master Academic Description (use in Abstract, Conclusion, Title Slide)

> *"CSMS-API is engineered as a **Monolithic, Headless REST API Backend** implementing a
> **Transaction Processing System (TPS / OLTP)** with an embedded **Management Information
> System (MIS)** reporting layer, backed by **PostgreSQL on Neon Cloud**, and enforcing
> 4-tier **Role-Based Access Control (RBAC)** via **Laravel Sanctum** and **Spatie
> Permissions**, while adhering to **OWASP Top 10** security standards."*

### 2.2 Term-by-Term Breakdown Rules

When explaining any IS academic term, ALWAYS follow this 3-part structure:
1. **Plain English definition** — what it means in simple words
2. **Where in the code** — cite the specific file, endpoint, or class
3. **Why it matters** — the academic/business justification

#### Term Definitions for This Project

| Term | Plain English | Code Evidence | Why It Matters |
|---|---|---|---|
| **Monolithic** | All code in one Laravel repo; not split into microservices | Single `app/` directory; all services in `app/Services/` | Guarantees ACID transactions across domains; no distributed network latency |
| **Headless** | No HTML rendered — only JSON output | No views except `welcome.blade.php` stub; all responses from `BaseApiController` | Any client (React, Flutter, POS hardware) can consume the API |
| **REST API** | Standard HTTP verbs under versioned routes | `routes/api.php` — all `/api/v1/` prefixed routes | Universal interoperability with any HTTP client |
| **TPS / OLTP** | Real-time routine transaction processing | `POSService::checkout()`, `DB::transaction()`, `lockForUpdate()` | Prevents overselling, ensures immediate stock accuracy |
| **MIS Reporting** | Aggregated management summaries from TPS data | `GET /api/v1/dashboard/stats`, `GET /api/v1/variants/low-stock` | Enables informed manager decisions without raw SQL access |
| **RBAC** | Role-gated data access per job function | `CheckRole.php` middleware, Spatie `role:MANAGER,ADMIN` | Principle of Least Privilege — cashier cannot see employee salaries |
| **OWASP Top 10** | International web security standard compliance | Bcrypt cost 12, parameterized Eloquent queries, rate limiting | Academic credibility + real-world security posture |
| **3NF Relational Model** | No transitive dependencies; FK-constrained tables | 22 migration files in `database/migrations/` | Financial data integrity; referential consistency |
| **Subschema** | Role-filtered view of the global schema | RBAC middleware + `$hidden` on Eloquent models | Different users see different data — not one flat view |
| **Three-Tier Architecture** | Thin client → App Server → Database | Web client → Vercel/Laravel → Neon PostgreSQL | Security: clients never touch the DB directly |
| **Porter's Value Chain** | Business activities that create customer value | Inbound=purchases, Operations=catalog, Outbound=sales | Justifies every feature as a business-value driver |
| **TPS → MIS → DSS → EIS** | Information system evolution pyramid | TPS+MIS implemented; DSS/EIS on roadmap | Shows architectural thinking beyond current scope |

### 2.3 How to Answer "What is Section 12 / Academic Terminology?"

When asked to explain the Academic Terminology section:
- Explain it is the "exam elevator pitch" — 1 sentence that uses all correct IS vocabulary
- Break down each term using the table above (3-part: meaning + code + justification)
- Contrast casual vs. academic language:
  - Casual: "I made a Laravel API for a clothing store"
  - Academic: "A Monolithic Headless TPS/OLTP REST API with embedded MIS reporting and RBAC"
- Tell the user WHERE to use it: Abstract, Conclusion, Title Slide subtitle, Q&A Defense answer

---

## 3. IS Academic Framework Rules

### 3.1 System Classification Hierarchy

```
TPS / OLTP  ──► IMPLEMENTED (POS checkout, purchase receiving, stock adjustments)
    ↓
MIS         ──► IMPLEMENTED (dashboard stats, low-stock report, audit logs, sales history)
    ↓
DSS         ──► ROADMAP (AI dead-stock alerts, sales forecasting, reorder prediction)
    ↓
EIS         ──► ROADMAP (executive KPI dashboard, YoY margin analysis)
    ↓
Expert System ► ROADMAP (automated reorder rules based on velocity)
```

### 3.2 IS Framework Model (I→P→O + Feedback + Control)

Mapped to real endpoints — never use generic examples:

| Component | Description | Real Code Reference |
|---|---|---|
| **INPUT** | Barcode scan, sale items, customer data, payment | `GET /api/v1/variants/barcode/{barcode}` |
| **INPUT** | Purchase order (supplier + variants + costs) | `POST /api/v1/purchases` request body |
| **PROCESS** | Stock validation, checkout logic, stock deduction | `app/Services/POSService.php::checkout()` |
| **PROCESS** | Auth/role verification before every mutation | `app/Http/Middleware/CheckRole.php` |
| **OUTPUT** | JSON invoice with invoice_no, totals, change due | `POST /api/v1/sales/checkout` response |
| **OUTPUT** | Low-stock variant list for manager review | `GET /api/v1/variants/low-stock` |
| **FEEDBACK** | Low-stock triggers purchase order creation | `GET /variants/low-stock` → `POST /purchases` |
| **FEEDBACK** | 422/403 errors correct invalid client input | All validated write endpoints |
| **CONTROL** | RBAC middleware restricts write access by role | `middleware('role:MANAGER,ADMIN')` in `routes/api.php` |
| **CONTROL** | `lockForUpdate()` prevents race condition oversell | `app/Services/POSService.php` line 57 |
| **CONTROL** | Rate limiting — 10/min on auth, 120/min on API | `middleware('throttle:10,1')` |

---

## 4. Database Documentation Rules

### 4.1 Consistency Rule (Non-Negotiable)

- PostgreSQL = **primary/production** database — always say this first
- Oracle SQL = **alternative/on-premise enterprise** engine — always say "alternative"
- SQLite = **local testing only** — never mention as production option

### 4.2 Relational Model Documentation

- Global schema: 22 tables across 4 domains (Catalog, Sales, Purchasing, Security/Audit)
- Subschema: RBAC-enforced visibility per role (always show as a table matrix)
- Normalization: 3NF — cite FK constraints in migrations as evidence
- ACID compliance: `DB::transaction()` + `lockForUpdate()` in both Service classes

### 4.3 Three-Tier Architecture Rule

Always label as: **Thin Client → Laravel App Server → Neon PostgreSQL**

| Tier | Label | Component | Rule |
|---|---|---|---|
| 1 | Presentation | Web / Mobile / POS hardware | Zero business logic lives here |
| 2 | Application | Laravel 11 on Vercel (serverless) | ALL business rules, RBAC, validation |
| 3 | Data | Neon Cloud PostgreSQL | Storage, integrity, row-level locks |

---

## 5. Enterprise Pattern Scoping Rules

### What SS-MIS IS (say exactly this in reports):
- ✅ **Full POS Engine** — barcode-to-receipt with automatic stock deduction
- ✅ **ERP Operations Module (Partial)** — inventory, purchasing, stock movements
- ✅ **ERP Finance Module (Partial)** — revenue tracking, cost price, payment records
- ✅ **CRM Module (Partial)** — customer registry, loyalty points, purchase history
- ✅ **Audit Trail / Compliance System** — immutable log of all state changes
- ✅ **RBAC / Identity Management** — 4-tier Spatie permission system

### What SS-MIS IS NOT (always be explicit):
- ❌ Full ERP (no accounting/GL, no payroll, no manufacturing MES)
- ❌ Full CRM (no email campaigns, no lead/opportunity pipeline)
- ❌ SCM / WMS (no warehouse bins, no carrier shipping, no pick-pack-ship)
- ❌ Business Intelligence / OLAP (no data warehouse, no multidimensional cubes)
- ❌ eCommerce (no end-customer shopping cart or online checkout)
- ❌ Accounting / General Ledger (revenue recorded but no double-entry bookkeeping)

### Correct Positioning Statement for Reports:
> *"SS-MIS is a TPS/OLTP system with embedded MIS reporting, implementing domain modules
> analogous to an ERP Operations module and a basic CRM module within the scope of a
> single-store retail clothing POS. It is not a full ERP, CRM, SCM, or BI platform."*

---

## 6. Ethics & PII Documentation Rules

### PII Inventory (always list these in ethics sections):

| Table | PII Fields | Visibility | Standard |
|---|---|---|---|
| `employees` | employee_name, email, phone, gender, position | ADMIN only | RBAC-gated |
| `employees` | password_hash, salary | **Hidden from ALL responses** | Eloquent `$hidden` |
| `customers` | customer_name, email, phone, address, gender | Cashier+ | Consent required |
| `audit_logs` | ip_address, user_agent | Manager+ | Inform employees |
| `api_logs` | ip_address, user_id | Admin only | Behavioral monitoring |

### Security Standards to Reference:
- Password hashing: Bcrypt cost factor 12 — **NIST SP 800-63B**
- Token storage: SHA-256 hash in DB — **OWASP ASVS**
- Transmission: HTTPS/TLS 1.3, Authorization: Bearer header — **RFC 6750**
- Access control: RBAC middleware on all private endpoints — **OWASP Top 10 A01**
- Input validation: Laravel validation rules before DB execution — **OWASP Top 10 A03**

### GDPR/PDPA Guidance:
- Soft deletes satisfy operational audit needs; hard delete for personal data on formal request
- Data retention: 7 years for financial records; 1 year for behavioral logs (api_logs)
- Third-party hosting: Neon Cloud (SOC 2 Type II) + Vercel (SOC 2 Type II) only

---

## 7. Porter's Value Chain Mapping

### Primary Activities

| Activity | SS-MIS Module | Key Endpoint(s) |
|---|---|---|
| **Inbound Logistics** | Supplier & Purchase Order management | `POST /api/v1/purchases`, `GET /api/v1/variants/low-stock` |
| **Operations** | Catalog & inventory management | `/api/v1/products`, `/api/v1/variants`, `/api/v1/stock-movements/adjust` |
| **Outbound Logistics** | POS checkout & payment | `POST /api/v1/sales/checkout`, `POST /sales/{id}/void` |
| **Marketing & Sales** | Customer registry + loyalty lock-in | `POST /api/v1/customers`, `CUSTOMERS.total_points` field |
| **Customer Service** | After-sale support & invoice retrieval | `GET /api/v1/sales/{id}`, audit trail |

### Support Activities

| Activity | SS-MIS Feature |
|---|---|
| **Firm Infrastructure** | Health checks, dashboard traffic stats, Vercel auto-scaling |
| **Human Resource Mgmt** | Employee CRUD (Admin), accountability via `employee_id` on every transaction |
| **Technology Development** | API versioning (`/v1/`), dual DB engine, open headless JSON architecture |
| **Procurement** | Supplier registry CRUD + cost-price tracking per variant |

---

## 8. Frontend Team Learning Reference

This section is for frontend developers integrating with the API.

### 8.1 Authentication Flow
```
1. POST /api/v1/auth/login → get access_token (Bearer token)
2. Store token: localStorage.setItem('auth_token', token)
3. Every request: Authorization: Bearer <token> header
4. On 401 response: clear token, redirect to login
5. POST /api/v1/auth/logout → invalidates token server-side
```

### 8.2 What Your Role Gets You
```
CASHIER  → Can: scan barcode, checkout sale, create customer, view sales
MANAGER  → All of CASHIER + catalog CRUD, void sales, stock adjust, suppliers, purchases, dashboard
ADMIN    → All of MANAGER + employee management, user creation, audit logs
STAFF    → Read-only catalog only
```

### 8.3 Error Response Shape (Always Consistent)
```json
{
  "success": false,
  "message": "Human-readable description",
  "error_code": "ERR_FORBIDDEN",
  "errors": null
}
```
- `401` → Token missing or invalid — redirect to login
- `403` → Token valid but role insufficient — show "Access Denied"
- `422` → Validation failed — `errors` object contains field-level messages
- `429` → Rate limited — show "Too many requests, wait 1 minute"

### 8.4 Key Public Endpoints (No Token Required)
```
GET /api/v1/categories              → Product categories
GET /api/v1/products                → Full catalog
GET /api/v1/variants                → All SKUs with stock levels
GET /api/v1/variants/barcode/{code} → Barcode scanner lookup
GET /api/v1/variants/low-stock      → Items needing restock
GET /api/v1/health                  → System status check
```

### 8.5 POS Checkout Request Shape
```json
POST /api/v1/sales/checkout
{
  "customer_id": 1,
  "items": [
    { "variant_id": 5, "quantity": 2, "discount": 0.00 }
  ],
  "payment_method": "CASH",
  "payment_amount": 50.00,
  "overall_discount": 0.00
}
```
Payment methods: `CASH`, `CARD`, `QR`, `ABA`

---

## 9. Key Files to Update When Documenting

| File | Purpose | When to Update |
|---|---|---|
| `CSMS_SYSTEM_ARCHITECTURE_REPORT.md` | Primary 13-section formal architecture document | Any structural or classification change |
| `README.md` | Quick-start reference — tech stack, classification, endpoints | Any new endpoint or config change |
| `API_DOCS.md` | Developer-facing endpoint reference with examples | Any endpoint add/remove/change |
| `.env.example` | DB engine comments (PostgreSQL primary, Oracle alternative) | Never changes unless new engine added |
| `docs/database_schema.md` | ER diagram and schema details | Any migration or schema change |
| `SECURITY_AUDIT_REPORT.md` | OWASP compliance and credential security | After any dependency update or audit |
| `.agents/skills/ssmis-architecture-docs/SKILL.md` | This skill file — agent memory for the project | After every documentation session |

---

## 10. Changelog of Improvements Made

| Date | What Changed | Why |
|---|---|---|
| 2026-08-16 | `CSMS_SYSTEM_ARCHITECTURE_REPORT.md` rewritten to 13 sections | Added IS Framework, TPS/MIS, subschema, three-tier, value chain, ethics, enterprise scope |
| 2026-08-16 | `README.md` System Classification section added | Consistency with architecture report |
| 2026-08-16 | `.env.example` three-block database config | Clarified PostgreSQL=primary, Oracle=alternative, SQLite=test-only |
| 2026-08-16 | Academic Terminology explanation pattern recorded | For student exam/defense preparation and frontend team onboarding |
| 2026-08-16 | Frontend team integration guide added to skill | For frontend developers consuming the API |
