# Execution Log: Database Relationship Fixes
**Target:** NeoDB Production
**Estimated Downtime:** Zero (CONCURRENTLY indexing used)

## 1. Pre-Execution Checklist
- [ ] Database backup completed.
- [ ] Staging environment test passed.
- [ ] DB traffic monitor active.

## 2. Step-by-Step Execution Guide

### Step 1: Missing Index Implementation
Run `docs/ops/02_migrate_fix.sql`. 
*Note:* This script uses `CREATE INDEX CONCURRENTLY` which does not block writes to the tables. However, it takes longer than a standard CREATE INDEX.

```bash
# Example if psql was available:
# psql "$DATABASE_URL" -f docs/ops/02_migrate_fix.sql
```

### Step 2: Post-Fix Validation
Run `docs/ops/04_validation.sql` to ensure all indexes were created and no new structural issues appeared.

### Step 3: Performance Audit
Monitor the following for 15 minutes after indexing:
- `pg_stat_activity` for long-running queries.
- CPU usage on NeoDB dashboard.

## 3. Estimated Downtime
**0 Seconds.** The use of `CONCURRENTLY` ensures that tables remain readable and writable during index builds.

## 4. Rollback Plan
If any degradation occurs, run `docs/ops/03_rollback.sql` to drop the new indexes.
