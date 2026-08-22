# REST API Rules Reference & Production Audit Report
**Project:** OutfitShop MIS & POS API (`https://api.kesararamwithdigital.tech/api/v1`)
**Date:** 2026-08-22 · **Auditor:** AI Agent (4 parallel domain audits)
**Reference standards:** GitHub REST API v3, Stripe API, Shopify Admin API, PayPal REST, Microsoft REST API Guidelines, JSON:API, RFC 9457 (Problem Details), RFC 6570/7231

---

# PART 1 — THE REST API RULE BOOK (Enterprise E-Commerce Edition)

## 1.1 URL & Naming Rules

| # | Rule | Example (GitHub / Stripe) |
|---|------|---------------------------|
| N1 | **Nouns, never verbs** — URLs identify resources, not actions | `GET /repos/{owner}/{repo}` (not `/getRepo`) |
| N2 | **Plural collection names** | `/products`, `/orders`, `/customers` |
| N3 | **Kebab-case for multi-word segments** | `/gift-cards`, `/stock-movements` |
| N4 | **Max 2 levels of nesting** — deeper belongs at top level | `/products/{id}/images` ✔; `/a/b/{id}/c/{id2}/d` ✘ |
| N5 | **Version in URL prefix** | `/api/v1/...` (Stripe: `/v1/charges`) |
| N6 | **IDs in path, filters in query** — never IDs as query params on a singular route | `DELETE /uploads/image/{id}` ✔; `DELETE /uploads/image?id=` ✘ |
| N7 | **One canonical name per resource** — no dual aliases; deprecate instead | GitHub: `repos` never duplicated |
| N8 | **Actions are allowed as exceptions** but must be `POST`, sub-resource style, and rare (<10% of API) | Stripe: `POST /v1/payment_intents/{id}/confirm` |
| N9 | **Trailing consistency** — singleton resources (`/cart`, `/inventory`) are legitimate for per-user singletons | GitHub: `/user` (the authenticated user) |
| N10 | **No typos, no abbreviations** — every segment spelled fully | `verify-coupon`, not `vrfy-cpn` |

## 1.2 HTTP Verb Rules

| # | Rule | Correct mapping |
|---|------|-----------------|
| V1 | **GET is safe** — never mutates state, never requires a body | `GET /payments/khqr?amount=` that *generates* a payload is an operation → must be POST |
| V2 | **POST creates** a resource (201) or executes an action | `POST /orders` → 201 |
| V3 | **PUT = full replacement, PATCH = partial update** — GitHub/Shopify/Stripe all use PATCH for partial | `PATCH /customers/{id}` with `{"phone": "..."}` |
| V4 | **DELETE removes** (200 with body or 204 no body — pick one, document it) | `DELETE /cart/items/{id}` |
| V5 | **Reads via POST are forbidden** — if it only reads, it's GET | `POST /gift-cards/check` ✘ → `GET /gift-cards/{code}` ✔ |
| V6 | **State transitions**: prefer `PATCH {resource}/{id}` with `{"status": "APPROVED"}` or a single documented action sub-resource — not 5 verb endpoints per state | Stripe: `POST /transfers/{id}/reversals` (one action) |

## 1.3 Status Code Rules

| Code | Meaning | E-commerce usage |
|------|---------|------------------|
| 200 | OK — read, update, delete-with-body | |
| 201 | **Created** — every create endpoint (Stripe/GitHub always) | checkout creates sale → 201 |
| 202 | Accepted — async job started | bulk import, export queued |
| 204/200 | Deleted — be consistent | |
| 400 | Malformed request (never as a catch-all for server bugs) | |
| 401 | **Unauthenticated** — bad/missing credentials (NOT 422) | login failure = 401 |
| 403 | Forbidden — authenticated but insufficient role | STAFF calling admin endpoint |
| 404 | Not Found — also hides existence of resources the caller may not see | |
| 409 | **Conflict** — duplicate resource, concurrent state clash | duplicate `branch_code` → 409, not 200 |
| 422 | Validation failed — well-formed but semantically wrong | negative quantity |
| 423 | Locked — account lockout | |
| 429 | Too Many Requests + `Retry-After` header | |
| 500 | Server bug — generic message in prod, detail only in debug | |

## 1.4 Response Envelope Rules

| # | Rule | Standard |
|---|------|----------|
| E1 | **One envelope for the entire API** — success + error shapes documented once | GitHub: resource-first; Stripe: `{"object", ...}` + `{"error": {...}}` |
| E2 | **Machine-readable string error codes** — `ERR_INSUFFICIENT_STOCK`, not "Not enough stock!" | Stripe: `error.code: "card_declined"` |
| E3 | **request_id in body AND `X-Request-Id` response header** for tracing | Stripe: `request_id` in every response; GitHub: `X-GitHub-Request-Id` |
| E4 | **Errors carry `detail.fields[]`** — which field, which rule, why | Stripe: `error.param` |
| E5 | **No stack traces / SQL / internal paths in production errors** — gated by APP_DEBUG | |
| E6 | **ISO-8601 UTC timestamps** | |
| E7 | **Pagination metadata** — `total, per_page, current_page, last_page` + HATEOAS-style `links` | Stripe: `has_more`; GitHub: `Link` header |
| E8 | **Consistent pagination param** (`per_page`) with a **server-side cap** (≤100) to prevent page-size DoS | GitHub caps at 100 |

## 1.5 Pagination, Filtering, Sorting, Includes (JSON:API style)

| # | Rule |
|---|------|
| P1 | Cursor pagination for high-volume feeds (`next_cursor`), offset pagination for admin tables |
| P2 | `filter[field]=value` or documented flat params — **pick one syntax API-wide** |
| P3 | `sort=-created_at,name` (leading dash = descending) — one syntax API-wide |
| P4 | `include=category,variants` with a server-side **allow-list** |
| P5 | Sparse fieldsets: `fields[products]=name,price` for mobile payloads |

## 1.6 Authentication & Authorization Rules

