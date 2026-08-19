---
name: neon-primary-connection
description: >
  Authoritative Neon PostgreSQL primary connection skill for OutfitShop SS-MIS API.
  Defines all connection methods, credentials, endpoints, and configuration rules
  for the Neon database branch (main). Always use this skill when configuring
  database connections, .env files, api/index.php, config/database.php, or any
  infrastructure that touches the database. Supersedes any other database
  connection configuration found in the project.
---

# Neon PostgreSQL — Primary Connection Skill

This is the SINGLE SOURCE OF TRUTH for all database connections in the OutfitShop SS-MIS API.
Whenever you touch database config, env files, or infrastructure code, this skill takes precedence.
Remove or replace any conflicting connection before applying these values.

---

## 1. Active Branch

- **Branch**: `main`
- **Database**: `neondb`
- **Owner**: `neondb_owner`

---

## 2. Connection Methods (All Active — Use All in Config)

### 2.1 Primary Pooled Connection (Laravel DB_CONNECTION — ALWAYS PRIMARY)

Use for all application database queries. PgBouncer transaction-mode pooler.

```
postgresql://neondb_owner:npg_SsC0GRvWm1Bz@ep-blue-mode-avbaa8zy-pooler.c-11.us-east-1.aws.neon.tech/neondb?sslmode=require&channel_binding=require
```

Parsed values for `.env`:

```dotenv
DB_CONNECTION=pgsql
DB_HOST=ep-blue-mode-avbaa8zy-pooler.c-11.us-east-1.aws.neon.tech
DB_PORT=5432
DB_DATABASE=neondb
DB_USERNAME=neondb_owner
DB_PASSWORD=npg_SsC0GRvWm1Bz
DB_SSLMODE=require
DB_OPTIONS=channel_binding=require
```

CRITICAL — Because this host is a PgBouncer pooler (transaction mode), prepared statements
are NOT supported. Always set `PDO::ATTR_EMULATE_PREPARES => true` in `config/database.php`
under the `pgsql` connection options. Failure to do this causes SQLSTATE[25P02] errors.

### 2.2 Neon Data API (HTTP REST — for lightweight reads/writes without a persistent connection)

Base URL:

```
https://ep-blue-mode-avbaa8zy.apirest.c-11.us-east-1.aws.neon.tech/neondb/rest/v1
```

Use for: serverless edge functions, health checks, or operations where a full TCP connection
is undesirable. Authenticate using the `neondb_owner` credentials or a JWT from the Auth URL below.

### 2.3 Neon Auth (JWT / JWKS — for token-based auth flows)

Auth URL:

```
https://ep-blue-mode-avbaa8zy.neonauth.c-11.us-east-1.aws.neon.tech/neondb/auth
```

JWKS URL (public key endpoint for JWT verification):

```
https://ep-blue-mode-avbaa8zy.neonauth.c-11.us-east-1.aws.neon.tech/neondb/auth/.well-known/jwks.json
```

Use for: validating JWTs issued by Neon Auth against the public JWKS endpoint.

---

## 3. Mandatory config/database.php — pgsql Block

The `pgsql` connection in `config/database.php` MUST always contain the following `options` key.
Never remove it. Never omit it.

```php
'pgsql' => [
    'driver' => 'pgsql',
    'url' => env('DB_URL') ?: env('DATABASE_URL'),
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', '5432'),
    'database' => env('DB_DATABASE', 'laravel'),
    'username' => env('DB_USERNAME', 'root'),
    'password' => env('DB_PASSWORD', ''),
    'charset' => env('DB_CHARSET', 'utf8'),
    'prefix' => '',
    'prefix_indexes' => true,
    'search_path' => 'public',
    'sslmode' => env('DB_SSLMODE', 'require'),
    // Neon uses PgBouncer in transaction-pooling mode.
    // Prepared statements are not supported across pooled connections,
    // so we disable them here to prevent SQLSTATE[25P02] errors.
    'options' => [
        PDO::ATTR_EMULATE_PREPARES => true,
    ],
],
```

---

## 4. api/index.php — Serverless Fallback Connection String

The fallback DATABASE_URL hardcoded in `api/index.php` MUST always point to the pooler URL.
Keep this line in sync whenever credentials change:

```php
$dbUrl = getenv('DATABASE_URL') ?: (getenv('POSTGRES_URL') ?: 'postgresql://neondb_owner:npg_SsC0GRvWm1Bz@ep-blue-mode-avbaa8zy-pooler.c-11.us-east-1.aws.neon.tech/neondb?sslmode=require&channel_binding=require');
```

---

## 5. Vercel Environment Variables

When deploying to Vercel, ensure these are set in the project environment:

| Key | Value |
|---|---|
| `DATABASE_URL` | `postgresql://neondb_owner:npg_SsC0GRvWm1Bz@ep-blue-mode-avbaa8zy-pooler.c-11.us-east-1.aws.neon.tech/neondb?sslmode=require&channel_binding=require` |
| `DB_CONNECTION` | `pgsql` |
| `DB_HOST` | `ep-blue-mode-avbaa8zy-pooler.c-11.us-east-1.aws.neon.tech` |
| `DB_PORT` | `5432` |
| `DB_DATABASE` | `neondb` |
| `DB_USERNAME` | `neondb_owner` |
| `DB_PASSWORD` | `npg_SsC0GRvWm1Bz` |
| `DB_SSLMODE` | `require` |

---

## 6. Cache Store

Cache MUST use the `database` driver backed by the same Neon connection.
Do NOT switch cache to `file` or `array` in production — the Vercel filesystem is read-only.

```dotenv
CACHE_STORE=database
```

The `cache` and `cache_locks` tables must exist in the `neondb` database on the `main` branch.

---

## 7. Conflict Resolution — If Another DB Connection Is Found

If any other `DB_HOST`, `DATABASE_URL`, or `DB_CONNECTION` value is found in any file
(`.env`, `api/index.php`, `config/database.php`, deployment configs, or CI/CD scripts)
that does NOT match the pooler host above, it must be:

1. Removed or replaced with the values from Section 2.1.
2. Never left as a secondary or fallback that could override the primary.

The non-pooler direct host (`ep-blue-mode-avbaa8zy.c-11.us-east-1.aws.neon.tech` without `-pooler`)
may only be used for one-off migrations or admin tasks, never for application runtime connections.

---

## 8. Quick Reference Cheatsheet

```
Pooler (primary):   ep-blue-mode-avbaa8zy-pooler.c-11.us-east-1.aws.neon.tech:5432/neondb
Data API:           https://ep-blue-mode-avbaa8zy.apirest.c-11.us-east-1.aws.neon.tech/neondb/rest/v1
Auth URL:           https://ep-blue-mode-avbaa8zy.neonauth.c-11.us-east-1.aws.neon.tech/neondb/auth
JWKS URL:           https://ep-blue-mode-avbaa8zy.neonauth.c-11.us-east-1.aws.neon.tech/neondb/auth/.well-known/jwks.json
Branch:             main
Database:           neondb
Username:           neondb_owner
SSL:                require + channel_binding=require
PDO:                ATTR_EMULATE_PREPARES = true (mandatory for pooler)
```
