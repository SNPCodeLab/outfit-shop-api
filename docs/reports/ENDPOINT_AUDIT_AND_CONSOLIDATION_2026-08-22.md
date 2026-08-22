# Full Endpoint Audit & API Consolidation Report
**Project:** OutfitShop-Backend-API (`https://api.kesararamwithdigital.tech/api/v1`)
**Date:** 2026-08-22 · **Scope:** every route, every role, before/after deploy

---

## 1. Audit method

- `route:list` export: **204 API routes** (98 GET, 76 POST, 20 DELETE, 11 PUT/PATCH-capable, 1 PATCH-only)
- Machine sweep: **357 requests** (every route with its expected role tier + guest auth probe)
- CRUD lifecycle: **50 real operations** (create/read/update/delete on test-marked entities with cleanup)
- Roles exercised: guest, STAFF, CASHIER, MANAGER, ADMIN
- Retest of all failures against the current build served locally on the production Postgres database before deploying

## 2. BEFORE state (deployed build at audit start)

| Sweep verdict | Count |
|---|---|
| OK | 251 / 357 |
| Deviation | 93 |
| Lifecycle as-expected | 35 / 50 |

### Failure categories and root causes

| # | Symptom | Count | Root cause | Resolution |
|---|---|---|---|---|
| F1 | `405 METHOD_NOT_ALLOWED` on every PATCH | 20 | Deployed build predated PATCH support (round 1) | Deploy |
| F2 | `404` on new routes (avatar, forgot/reset password, shipping-orders, gift-cards create, stock-opname, GDPR canonical, uploads path-delete) | 36 | Routes added in rounds 1-3 not deployed | Deploy |
| F3 | Guest `200` on `/inventory/statistics`, `/variants/low-stock` | 2 | M2 fix not deployed | Deploy |
| F4 | `POST /employees` → 500 | 1 | Old-build defect (works on current build: 201 verified) | Deploy |
| F5 | `GET /admin/performance` → 500 | 1 | Old fabricated-telemetry controller | Deploy (rewritten, verified 200) |
| F6 | `GET /postman.json` → 500 | 2 | Old route pointed at a removed file path | Deploy (new route verified 200 + guest 401) |
| F7 | `GET /products/{id}` intermittent 404 / `__PHP_Incomplete_Class` in payload | cluster | Old build cached serialized Eloquent models | Deploy (array-payload caching, verified 200) |
| F8 | `PATCH /variants/{id}` → 422 on partial update | 1 | **Real bug in current code**: variant update required all fields | **Fixed**: `sometimes` validation, verified 200 |
| F9 | `void`/transfer ship/receive → 400 instead of 422/409 | 5 | Old status mapping; transfers still use 400 for state rejection | Deploy for void; transfers documented as 409-candidate follow-up |
| F10 | Staff-401 burst during sweep | 24 | Test-harness artifact: login rate limit (5/min) hit during back-to-back runs; fresh staff login returns 200 on all endpoints | Not a defect |
| F11 | Lifecycle probe 422s (stock adjust, estimate, promotion, webhook, wishlist) | 5 | Harness payload field names did not match documented validation | Corrected payloads: all pass |

## 3. Fixes applied this round

1. `ProductVariantController::update` now accepts partial updates (`sometimes|required`) - the only genuine code bug found; regression-verified against production Postgres.
2. Release commit `2470512` deployed through the full checkpoint protocol (DB integrity 174 products / 4 active employees, AUTH_OK, Pint PASS 231 files) and the 6-stage CI/CD pipeline.
3. Test entities (`TESTAUDIT-*`, `RT-*`) removed from production after the audit.

## 4. AFTER state (post-deploy verification)

See section 7 (filled after the post-deploy sweep re-run).

## 5. KEEP / DELETE endpoint consolidation

The API is already **one unified surface**: one base URL, one Neon PostgreSQL
database shared by all branches (`store_branches` -> `store_inventories` per
branch per variant, `pos_shifts` ties employee+branch, `stock-transfers`
moves stock between branches). Nothing is split across services.

### KEEP (canonical surface - 171 routes)