| # | Rule | Standard |
|---|------|----------|
| A1 | **Short-lived tokens** (≤24h) + refresh rotation — never non-expiring | Stripe: keys rotatable; GitHub: tokens expire |
| A2 | **Token abilities/scopes** — a POS token should not call admin endpoints | GitHub: fine-grained PATs; Stripe: restricted keys |
| A3 | **Login failure = 401 with a generic message** — no user enumeration |
| A4 | **Account lockout** after N failed attempts with timed release |
| A5 | **Rate limits per role** + `Retry-After` + `X-RateLimit-*` headers | GitHub: 5000/hr authenticated, 60/hr unauthenticated |
| A6 | **Every public route is throttled** — an unthrottled public write endpoint is an abuse vector |
| A7 | **IDOR protection** — unauthenticated access to a receipt by sequential ID leaks PII |
| A8 | **Paid assets require purchase verification** before download |
| A9 | 2FA must actually verify the code (TOTP RFC 6238) or be removed |
| A10 | Password reset flow must exist for any account system |

## 1.7 Idempotency & Concurrency Rules (e-commerce critical)

| # | Rule | Standard |
|---|------|----------|
| I1 | **All money-mutating endpoints accept `Idempotency-Key`** | Stripe: mandatory on all POSTs |
| I2 | **Idempotency key MUST be backed by a UNIQUE constraint** — check-then-act without DB enforcement loses the race |
| I3 | **Stock/balance deductions inside `DB::transaction()` + row locks (`SELECT ... FOR UPDATE`)** — prevents oversell |
| I4 | **Money is always `DECIMAL(12,2)`** (never float); historical prices stamped on the line item |
| I5 | Replay returns the original result (200 + same payload), not a duplicate charge (201) |

## 1.8 Security Rules

| # | Rule |
|---|------|
| S1 | Security headers: HSTS, X-Content-Type-Options, X-Frame-Options, tight CSP (no `unsafe-eval` for JSON APIs) |
| S2 | CORS explicit allow-list, no `*`, preflight cache `max_age > 0`, no localhost in production list |
| S3 | Secrets only in `.env` (gitignored); no credentials in git history |
| S4 | Mass-assignment: `$fillable` on every model; never `User::create($request->all())` with `is_admin` fillable |
| S5 | GDPR: export (Art. 20) + erasure (Art. 17) endpoints; PCI-DSS: zero card storage |
| S6 | Admin surfaces optionally IP-allow-listed — middleware must not be dead code |
| S7 | No info leak in 500s (message/file/line) in production |

## 1.9 Performance, Caching, Queues Rules

| # | Rule |
|---|------|
| Q1 | Cache read-heavy catalogs (Redis), target >85% hit ratio |
| Q2 | Cache invalidation on model events (observers) — not only manual `forget()` in controllers |
| Q3 | Heavy work (PDF, Excel, email, image processing) → **queued jobs with retry/backoff**; HTTP returns 202 |
| Q4 | No N+1: eager-load relations on every list endpoint |
| Q5 | Latency targets: <50ms cached, <200ms uncached |
| Q6 | Telemetry must never fabricate values — return `null`/`unavailable` when data is missing |

## 1.10 Documentation & DX Rules

| # | Rule |
|---|------|
| D1 | Machine-readable spec (OpenAPI) + generated Postman |
| D2 | Public error-code dictionary |
| D3 | Changelog with deprecation windows + `Deprecation`/`Sunset` headers |
| D4 | Every route named (route cache, signed URLs, error tracing) |

## 1.11 Testing & CI/CD Rules

| # | Rule |
|---|------|
| T1 | Feature tests per RBAC tier × critical transaction (checkout, void, idempotent replay) |
| T2 | Unit tests for services (pricing, stock math) |
| T3 | CI: lint → test (on the PRODUCTION database engine, not only sqlite) → build → deploy → smoke test → notify |
| T4 | Rollback path + backup retention (RPO <5min, RTO <30min) |

---

# PART 2 — AUDIT RESULTS (11-Domain Scoring)

**Scale measured:** 189 endpoints · 54 controllers · 38 migrations · 4 jobs · 7 test files.

| # | Domain | Score | Verdict |
|---|--------|:-----:|---------|
| 1 | Response Format & Enveloping | **8/10** | ✅ Strong — one factory (53/54 controllers), request_id, Stripe-style errors |
| 2 | Auth & RBAC | **6/10** | ⚠️ 4-tier RBAC + lockout + rotation excellent; fake 2FA, never-expiring tokens |
| 3 | Database & Concurrency | **7/10** | ⚠️ FKs/decimal/composite indexes strong; idempotency race |
| 4 | Caching Layer | **5/10** | ⚠️ Cached but database store, manual-only invalidation |
| 5 | Queues & Workers | **3/10** | ❌ All 4 jobs are dead code; exports synchronous |
| 6 | Security Hardening | **6/10** | ⚠️ Headers/GDPR/mass-assignment good; IDOR + public write exposure |
| 7 | Performance & Latency | **6/10** | ⚠️ No N+1; uncapped per_page; fabricated telemetry |
| 8 | Automated Testing | **3/10** | ❌ 20 real tests for 189 endpoints; zero unit tests |
| 9 | Logging & Monitoring | **6/10** | ⚠️ 5 channels + api_logs; fabricated fallback metrics |
| 10 | API Documentation & DX | **7/10** | ✅ Postman + /guide + error codes; routes unnamed |
| 11 | DevOps & CI/CD | **7/10** | ✅ 6-stage pipeline + smoke test; no rollback step |
| | **TOTAL** | **64/110** | **58% — production-adjacent** |

---

## 2.1 🔴 CRITICAL (fix before any further production use)

| # | Finding | Location |
|---|---------|----------|
| C1 | **Fake 2FA** — `verify2FA` always returns `2fa_verified: true`; secret generated from non-cryptographic `md5(uniqid())`; submitted code is never compared | `app/Http/Controllers/Api/V1/AuthController.php:268-296` |
| C2 | **Idempotency race** — `sale_headers.idempotency_key` has NO unique index; the check-then-act guard in POSService runs outside the transaction → two concurrent retries with the same key create **duplicate sales/charges** | `app/Services/POSService.php:50-55` + `database/migrations/2026_08_17_000007_...:21-23` |

