---
name: api-endpoint-audit-protocol
description: Protocol for auditing every API endpoint in the OutfitShop-Backend-API production system - full route sweep with all RBAC roles, real CRUD lifecycle tests, failure classification (real defect vs stale-build vs harness artifact), fix verification against the current code locally before deploying, and post-deploy re-verification. Use whenever the user asks to "test all endpoints", "check the whole API", "audit endpoints", "sweep all routes", "test CRUD for all users/roles", "find what fails and fix it", or wants a keep/delete endpoint consolidation report.
---

# API Endpoint Audit Protocol

Audits the live API surface end to end, fixes what fails, and produces a
before/after report plus a keep/delete consolidation list. Built from the
2026-08-22 full audit of 204 routes / 357 sweep requests / 53 lifecycle
steps, which found the production password-hashing outage, the variant
PATCH defect, and the Postgres boolean-binding class of failures.

## Runtime facts to remember (discovered the hard way)

These make failures on production that never reproduce locally:

1. **Vercel PHP cannot create bcrypt hashes when BCRYPT_ROUNDS resolves
   invalidly** (hosting env carries empty/0). `password_hash` throws a
   ValueError that `@` cannot silence. Verify worked all along, so logins
   succeeded while every password-SETTING endpoint 500'd. Guarded by
   `app/Hashing/FallbackHasher.php` (cost clamped 4-31, round-trip probe)
   and `PostgresBooleanConnection` - do not revert these.
2. **Laravel binds PHP booleans as integers**; Postgres rejects them
   (SQLSTATE 42804). Never write raw `true/false` payloads to Postgres
   outside the custom connection; CI's Postgres stage enforces parity.
3. **Postgres disallows SELECT aliases in HAVING** - sqlite tests pass
   where prod breaks. Use `havingRaw('COUNT(*) > 1')`.
4. Login throttle is 5/min per IP - burst harness logins fail silently to
   401 storms that look like an auth outage (see artifact rules below).
5. One PHPUnit process reuses the app instance: the Sanctum guard caches
   the first authenticated user, so follow-up requests in the same test
   authenticate regardless of token. Call `$this->app['auth']->forgetGuards()`
   between requests when asserting token-invalid states.

## Phase 1 - Export the route inventory

```bash
php artisan route:list --path=api --json > /tmp/routes_api.json
```

Note the middleware field contains FULL class names
(`App\Http\Middleware\CheckRole:ADMIN`), not aliases. Tier mapping:
no `Authenticate` = tier 0 guest; `Authenticate` alone = tier 1 staff;
`CheckRole:CASHIER,MANAGER,ADMIN` = 2; `CheckRole:MANAGER,ADMIN` = 3;
`CheckRole:ADMIN` only = 4. Route URIs already include `api/v1/` - strip
BOTH segments when the base URL already ends in `/api/v1` (double-prefix
was a real 404-everything bug).

## Phase 2 - Run the sweep

Use `scripts/endpoint_audit.py` (role-tiered request per route + guest
auth probe, throttle-aware retries, validation-probe payloads for
dangerous mutations so they return 422 with zero side effects):

```bash
export API_BASE="https://api.kesararamwithdigital.tech/api/v1"
export API_ADMIN_PW=... API_MANAGER_PW=... API_CASHIER_PW=... API_STAFF_PW=...
python3 scripts/endpoint_audit.py && python3 scripts/classify_audit.py
```

Classify results with these verdicts - the classification IS the audit:

| Pattern | Verdict |
|---|---|
| Guest 200 on a protected route | Real security gap (fix in code) |
| Guest 401 on protected route | Correct |
| Role gets 403 on a higher tier | Correct RBAC |
| 405 on PATCH/new routes | Stale deployed build - fix is deploy, verify current code locally first |
| 404 on new routes | Stale build (same as above) |
| 422 on empty-payload mutation probe | Healthy (validation works) |
| 500 | Real defect - root-cause before touching code |
| Staff/cashier 401 burst across MANY endpoints | Harness artifact: login rate limit - re-verify with a fresh single login before believing it |
| 404 on `{id}` substituted as `1` | Placeholder-id artifact (entity id 1 never existed) - not a defect |
| DELETE /customers 405 | By design (GDPR erasure replaces deletion) |

## Phase 3 - Real CRUD lifecycle

Use `scripts/lifecycle_audit.py` (create/read/PATCH/delete on
TESTAUDIT-prefixed entities with cleanup, checkout->receipts->void on a
dedicated test variant, shifts open/close, transfers create->cancel,
cart, wishlist, exports). Two rules:

- Financial/dangerous flows get REAL entities created for the audit and
  cleaned up (void the sale, cancel the transfer, delete the product).
- A lifecycle 422 with a WRONG-FIELD payload is a harness bug, not an API
  bug - retest with fields matching the controller's `validate()` before
  reporting. Known correct field names: stock adjust wants
  `movement_type`+`quantity`+`note`; estimates want `customer_id`; webhooks
  want `url`+`event_type`; wishlist toggle wants `customer_id`+`product_id`.

## Phase 4 - Verify against current code BEFORE deploying

Serve the current working tree locally - it connects to the same
production Neon database through `.env`, giving exact parity:

```bash
php artisan serve --host=127.0.0.1 --port=8011
# point the scripts' API_BASE at http://127.0.0.1:8011/api/v1 and re-run
```

A production failure that passes here means "stale build" (deploy) or
"runtime-only" (see runtime facts). A failure here is a code bug: fix,
add a regression test, run `php artisan test` + `vendor/bin/pint`.

## Phase 5 - Deploy and re-verify

Mandatory: follow `checkpoint-push-protocol` (DB integrity, AUTH_OK,
Pint) before pushing to `main` (mirrors to `docs`, triggers the 6-stage
pipeline). Watch with `gh run watch`/`gh run view`. After green:

1. Re-run the failed steps against production - confirm each fixed
   endpoint returns the expected status.
2. For hashing/auth fixes, prove the FULL loop (create account -> reset
   password -> login with the runtime-created hash).
3. Clean up every TESTAUDIT-* entity via tinker soft-deletes.

## Phase 6 - Report

Produce a before/after table (sweep OK count, lifecycle score, each fixed
endpoint with old->new status), the failure classification, and the
keep/delete consolidation: canonical routes to KEEP (171 as of 2026-08-22)
versus the 19 deprecated aliases to DELETE at sunset (2027-12-31) - the
list lives in `docs/reports/ENDPOINT_AUDIT_AND_CONSOLIDATION_2026-08-22.md`
section 5. State the one-surface architecture explicitly: one base URL,
one Neon database, branches via `store_branches`/`store_inventories`/
`pos_shifts`/`stock-transfers`.

## Scripts

- `scripts/endpoint_audit.py` - full sweep (writes
  `/tmp/endpoint_audit_prod.json`)
- `scripts/classify_audit.py` - expected-status classification
- `scripts/lifecycle_audit.py` - real CRUD lifecycle (writes
  `/tmp/lifecycle_audit_prod.json`)

Credentials come from env vars, never from the skill files. Set
`API_BASE` to override the target (local server vs production).
