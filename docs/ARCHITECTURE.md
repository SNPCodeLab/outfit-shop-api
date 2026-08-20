# System Architecture

OutfitShop-Backend-API is a **Monolithic, Headless REST API** built on Laravel 12. It follows a clean separation of concerns, ensuring that business logic is decoupled from HTTP transport and data persistence.

---

## 1. Request Lifecycle

```text
Client (Web/POS) 
  ──► Vercel/Public Entry (api/index.php)
    ──► API Routes (routes/api.php)
      ──► Middleware (Auth, RBAC, Security)
        ──► Form Request (Validation)
          ──► Controller (Request Handling)
            ──► Service (Business Logic & Transactions)
              ──► Repository/Model (Data Access)
                ──► ApiResponse (Unified JSON Envelope)
```

---

## 2. Responsibility Matrix

To maintain a clean and scalable codebase, each layer has a strict set of responsibilities.

| Layer | Responsibility | Forbidden Actions |
|---|---|---|
| **Routes** | Define URLs, HTTP verbs, and middleware. | No logic or database queries. |
| **Middleware** | Auth, RBAC, Rate Limiting, Logging. | No business mutations. |
| **Form Request** | Data validation and 422 error shaping. | No data persistence. |
| **Controller** | Orchestrate services, return `ApiResponse`. | No direct SQL or fat transactions. |
| **Service** | **Core Domain Logic**. ACID transactions, POS math. | No HTTP session or redirect logic. |
| **Model** | Data relationships, casts, and scopes. | No API response formatting. |
| **ApiResponse** | Unified JSON structure and error mapping. | No database operations. |

---

## 3. Directory Structure

```text
app/
  Http/
    Controllers/Api/V1/  Versioned HTTP adapters.
    Middleware/          Sanctum, RBAC, and Security headers.
    Requests/            Validation rules per domain.
    Response/            ApiResponse envelope factory.
  Models/                Eloquent entities (PostgreSQL).
  Services/              Domain logic (POS, Inventory, KHQR).
  Repositories/          (Optional) Isolated data queries.
config/                  Framework and custom API settings.
database/
  migrations/            PostgreSQL schema definitions.
  seeders/               Master data (Roles, Sizes, Colors).
docs/                    Project documentation and API guides.
routes/
  api.php                All /api/v1 routes.
tests/
  Feature/               HTTP and RBAC integration tests.
  Unit/                  Isolated logic tests.
```

---

## 4. API Versioning Strategy

All public APIs are prefixed with `/api/v1`. 
- **Breaking Changes**: Handled by introducing `/api/v2` with new controllers in `app/Http/Controllers/Api/V2/`.
- **Backward Compatibility**: V1 is maintained until a full deprecation cycle is completed.

---

## 5. Serverless Optimization (Vercel)

The application is hardened for serverless deployment:
- **Statelessness**: Sessions and cache use the `database` driver.
- **Bootstrapping**: Custom `api/index.php` handles read-only filesystems by redirecting cache to `/tmp`.
- **Database**: Uses Neon PostgreSQL with pooler connection strings to manage high-concurrency connections.

---
Copyright 2024–2026 SNPCodeLab. All rights reserved.