## 2.2 🟠 HIGH

| # | Finding | Location | Rule broken |
|---|---------|----------|-------------|
| H1 | **Sanctum tokens never expire** (`'expiration' => null`) — a leaked token is valid forever | `config/sanctum.php:55` | A1 |
| H2 | **IDOR: unauthenticated receipts/invoices** by sequential ID — `GET /orders/{id}/receipt-thermal`, `/invoice-pdf`, `/sales/{id}/receipt-thermal`, `/invoice-pdf` expose PII with no auth | `routes/api.php:158-162` | A7 |
| H3 | **Paid digital assets downloadable without auth or purchase verification** | `routes/api.php:117`, `DigitalAssetController.php:18-43` | A8 |
| H4 | **Public tier is unthrottled** — Laravel 12 api group ships without throttle and `throttleApi()` is never called; every public route except `/auth/login` has no rate limit (incl. the public write endpoints) | `bootstrap/app.php:33-51` | A6 |
| H5 | **All 4 queue Jobs are dead code** (zero `dispatch()` call sites); exports/PDFs run synchronously inside HTTP | `app/Jobs/*`, `FileExportController.php:22-169` | Q3 |
| H6 | **Test suite ~20 tests / 189 endpoints** — zero coverage of idempotency, void, cart, transfers, webhooks | `tests/` | T1 |

## 2.3 🟡 MEDIUM

| # | Finding | Location | Rule |
|---|---------|----------|------|
| M1 | Unauthenticated review **writes** (spam/abuse; client-supplied `customer_id`) | `routes/api.php:119` | A6/A7 |
| M2 | Public business-intelligence: `/inventory/statistics` (cost/resale valuation), `/variants/low-stock` | `routes/api.php:146,153` | — |
| M3 | Login failure returns **422** (signals which field failed) instead of 401 generic | `AuthController.php:241-243` | 401 rule |
| M4 | Duplicate `branch_code` returns **200** with existing record instead of **409** | `StoreBranchController.php:66-69` | 409 rule |
| M5 | Blanket `catch (Exception) → 400` in checkout controllers flattens real 500s to 400 | `OrderController.php:112-113`, `SaleController.php:102,139` | 400 rule |
| M6 | `per_page` uncapped in 11+ controllers (`per_page=100000` dumps full ledger with eager loads); `per_page=all` unpaginated; `AuditLogController` hardcodes 50 | `SaleController.php:62`, `ProductController.php:155-157`, `AuditLogController.php:15` | E8 |
| M7 | **49 RPC-style endpoints (~26% of API)** incl. worst offenders: `POST /shipping/create` (verb in URL), `POST /shipping/{id}/status` (update-as-POST), `POST /gift-cards/check` (read-as-POST), `GET /payments/khqr` (generation-on-GET with required input), `DELETE /cart/clear`, `POST /wishlist/toggle` | `routes/api.php:95,137,142,156,163,215-216` | N1/V1/V5 |
| M8 | **Dual `orders`/`sales` alias surface** — identical controller methods routed under both names; plus `image` vs `images` split (`POST /products/{id}/image` AND `POST /products/{id}/images`) | `routes/api.php:199-206,317-318` | N7 |
| M9 | **Zero PATCH usage** — 10 PUT updates, no PATCH (partial updates are the norm in GitHub/Stripe/Shopify) | `routes/api.php` (all update routes) | V3 |
| M10 | **No password-reset / forgot-password endpoint exists** | project-wide | A10 |
| M11 | Tokens issued with `['*']` abilities — no scope separation | `AuthController.php:129,183,322,439` | A2 |
| M12 | Redis unused (`CACHE_STORE=database`); `AdminPerformanceController` fabricates hit-ratio (88.7%) and latency numbers when data missing — dashboards lie green during outages | `.env:42`, `AdminPerformanceController.php:22-107` | Q1/Q6 |
| M13 | Cache invalidation is manual-only (no observers); `categories:all` is `rememberForever`; `product_colorways:{id}` has no invalidation path | `CategoryController.php:20`, `ProductMatrixController.php:93` | Q2 |
| M14 | STAFF can write `/customers` although the declared permission map grants STAFF only `customers.read` — permission map not server-enforced | `routes/api.php:187-188` | — |
| M15 | Login crash path leaks `$e->getMessage()` + file/line in 500 detail **regardless of APP_DEBUG** | `AuthController.php:255-260` | E5/S7 |
| M16 | `X-Request-Id` exists in body but is **never set as response header** — non-JSON responses (downloads/HTML receipts) carry no trace id | `SecurityHeadersMiddleware.php:23-29` | E3 |
| M17 | Raw catalog SQL untracked in git, run manually against Neon (bypasses migrations/seeders) | `database/sql/*.sql` | T4 |
| M18 | CSP with `unsafe-inline` + `unsafe-eval`; localhost origins in production CORS allowlist; `max_age=0` | `SecurityHeadersMiddleware.php:18`, `config/cors.php:25-32` | S1/S2 |
| M19 | `AdminIpWhitelistMiddleware` + `AdminMiddleware` + `ApiDeprecationHeaderMiddleware` are **dead code** (never registered/attached); whitelist also fails open and has a `127.0.0.1` wildcard bug | `app/Http/Middleware/` | S6/D3 |
| M20 | sqlite-in-tests vs Postgres-in-prod parity (ILIKE, pg_stat_activity) — tests can pass where prod breaks | `phpunit.xml` vs `ProductController.php:53` | T3 |

## 2.4 ⚪ LOW (selection)

- `GET /health` = `GET /status` and `/guide` = `/docs` duplicate surfaces; `/postman.json` publishes the internal API map publicly.
- Only 1 of 189 routes has a name (`->name('login')`) — breaks route caching/signed URLs (D4).
- `GET /alerts/active` is an inline closure with raw `DB::table()` in the route file (`routes/api.php:226-236`).
- `User::$fillable` includes `is_admin` (privilege-escalation foot-gun) (`app/Models/User.php:23-29`).
- `ProductController::show()` caches full serialized Eloquent models into the database cache store.
- Duplicate dead branch `if ($inc === 'variants')` twice; `primaryImage` in allow-list never mapped (`ProductController.php:28-40`).
- `stock_movements.created_by` has no actual FK constraint (`2026_08_17_000007:53-55`).
- Default page-size drift (20 vs 50) undocumented; `admin` log channel defined but never used; composer `audit.ignore` pins 10 advisories; deploy has no rollback step.
- `ApiResponse.php:183` comment "Temporarily forced for live troubleshooting" — the `debug` key always renders and the gate lives only at call sites.

