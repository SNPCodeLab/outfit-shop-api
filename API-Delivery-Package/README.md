<div align="center">
  <img src="https://res.cloudinary.com/od8t271n/image/upload/v1787064621/bleu-SNPCodeLab.png" alt="OutfitShop Primary Logo" width="280" />
  <h1>OutfitShop API — Delivery Package</h1>
  <p>Official Integration & Handoff Package for <strong>OutfitShop Ecommerce Clothing API</strong>.</p>
</div>

---

## 🎨 Brand Assets Directory

| Asset | CDN URL |
| :--- | :--- |
| **Primary Logo** | `https://res.cloudinary.com/od8t271n/image/upload/v1787064621/bleu-SNPCodeLab.png` |
| **Animated Cycle** | `https://res.cloudinary.com/od8t271n/image/upload/v1787062663/default-cycle-SNPCodeLab.gif` |
| **Secondary Logo** | `https://res.cloudinary.com/od8t271n/image/upload/v1787062664/bleu-SNPCodeLab.gif` |
| **Vector Icon** | `https://res.cloudinary.com/od8t271n/image/upload/v1787062662/anime-SNPCodeLab.svg` |
| **Brand Video** | `https://res.cloudinary.com/od8t271n/video/upload/v1787062665/default-cycle-SNPCodeLab.mp4` |

---

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
├── 📄 postman_collection.json  # Complete OutfitShop Postman v2.1 test collection
├── 📄 openapi_spec.json        # OpenAPI 3.0 JSON specification
├── 📄 openapi_spec.yaml        # OpenAPI 3.0 / Swagger YAML schema specification
├── 📄 error_codes.md           # Machine-readable error dictionary & HTTP codes
├── 📄 auth_flow.md             # 4-Tier RBAC & Sanctum token lifecycle
├── 📄 test_credentials.md      # Staging & local demo employee credentials
└── 📄 example_requests.md      # Copyable cURL & TypeScript/Axios code snippets
```

---

## ⚡ Quickstart for Frontend Developers

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
3. **Pessimistic Locking**: `POST /orders/checkout` (and legacy alias `POST /sales/checkout`) uses row locking on `product_variants`. Always pass `X-Idempotency-Key` to avoid double-charging on network retries.
4. **Cart & Wishlist**: Public endpoints `GET /cart`, `POST /cart/items`, `GET /wishlist`, `POST /wishlist/toggle` are available for guest & logged-in user experiences.

---

## 👥 Engineering & Support Contacts
- **Backend Architecture Lead**: `support@kesararamwithdigital.tech`
- **Package Name**: `outfitshop/api`
