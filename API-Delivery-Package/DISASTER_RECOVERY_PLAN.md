# 🚨 Enterprise Disaster Recovery Plan & Operations Runbook

**System**: Retail Clothing Store Management System (SS-MIS / CSMS)  
**Database**: PostgreSQL 16 on Neon Cloud  
**API Engine**: Laravel 11 PHP 8.5 Monolithic Headless REST Gateway  

---

## 🎯 1. Disaster Recovery Service Level Agreements (SLAs)

| Metric | Target SLA | Strategy & Implementation |
| :--- | :---: | :--- |
| **RPO (Recovery Point Objective)** | **`< 5 Minutes`** | Continuous write-ahead log (WAL) archiving and automated Neon Cloud Point-in-Time Recovery (PITR) continuous snapshots. |
| **RTO (Recovery Time Objective)** | **`< 30 Minutes`** | Automated blue/green DNS failover with automated container rebuilds on Vercel / Cloudflare Edge. |

---

## 🏗️ 2. Database Replication & Backup Architecture

1. **Continuous Point-in-Time Recovery (PITR)**:
   - Neon PostgreSQL continuously archives transactions. The database can be restored to any exact second within the last 7 days.
2. **Nightly Compressed Physical Dumps**:
   - `php artisan db:backup --cloud --prune=30` executes every morning at `02:00 AM UTC`.
   - Compressed `.dump` files synced to encrypted AWS S3 / Cloudflare R2 bucket (`s3://csms-backups/backups/`).
3. **Multi-Region Replica**:
   - Read-only replica stationed in secondary cloud availability zone for read-scaling and instant primary failover.

---

## 📋 3. Emergency Disaster Recovery Runbook (Step-by-Step)

### Scenario A: Primary Database Corruption or Accidental Data Loss
1. **Freeze Writes**:
   ```bash
   php artisan down --secret="emergency-restore-token"
   ```
2. **Restore Point-in-Time via Neon CLI**:
   ```bash
   # Restore branch to 5 minutes prior to incident
   neon branches create --parent main --name restore-point --timestamp "2026-08-17T12:00:00Z"
   ```
3. **Update Database Connection in Environment**:
   ```bash
   # Update DB_URL or DB_HOST in production .env
   php artisan config:clear
   php artisan cache:clear
   ```
4. **Run Verification & Reopen Gateway**:
   ```bash
   php artisan test --filter=POSCheckoutApiTest
   php artisan up
   ```

---

### Scenario B: Complete Server / Host Outage (Automated Failover)
1. **DNS Failover**: Cloudflare automatically routes traffic to standby secondary API gateway.
2. **Container Re-hydration**: Trigger automated Vercel/Docker redeploy from `main` branch.
3. **Health Verification**:
   ```bash
   curl -I https://api.kesararamwithdigital.tech/api/v1/health
   # Expected: HTTP 200 OK
   ```

---

## 🧪 4. Backup Testing Schedule

- **Automated Daily Validation**: Laravel scheduler runs nightly backup and verifies file integrity.
- **Monthly Dry-Run Restoration**: DevOps team clones latest S3 backup to staging database on the 1st of every month to verify 0-error restoration within 15 minutes.