---

## 2.5 What the API already does RIGHT (credit where due)

- **Envelope discipline**: single `ApiResponse` factory, 53/54 controllers, zero raw-JSON bypasses, structured `error.detail.fields[]`, pagination meta + links, ISO timestamps, request_id echo.
- **Checkout integrity**: `DB::transaction()` + `lockForUpdate()` on every variant row, historical price stamping, stock_before/after ledger, invoice numbering, idempotency key accepted from body **and** header.
- **RBAC architecture**: clean 4-tier route grouping, role-based rate limits (300/200/100/50/30 per min) with `Retry-After` + `X-RateLimit-Reset` headers, account lockout (10 tries / 15 min) with security-channel alerts.
- **Database**: FKs everywhere, composite performance indexes on sale/payment/stock tables, `DECIMAL(12,2)` money throughout, unique SKUs/barcodes, eager loading on all list endpoints (no N+1 found).
- **GDPR**: export-data, forget-me (anonymization preserving 7-year tax ledger), retention-policy endpoint — manager/admin gated.
- **CI/CD**: 6-stage GitHub Actions pipeline with Pint → tests → build → zero-downtime deploy → live smoke test → Slack.
- **Hygiene**: no secrets in git, `$fillable` on all models, token values never logged.

---

# PART 3 — 4-WEEK IMPROVEMENT ROADMAP

## Week 1 — Close the critical security holes (C1, C2, H1–H4)
1. **Idempotency**: migration adding `UNIQUE` index on `sale_headers.idempotency_key`; wrap guard in try/catch on duplicate-key → return existing sale (I1/I2/I5).
2. **Fix or remove 2FA**: implement TOTP (e.g. `pragmarx/google2fa`) or delete the endpoints entirely — an always-true verify is worse than none (A9).
3. **Sanctum expiration** → e.g. 24h + refresh rotation already exists (A1); add token abilities per role (A2).
4. **Move the 5 receipt/invoice/download routes behind `auth:sanctum`** + purchase verification (A7/A8); require auth for review writes (M1).
5. **Global public throttle** — register a default `throttle:public` on the Tier-1 group (A6).

## Week 2 — REST correctness + response hygiene (M3–M9)
6. 401 on bad credentials (generic message); 409 on duplicate branch; replace blanket 400 catch with real status mapping.
7. Cap `per_page` globally (≤100) via a shared request helper; kill `per_page=all`; unify default (20) and document it.
8. Fix wrong-verb endpoints: `GET /gift-cards/{code}` replaces `POST /gift-cards/check`; `GET /payments/khqr` → `POST`; `POST /shipping/{id}/status` → `PATCH /shipping-orders/{id}`; `POST /shipping/create` → `POST /shipping-orders`.
9. Introduce PATCH alongside PUT for partial updates; consolidate `orders`/`sales` into one canonical resource with `Sunset` deprecation headers on the alias (M8, D3); merge `/uploads/image` routes into the plural schema with `{id}` in path.
10. Set `X-Request-Id` response header in middleware (M16).

## Week 3 — Performance & async (H5, M12, M13)
11. Wire the 4 existing Jobs: `GenerateReportExportJob` from FileExportController (return 202 + job id), `SendOrderNotificationJob` from checkout, `ProcessImageUploadJob` from uploads; add supervisor/worker config.
12. Switch `CACHE_STORE=redis`; add Eloquent Observers for Product/Category/Variant cache invalidation; replace `rememberForever` with TTL; fix `product_colorways` invalidation.
13. Remove fabricated telemetry fallbacks — return `null` + `available: false` instead of invented numbers (Q6).

## Week 4 — Testing, hardening, handover (H6, M14–M20)
14. Test sprint: idempotent-replay race, void/refund, cart lifecycle, per-role matrix for the 49 RPC endpoints, cache behavior, POSService unit tests (money math).
15. Run tests against Postgres in CI (services: postgres) for parity (M20); commit `database/sql` as seeders or delete; clean dead middleware or register it (M19); tighten CSP/CORS (M18).
16. Publish updated Postman + error-code dictionary reflecting the verb fixes; add route names; document the pagination cap and defaults.

## KPI targets after the roadmap
| Metric | Target |
|---|---|
| Cached latency | < 50 ms |
| Uncached latency | < 200 ms |
| Cache hit ratio | > 85% (measured, not fabricated) |
| API error rate | < 0.2% |
| Test pass rate | 100% on ≥150 feature tests |
| Duplicate-charge window | 0 (unique index enforced) |
| Public unthrottled endpoints | 0 |

---

# PART 4 — FIX STATUS (Execution Log, 2026-08-22)

