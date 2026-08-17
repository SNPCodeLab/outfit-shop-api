# Retail Clothing Store MIS & POS — API Delivery Package

Welcome to the official API Integration Package for the **Retail Clothing Store MIS & Point-of-Sale System (SS-MIS / CSMS)**.

## 🌐 Environments & Base URLs

| Environment | Base Gateway URL | Database Engine | Access Type |
|---|---|---|---|
| **Production Gateway** | `https://api.kesararamwithdigital.tech/api/v1` | PostgreSQL 16 (Neon Cloud) | Live SSL Protected |
| **Local Development** | `http://127.0.0.1:8000/api/v1` | PostgreSQL 16 / SQLite | Local Docker / Artisan |
| **Gateway Discovery** | `https://api.kesararamwithdigital.tech/` | N/A | Public Routing Map |

---

## 📁 Package Directory Structure

```text
📁 API-Delivery-Package/
├── 📄 README.md                # This setup & architectural guide
├── 📄 postman_collection.json  # Complete Postman v2.1 test collection
├── 📄 openapi_spec.yaml        # OpenAPI 3.0 / Swagger schema specification
├── 📄 error_codes.md           # Machine-readable error dictionary & HTTP codes
├── 📄 auth_flow.md             # 4-Tier RBAC & Sanctum token lifecycle
├── 📄 test_credentials.md      # Staging & local demo employee credentials
└── 📄 example_requests.md      # Copyable cURL & TypeScript/Axios code snippets
```

---

## ⚡ Quickstart for Frontend Developers (3 Minutes)

### 1. Configure Environment Variable
In your Next.js / Vite root directory, create or update `.env.local`:

```env
NEXT_PUBLIC_API_BASE_URL=https://api.kesararamwithdigital.tech/api/v1
```

### 2. Standard Request Headers
Always attach these headers in your HTTP client:

```http
Content-Type: application/json
Accept: application/json
Authorization: Bearer <sanctum_token>
X-Request-Id: <uuid_v4>
```

### 3. Core Business Policy Rules
1. **Tax Policy**: `10.00% Tax-Exclusive (VAT)` applied on net subtotal (`Subtotal = Items - Discount`, `Tax = round(Net * 0.10, 2)`).
2. **Exchange Rate**: Fixed retail rate `1 USD = 4,100 KHR`.
3. **Pessimistic Locking**: `POST /sales/checkout` uses row locking on `product_variants`. Always pass `X-Idempotency-Key` to avoid double-charging on network retries.
4. **Brand Scope**: The brand name *"KhmeRiel (Clothing MIS & POS)"* is strictly for the frontend user interface; backend endpoints remain domain-agnostic.

---

## 🧪 Automated Test Suite Verification

The backend API test suite validates all 135+ endpoints with 100% pass rate:

```bash
php artisan test
# Tests: 307 passed (0 failed, 0 errors)
```

---

## 👥 Engineering & Support Contacts
- **Backend Architecture Lead**: `support@kesararamwithdigital.tech`
- **GitHub Repository**: [github.com/SNPbuilds/csms-backend-api](https://github.com/SNPbuilds/csms-backend-api)