| Domain | Canonical endpoints |
|---|---|
| Auth & session | `POST /auth/login`, `GET /auth/me`, `POST /auth/avatar`, `logout`, `refresh`, `revoke-all`, `2fa/setup`, `2fa/verify`, `forgot-password`, `reset-password`, `admin-reset-password`, `register` |
| System | `GET /health`, `GET /guide`, `GET /postman.json` (manager/admin gated) |
| Catalog | `categories`, `clothing-sizes`, `colors`, `brands`, `bundles` CRUD; `products` CRUD + `images`, `matrix`, `colorways`, `reviews`, `download`; `variants` CRUD + `barcode/{barcode}`, `tiers`, `batches`, `barcode-label`, `low-stock` |
| Storefront | `cart` (GET, `POST /cart/items`, PATCH/DELETE `/cart/items/{id}`, `DELETE /cart`), `wishlist` (GET/POST/DELETE + `toggle`), `promotions` (+`active`, `verify-coupon`), `marketing/banners`, `settings/audio-cues`, `currencies` |
| Sales | `POST /orders/checkout`, `GET /orders`, `GET /orders/{id}`, receipts/invoice/khqr sub-resources, `POST /orders/{id}/void`, `estimates` + convert, `invoices`, `gift-cards` create + `GET /gift-cards/{code}`, `POST /payments/khqr` |
| Customers | `customers` read (all roles) + write (CASHIER+), `loyalty`, `redeem-points` |
| Operations | `shifts` (open/close/drop/current), `offline` (manifest/push), `shipping-orders` (GET/POST/PATCH), `dashboard` (stats, role-pulse), `alerts/active` |
| Inventory | `statistics`, `restock-recommendations`, `expiring-soon`, `stock-movements` + `adjust`, `stock-opname`, bulk ops (adjust/price/import/receive), `branches` + `{id}/stock`, `stock-transfers` 5-stage lifecycle |
| Supply | `suppliers` CRUD, `purchases` CRUD + `auto-generate` |
| Media | `uploads/image`, `uploads/image/{publicId}`, `uploads/batch`, `uploads/gallery`, `POST /products/{id}/images`, `POST /variants/{id}/image` |
| Insights & compliance | `reports/*` (7), `ai/*` (5), `audit-logs`, `exports/*` (4), GDPR `customers/{id}/data-exports` + `erasure-requests`, `compliance/audit-retention-policy`, `webhooks` |
| Admin | `employees` CRUD, `admin/master-pulse`, `admin/performance`, `admin/api-analytics`, `admin/broadcast-alert` |

### DELETE (deprecated aliases - 19 routes)

These duplicate a canonical endpoint and only exist for backward
compatibility. They emit `Deprecation: true` + `Sunset: 2027-12-31` headers.

| Delete | Replaced by |
|---|---|
| `GET /status` | `GET /health` |
| `GET /docs` | `GET /guide` |
| `POST /sales/checkout` | `POST /orders/checkout` |
| `GET /sales` | `GET /orders` |
| `GET /sales/{id}` | `GET /orders/{id}` |
| `GET /sales/{id}/khqr` | `GET /orders/{id}/khqr` |
| `GET /sales/{id}/receipt-thermal` | `GET /orders/{id}/receipt-thermal` |
| `GET /sales/{id}/invoice-pdf` | `GET /orders/{id}/invoice-pdf` |
| `POST /sales/{id}/void` | `POST /orders/{id}/void` |
| `GET /shipping/orders` | `GET /shipping-orders` |
| `POST /shipping/create` | `POST /shipping-orders` |
| `POST /shipping/{id}/status` | `PATCH /shipping-orders/{id}` |
| `POST /gift-cards/check` | `GET /gift-cards/{code}` |
| `POST /gift-cards/issue` | `POST /gift-cards` |
| `GET /payments/khqr` | `POST /payments/khqr` |
| `DELETE /cart/clear` | `DELETE /cart` |
| `DELETE /uploads/image` (query param) | `DELETE /uploads/image/{publicId}` |
| `POST /compliance/customers/{id}/export-data` | `POST /customers/{id}/data-exports` |
| `POST /compliance/customers/{id}/forget-me` | `POST /customers/{id}/erasure-requests` |

**Recommendation:** delete the 19 aliases in the next minor version once the
frontend confirms it uses only canonical routes (the Postman collection
already does). One further consolidation candidate: `POST /products/{id}/image`
and `POST /variants/{id}/image` overlap with `POST /products/{id}/images` -
keep the plural route as canonical and fold the singular upload-and-attach
convenience into it.

### Kept by design (action exceptions, Stripe-style)

`POST /wishlist/toggle`, `POST /promotions/verify-coupon`, `POST /currencies/convert`,
`estimates/{id}/convert`, `stock-transfers/{id}/{approve|pick|ship|receive|cancel}`,
`purchases/auto-generate`, `inventory/stock-opname`, `webhooks/test` - verbs
expressed as action sub-resources, under 10 percent of the API.

## 6. Known follow-ups

1. Transfer lifecycle state rejections return 400; move to 409 CONFLICT for
   consistency with the status-code rules.
2. PDO boolean binding on the pooled Neon connection binds PHP booleans as
   integers - any new boolean write must use `DB::raw('true'/'false')` until
   a connector-level options fix lands.
3. Rotate the 4 Postman-embedded passwords (runbook in the main audit report,
   part 8.2).

## 7. Post-deploy verification

(filled after re-run)