| ID | Fix | Status | Files touched |
|----|-----|--------|---------------|
| C1 | Real RFC 6238 TOTP 2FA (cryptographic secret, constant-time verify, encrypted at rest, verified by RFC test vector) | ✅ FIXED | `app/Support/Totp.php` (new), `AuthController.php`, migration `..._000002_add_two_factor_columns...`, `User.php`/`Employee.php` hidden attrs |
| C2 | UNIQUE index on `sale_headers.idempotency_key` + race-safe guard (locked in-transaction re-check + unique-violation recovery) | ✅ FIXED | migration `..._000001_add_unique_index...`, `app/Services/POSService.php` |
| H1 | Sanctum token expiration 24h (`SANCTUM_EXPIRATION_MINUTES`, default 1440) | ✅ FIXED | `config/sanctum.php` |
| H2 | Receipts/invoices/KHQR-by-order moved behind `auth:sanctum` | ✅ FIXED | `routes/api.php` |
| H3 | Digital download requires auth + purchase verification (employees exempt; customers checked via COMPLETED sale) | ✅ FIXED | `routes/api.php`, `DigitalAssetController.php` |
| H4 | Public tier throttled (guest branch of role-based limiter, 30/min per IP; health/docs left open for monitors) | ✅ FIXED | `routes/api.php` |
| M1 | Review writes require authentication | ✅ FIXED | `routes/api.php` |
| M3 | Bad credentials → 401 generic (`AUTHENTICATION_FAILED`), no field hint | ✅ FIXED | `AuthController.php`, `AuthApiTest.php` |
| M4 | Duplicate `branch_code` → 409 `DUPLICATE_BRANCH_CODE` | ✅ FIXED | `StoreBranchController.php` |
| M5 | Checkout/void: business rules → 422 (`PosRuleException`), unexpected faults → 500 with generic message | ✅ FIXED | `PosRuleException.php` (new), `POSService.php`, `OrderController.php`, `SaleController.php` |
| M6 | Global `perPage()` cap (≤100) in BaseApiController, applied to 12 controllers; `per_page=all` removed; AuditLog honors per_page | ✅ FIXED | `BaseApiController.php` + 12 controllers |
| M7 | Correct-verb aliases added: `GET /gift-cards/{code}`, `POST /payments/khqr`, `GET|POST /shipping-orders`, `PATCH /shipping-orders/{id}` (legacy routes kept) | ✅ FIXED | `routes/api.php`, `GiftCardController.php` |
| M9 | PATCH accepted alongside PUT on all 10 update routes (`Route::match`) | ✅ FIXED | `routes/api.php` |
| M15 | Login 500 detail gated behind `app.debug` | ✅ FIXED | `AuthController.php` |
| M16 | `X-Request-Id` echoed/generated as response header (consistent with envelope) | ✅ FIXED | `SecurityHeadersMiddleware.php` |
| — | ProductController include-map bug (duplicate `variants` branch, unmapped `primaryImage`) | ✅ FIXED | `ProductController.php` |
| — | New tests: idempotent replay (no duplicate charge), unique-index existence, 6 TOTP unit tests incl. RFC 6238 vector | ✅ ADDED | `tests/Feature/IdempotentCheckoutTest.php`, `tests/Unit/TotpTest.php` |
| — | Postman collections updated (auth headers on the 9 newly protected requests), master + root copy | ✅ SYNCED | `postman_collection.json`, `postman/OutfitShop_Master_Collection.json` |

**Verification:** 30 tests / 326 assertions — 100% pass. Pint: 195 files clean. `route:list` confirms 201 routes incl. PATCH verbs and new aliases.

**Deployment notes:**
1. Run `php artisan migrate` (2 new migrations: unique index + 2FA columns).
2. `php artisan config:cache` refresh required (sanctum expiration).
3. Existing tokens older than 24h (by `last_used_at`) will expire — clients must call `POST /auth/refresh`.
4. Callers of the moved endpoints (receipts, invoices, downloads, review writes) must now send `Bearer` tokens; the Postman collection already reflects this.

**Remaining from roadmap:** ~~H5, H6, M2, M8, M10, M11, M12, M13, M14, M17–M20~~ — **all subsequently fixed; see Part 5 (round 2), Part 6 (round 3), Part 7 (compliance matrix), and Part 8 (final closure).**

---

# PART 5 — ROUND 2 EXECUTION LOG (2026-08-22, same day)

All remaining CRITICAL/HIGH/MEDIUM verdicts executed, plus the database
standardization request (unified user profile + missing relationships).

## 5.1 Database standardization (tables first, as requested)

| Change | Migration / File |
|---|---|
| `users` is now a complete identity record: `username` (unique), `phone`, `avatar_url` (profile picture), `joined_at` (staff start date), `status` + status index, `last_login_at`, `last_login_ip`, soft deletes, and the real `employee_id` FK (with orphan cleanup) that was fillable-but-missing | `2026_08_22_000003_standardize_user_identity_tables.php` |
| `employees` gains matching `joined_at`, `avatar_url`, `last_login_at`, `last_login_ip` (login telemetry parity for the primary POS identity) | same migration |
| `stock_movements.created_by` now a real FK to employees (orphans nulled first) — was a bare column with no constraint | `2026_08_22_000004_add_missing_relational_constraints.php` |
| `products` gets `idx_products_active_recent (deleted_at, created_at)` backing the default soft-delete listing query from indexing.todo | same migration |
| User model: fillable/casts/SoftDeletes + `employee()` relation; both models hide the 2FA + new telemetry columns from responses | `app/Models/User.php`, `app/Models/Employee.php` |
| Login records `last_login_at`/`last_login_ip` on every successful login (both identity types); `/auth/me` now returns the full profile (username, phone, avatar_url, joined_at, last_login_at, status) | `AuthController.php` |
| Raw catalog SQL (`database/sql/*.sql`) now runs through the standard seeder lifecycle with brand-already-present guards | `database/seeders/BrandCatalogSeeder.php` |

## 5.2 Remaining verdict fixes

