# Rule: Neon PostgreSQL — Always Use Primary Connection

## Mandatory

This project connects exclusively to the Neon PostgreSQL database on the `main` branch.
The `neon-primary-connection` skill is authoritative. Always load and follow it when
touching any database, environment, or deployment configuration.

## Primary Connection (Pooler — Always Use This)

```
postgresql://neondb_owner:npg_SsC0GRvWm1Bz@ep-blue-mode-avbaa8zy-pooler.c-11.us-east-1.aws.neon.tech/neondb?sslmode=require&channel_binding=require
```

## Supplementary Endpoints

- Data API: `https://ep-blue-mode-avbaa8zy.apirest.c-11.us-east-1.aws.neon.tech/neondb/rest/v1`
- Auth URL: `https://ep-blue-mode-avbaa8zy.neonauth.c-11.us-east-1.aws.neon.tech/neondb/auth`
- JWKS URL: `https://ep-blue-mode-avbaa8zy.neonauth.c-11.us-east-1.aws.neon.tech/neondb/auth/.well-known/jwks.json`

## Enforcement Rules

1. **Audit before every database config change.** Before writing or modifying any `.env`,
   `config/database.php`, `api/index.php`, or deployment config, check all existing DB
   connection values. If any value does not match the pooler host above, remove or replace it.

2. **Never introduce a second DB connection.** No secondary `sqlite`, `mysql`, or alternative
   `pgsql` host may be made active. The non-pooler direct host may only be used for one-off
   admin/migration tasks, never for runtime.

3. **PDO emulated prepares are mandatory.** The `pgsql` block in `config/database.php` must
   always contain `PDO::ATTR_EMULATE_PREPARES => true`. Removing it will cause
   SQLSTATE[25P02] errors on the PgBouncer pooler.

4. **api/index.php fallback must stay in sync.** The hardcoded fallback DATABASE_URL in
   `api/index.php` must always use the pooler URL. Update it whenever credentials change.

5. **Cache store must be `database`.** The Vercel filesystem is read-only. Never switch
   `CACHE_STORE` to `file` in production.

6. **All connection methods are active.** The pooler (primary), Data API, and Auth/JWKS
   endpoints are all live. Use the appropriate one for each use case as defined in the skill.