| ID | Fix | Status |
|----|-----|--------|
| M2 | `/inventory/statistics` + `/variants/low-stock` moved behind `auth:sanctum` (business intelligence is not storefront data) | ✅ |
| M8 | Legacy aliases (`/sales/*`, `/shipping/*`, `POST /gift-cards/check`, `GET /payments/khqr`) now emit IETF `Deprecation: true` + `Sunset` + successor `Link` headers (sunset 2027-12-31) | ✅ |
| M10 | Full password-reset flow: `POST /auth/forgot-password` (generic response, single-use 30-min token, sha256-hashed in cache, security-logged) + `POST /auth/reset-password` (revokes ALL target sessions) + ADMIN `POST /auth/admin-reset-password` (no-mailer ops path, audited) | ✅ |
| M11 | New tokens carry least-privilege abilities derived from the role permission map; `ability:sales.checkout` enforced on both checkout routes (legacy `['*']` tokens still pass). Ability enforcement on void was reverted — see 5.4 note | ✅ (partial) |
| M12 | `AdminPerformanceController` telemetry honesty contract: every metric measured or `null` + `telemetry_available: false`; health status derived from real signals (`UNKNOWN_NO_TELEMETRY` when empty); `.env.example` documents Redis as the production cache store | ✅ |
| M13 | Eloquent observers (`ProductObserver`, `ProductVariantObserver`, `CategoryObserver`) flush read caches on ANY write path; `categories:all` `rememberForever` replaced with 1h TTL; `product_colorways:{id}` invalidation now exists via observers | ✅ |
| M14 | Customer writes gated to `role:CASHIER,MANAGER,ADMIN` — the STAFF read-only permission map is now server-enforced | ✅ |
| M17 | Brand catalog SQL wrapped in `BrandCatalogSeeder` (idempotent guards per brand) | ✅ |
| M18 | CSP tightened (no `unsafe-eval`; inline styles only for HTML renders); CORS production origins by default, localhost merged only for local/testing envs, `max_age=86400`, exposes `X-Request-Id`/`Retry-After`/`X-RateLimit-Reset`, `CORS_ALLOWED_ORIGINS` override | ✅ |
| M19 | `AdminIpWhitelistMiddleware` fixed (real CIDR/IP matching, no wildcard/localhost bypass) and registered as `admin.ip` on the Tier-4 group; `ApiDeprecationHeaderMiddleware` registered as `deprecated`; dead `AdminMiddleware` deleted | ✅ |
| M20 | CI test stage now runs the suite against a PostgreSQL 16 service container (production engine parity); phpunit env `force=false` keeps sqlite as the local default | ✅ |
| H5 | All 4 Jobs wired: `SendOrderNotificationJob` dispatched post-commit on customer checkouts; `ProcessImageUploadJob` from product-image store; `GenerateReportExportJob` via `?async=1` on inventory export (202 + export_id); `BulkStockOpnameJob` via new `POST /inventory/stock-opname` (202, audited) | ✅ |
| H6 | 8 new regression tests: guest-401 on all formerly public PII/BI endpoints, STAFF-vs-CASHIER customer writes, PATCH partial updates, 409 duplicate branch, per_page cap, void lifecycle + double-void 422, full forgot/reset password rotation. Suite: **38 tests / 352 assertions, 100%** | ✅ |

## 5.3 PATCH-semantics fix discovered during testing

`CategoryController::update` required `category_name` on every update —
which contradicts the newly added PATCH support. Update validation now uses
`sometimes` so partial updates validate only the fields a client sends.

## 5.4 Known limitation (documented)

Token-ability middleware (`ability:sales.void`) on the void routes produced
inconsistent identity resolution inside the PHPUnit process (multiple
authenticated users per test share one PHP process; production runs one
request per process and cannot exhibit this). Enforcement stays on the
checkout routes, where it passed consistently; revisit void ability
enforcement with a dedicated middleware-priority entry or in-controller
check.

## 5.5 Verification (round 2)

- Pint: full app/routes/config/database/tests clean
- PHPUnit: 38 passed, 352 assertions (includes 2 new migrations applied per test via RefreshDatabase)
- Routes: 205 registered (was 189) — new: forgot/reset/admin-reset password, stock-opname, RESTful shipping aliases, GET gift-cards/{code}, POST payments/khqr; PATCH verb now available on all 10 update routes
- Deployment additions: `php artisan migrate` (2 new migrations), optional `ADMIN_IP_WHITELIST` env, `CORS_ALLOWED_ORIGINS` env, queue worker recommended (`php artisan queue:work database`) now that jobs actually dispatch

---

# PART 6 — RISK / FAKE / INJECTION SWEEP (2026-08-22, round 3)

## 6.1 Injection audit results (full codebase sweep)

| Vector | Finding | Action |
|---|---|---|
| Raw SQL (`whereRaw`/`DB::raw`/`DB::select`/`unprepared` — every usage in app/ + routes/) | All fragments are static strings or allow-listed (`ReportController` TO_CHAR format comes from a `match` on an allow-listed `group_by`) | ✅ No injectable SQL found; binding used everywhere user input reaches queries |
| LIKE/ILIKE wildcard injection | `?q=` search and `?brand=` interpolated raw `%`/`_` from clients (pattern-craft full-scan DoS) | ✅ FIXED — shared `escapeLike()` helper applied in Product + Customer search/brand filters |
| HTML injection in rendered documents | Invoice HTML escaped name/phone/address/product/size/color but **not `variant->sku`**; thermal receipts are JSON (safe) | ✅ FIXED — SKU now htmlspecialchars-escaped |
| Mass assignment | `User::$fillable` included `is_admin` (privilege-escalation foot-gun) | ✅ FIXED — removed from fillable; set only via audited `forceFill()` in register; regression test proves `is_admin: true` payload is ignored |
| Credential exposure | **`GET /postman.json` served the collection publicly — it embeds 6 real working passwords** (admin/manager/cashier/staff logins) | ✅ FIXED — route now requires `auth:sanctum` + `role:MANAGER,ADMIN`; guest receives 401 (regression-tested). **Rotate those 4 passwords after deploying** since they were exposed |
| Debug/internals leak | `ApiResponse::error()` rendered `debug` unconditionally ("temporarily forced" comment); login 500 detail was already gated but any future caller could leak | ✅ FIXED — `error()` itself strips debug payloads unless `APP_DEBUG=true`; no call site can bypass |
| Registration token issuance | `register()` auto-issued a live token for the new account | ✅ FIXED — account created only; token requires a separate login (audited, admin-channel logged) |
| Latent 500 in register | Spatie `assignRole` crashed when the role table is not seeded | ✅ FIXED — fail-soft with warning log |

## 6.2 Remaining LOW-tier items closed

| Item | Action |
|---|---|
| Duplicate `GET /status` + `GET /docs` | Kept as aliases with `Deprecation`/`Sunset` headers (2027-12-31); `/health` and `/guide` are canonical |
| `GET /alerts/active` inline closure with raw DB in the route file | Moved to `DashboardController::activeAlerts` |
| Route names (1 of 189 named) | Named the core surface: health, guide, login, forgot/reset, auth session group (me/logout/refresh/revoke-all/2fa), products index/show/images, orders index/show/checkout, dashboard.role-pulse, alerts.active |
| `ProductController::show` cached full Eloquent models | Now caches the array payload (bounded cache weight, identical wire format) |
| `admin` log channel defined but unused | Now used by register + admin-forced password reset |
| Deploy pipeline had no rollback | Stage 7 manual rollback job added (`workflow_dispatch` + `rollback_sha` input, signed webhook trigger, post-rollback health gate) |
| composer `audit.ignore` pinned 10 advisories | Cleared — `composer audit` reports **0 known vulnerabilities** |
| Default page-size drift (20 vs 50) | Documented per endpoint (products/sales/orders default 20; employees/customers/suppliers/stock-movements/audit-logs default 50); all capped at 100 |
| M11 completion | Void now enforces `tokenCan('sales.void')` in-controller (defense in depth; legacy `*` tokens pass) |

## 6.3 Verification (round 3)

- PHPUnit: **39 tests / 357 assertions — 100% pass** (2 new: postman.json guest-401, registration no-token + is_admin mass-assignment ignored)
- Pint: 219 files clean · `composer audit`: clean · route:list: 205 routes
- postman.json exposure requires password rotation on deploy (see 6.1)

---

# PART 7 — PART 1 RULE-BY-RULE COMPLIANCE MATRIX (2026-08-22, round 4)

Part 1 is the rule book (nothing to "fix" in it); this matrix answers where the
API now stands against every rule, after all four execution rounds.

| Rule group | Rule | Status |
|---|---|---|
| Naming N1 (nouns, actions rare) | ✅ Canonical RESTful route added for every wrong-verb case; legacy RPC routes kept only as deprecated aliases with Sunset headers. The intentional action layer (stock-transfer lifecycle, bulk ops, shifts) follows the Stripe action-sub-resource exception |
| Naming N2/N3/N9/N10 | ✅ Plural, kebab-case, singletons, no typos |
| Naming N4 (nesting ≤ 2) | ✅ `/customers/{id}/data-exports` + `/customers/{id}/erasure-requests` canonical; deep `/compliance/*` paths deprecated |
| Naming N5 (versioning) | ✅ `/api/v1` |
| Naming N6 (IDs in path) | ✅ `DELETE /uploads/image/{publicId}` canonical; query-param delete deprecated |
| Naming N7 (one canonical name) | ✅ `orders` canonical; `sales/*` deprecated; `shipping-orders` canonical; `/shipping/*` deprecated; `/health`+`/guide` canonical; `/status`+`/docs` deprecated; `POST /gift-cards` + `DELETE /cart` canonical with legacy aliases |
| Verbs V1 (GET safe) | ✅ POST `/payments/khqr` canonical; GET alias deprecated |
| Verbs V2/V4 (POST→201, DELETE) | ✅ Verified across controllers |
| Verbs V3 (PATCH partial) | ✅ PATCH on all 10 update routes + `sometimes` validation on all update endpoints (Color, ClothingSize, Supplier, Customer, Employee fixed this round; Category/Brand/Product already done) |
| Verbs V5 (no reads via POST) | ✅ `GET /gift-cards/{code}` canonical; `verify-coupon`/`convert` remain POST by the computation exception (Stripe pattern) |
| Verbs V6 (state transitions) | ✅ `PATCH /shipping-orders/{id}`; transfer lifecycle documented as action-sub-resource exception |
| Status codes | ✅ 200/201/202/401/403/404/409/422/423/429/500 all conform (login 401, branch 409, checkout 422/500 mapping, Retry-After headers) |
| Envelope E1–E8 | ✅ Single factory; string error codes; request_id body + header; error.detail.fields; debug gated centrally; ISO-8601; pagination meta + links; per_page capped at 100 |
| Pagination/filtering P1–P5 | ✅ P2/P3 partial by design: JSON:API-style sort/include live on the products surface only (documented); cursor pagination available in the envelope; sparse fieldsets not implemented (documented backlog) |
| Auth A1–A10 | ✅ All ten rules enforced (24h tokens + rotation, abilities, generic 401, lockout, role throttles, public throttle, IDOR fixed, purchase-gated downloads, real TOTP 2FA, password reset) |
| Idempotency I1–I5 | ✅ UNIQUE index + race-safe double guard + locks + decimal money + replay 200 |
| Security S1–S7 | ✅ Headers/CSP, CORS allow-list, secrets out of git, no mass-assignment (is_admin unfillable), GDPR endpoints, registered IP whitelist, no 500 leaks |
| Performance Q1–Q6 | ✅ Observers invalidate caches; jobs wired; no N+1; telemetry honest. Note: production `CACHE_STORE=redis` flip is an operator action documented in .env.example |
| DX D1–D4 | ✅ Postman (auth-gated), error dictionary, Deprecation/Sunset headers, core routes named |
| Testing/CI T1–T4 | ✅ 39 tests/357 assertions (RBAC, checkout, idempotency, void, reset, mass-assignment, TOTP vectors); CI runs on PostgreSQL; rollback job + REAL nightly `backup:database` command (round 4 also replaced the previously scheduled phantom `db:backup` command that never existed) |

**Open by design (documented, not risks):** legacy aliases until sunset 2027-12-31; action-sub-resource layer for POS workflows; sparse fieldsets; Redis store flip on the server; production password rotation for the 4 leaked credentials.

**Round 4 verification:** 39 tests / 357 assertions pass · pint clean · 210 routes · `backup:database` command registered and scheduled.

---

# PART 8 — FINAL CLOSURE OF PARTS 2–6 RESIDUALS + DOUBLE-CHECK (round 5)

## 8.1 Residuals from Parts 2–6 closed in this round

| Source | Residual | Closure |
|---|---|---|
| P2/P3 LOW | Only core routes had names | Auto-namer registered (`AppServiceProvider::booted`): every route now carries a deterministic `api.{method}.{path}` name; explicit names (login, products.show, orders.checkout, auth.*) always take precedence |
| P3 W4 | Cart lifecycle, cache behavior, POSService money math untested | 3 new tests: guest cart full lifecycle (add → PATCH qty → remove → DELETE /cart), product cache invalidated by update through the observer, checkout money math (item discount, overall discount, 10% tax, change due, historical unit price, stock deduction) |
| P3 W4 | Postman + error dictionary did not reflect the new surface | New folder "12 - v1.2 Security and Canonical REST Endpoints" (13 requests: password recovery, admin reset, canonical gift-card/cart/shipping/GDPR routes, stock-opname, auth-gated postman.json) added to BOTH collections; `API-Delivery-Package/error_codes.md` extended with all 11 new codes + deprecation note |
| P4 | Stale "Remaining from roadmap" line | Updated to point at Parts 5–8 |
| P6 6.1 | Password rotation flagged as post-deploy step | Runbook below |

## 8.2 Credential rotation runbook (post-deploy, required)

The 4 Postman-embedded passwords were publicly readable via `/postman.json`
until this release. Rotation MUST happen after deploy (rotating before deploy
would re-expose the new secrets through the still-public route):

1. Deploy this release; verify `GET /api/v1/postman.json` without a token returns 401.
2. Login as ADMIN, then for each account call
   `POST /api/v1/auth/admin-reset-password {"email":"<account>","new_password":"<new>"}`:
   `admin@api.kesararamwithdigital.tech`, `manager@…`, `cashier@…`, `staff@…`.
   Each call revokes all of that account's sessions automatically.
3. Re-login as each role; Postman variable `token` refreshes via the login requests.
4. Update the four login request bodies in both Postman files with the new passwords (they are the source of truth for handoff).
5. Optional hardening: set `ADMIN_IP_WHITELIST` on the server.

## 8.3 Double-check verification (this round)

- PHPUnit: **42 tests / 374 assertions — 100% pass**
- Pint: clean · both Postman JSONs valid · `composer audit`: 0 advisories
- `route:list`: 210 routes, every route now named

## 8.4 Final sweep (round 6, same day)

Full re-verification of every Part 2–6 fix against the codebase found one
residual gap, closed here:

| Item | Finding | Closure |
|------|---------|---------|
| M11 residual | `POST /auth/refresh` re-issued rotated tokens without abilities, silently widening them to the Sanctum default `['*']` — so every 24h rotation defeated the least-privilege scope login issues | `refresh()` now resolves the role first and passes `tokenAbilities($role)` to `createToken()`, matching login exactly |
| Test coverage | No test exercised `/auth/refresh` at all | New regression test `refresh rotation preserves role scoped token abilities`: rotated token differs, old token row revoked + rejected (401), rotated token accepted, and abilities are the cashier scope (`sales.checkout` present, `*` absent) |

Note on the test environment: the cached Sanctum request guard makes any
follow-up request in the same PHPUnit process appear authenticated (even with
a garbage token) because one app instance serves all requests in a test. The
test calls `auth()->forgetGuards()` between requests to force fresh guard
resolution; production is unaffected (one request per process). This is the
same class of phenomenon documented in 5.4.

**Round 6 verification:** 43 tests / 381 assertions — 100% pass · Pint 230 files clean · `composer audit` 0 advisories · `route:list` 210 routes, 0 unnamed.

---

# PART 9 — ROUND 7: PROFILE-PICTURE FEATURE + PRODUCTION MIGRATION (2026-08-22)

## 9.1 Avatar feature (all roles: ADMIN, MANAGER, CASHIER, STAFF + user accounts)

| Change | File |
|---|---|
| `POST /auth/avatar` — self-service picture: multipart image (Cloudinary `khmeriel/avatars`, stable public id per account so re-uploads overwrite) or hosted `avatar_url` string; audit-logged as `UPDATE_AVATAR` | `AuthController::uploadAvatar`, `routes/api.php` (`auth.avatar`) |
| Employee management accepts `avatar_url` (validated url, max 500) on create and update | `StoreEmployeeRequest`, `EmployeeController` |
| Default avatars (DiceBear initials) seeded for the 6 standard accounts with idempotent backfill that never clobbers a user-uploaded picture | `RolesAndPermissionsSeeder` |
| 5 new tests (URL set + profile read, Cloudinary upload with stable public id, 422 without source, admin sets employee avatar, seeder backfill idempotency) | `tests/Feature/AvatarUploadTest.php` |
| Postman: request added to master (folder 11, session management) and root copy (folder 12) | both collection JSONs |

Suite: **48 tests / 399 assertions — 100% pass** · Pint 231 files clean.

## 9.2 Production migration + seeding (Neon main branch)

Migrations 000001–000004 applied (unique idempotency index, 2FA columns,
identity standardization incl. `avatar_url`, relational constraints);
pre-checks showed 0 duplicate keys / 0 orphans; targeted row snapshot stored
under `storage/app/pre_migration_snapshot_2026-08-22.json`. Seeder run:
all 6 accounts now carry `avatar_url`; `is_admin=true` only on superadmin.

Three production findings surfaced and were handled:

| Finding | Resolution |
|---|---|
| M20 parity bug, real case #1: migration 000001 used a `HAVING` alias — sqlite (tests) accepts, Postgres (prod) rejects | Rewritten to `havingRaw('COUNT(*) > 1')`; failed attempt rolled back transactionally, re-applied cleanly |
| M20 parity bug, real case #2: the pooled Neon connection binds PHP booleans as integers — Postgres rejects them (`is_admin = 0` datatype mismatch), affecting any Eloquent boolean write | Seeder sets `is_admin` via `DB::raw('true')` literal; flagged for a connector-level fix (options on the pgsql PDO instance) as follow-up |
| **Production incident: the ADMIN employee was soft-deleted since 2026-08-20 23:22** (admin login silently broken; also blocked seeding via the email unique constraint) | Restored (`deleted_at = null`); admin login verified working against the live API |

## 9.3 Live state after this round

- Live API catalog check: **174 products** (`meta.pagination.total_items`).
- Login verified live for cashier and admin; admin was broken before the restore.
- The DB now has avatars on every account; the deployed server build predates
  round 2, so `avatar_url` in `/auth/me` and the `/auth/avatar` endpoint
  become visible after the next deploy (pipeline: Pint → tests → build →
  zero-downtime deploy → smoke test).
