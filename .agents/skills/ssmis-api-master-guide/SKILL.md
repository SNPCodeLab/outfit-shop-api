---
name: ssmis-api-master-guide
description: >
  Primary Authoritative Skill for SS-MIS (OutfitShop) API. 
  Covers infrastructure management, real-time database integrity, 
  and enterprise POS transactional logic.
---

# SS-MIS Master API Architecture & Operational Skill

This skill defines the technical and operational standards for the KhmeRiel / CSMS Store Stock & POS REST API.

## 1. Primary Infrastructure Status
- **Production Gateway**: `https://api.kesararamwithdigital.tech/api/v1`
- **Primary Database**: Neon PostgreSQL — branch `main`, database `neondb`
  - Pooler (primary): `ep-blue-mode-avbaa8zy-pooler.c-11.us-east-1.aws.neon.tech:5432`
  - Data API: `https://ep-blue-mode-avbaa8zy.apirest.c-11.us-east-1.aws.neon.tech/neondb/rest/v1`
  - Auth URL: `https://ep-blue-mode-avbaa8zy.neonauth.c-11.us-east-1.aws.neon.tech/neondb/auth`
  - JWKS URL: `https://ep-blue-mode-avbaa8zy.neonauth.c-11.us-east-1.aws.neon.tech/neondb/auth/.well-known/jwks.json`
  - Full connection string: `postgresql://neondb_owner:npg_SsC0GRvWm1Bz@ep-blue-mode-avbaa8zy-pooler.c-11.us-east-1.aws.neon.tech/neondb?sslmode=require&channel_binding=require`
  - See `neon-primary-connection` skill for complete configuration rules.
- **Media CDN**: Cloudinary Edge (`od8t271n`)
- **Framework**: Laravel 12 (Hardened for Serverless/Vercel)

## 2. Verified Operational Capabilities

### 2.1 Real-Time Data Integrity
- **Multi-Dimensional SKU Matrix**: Full support for Size $\times$ Color variant tracking with atomic inventory locking.
- **Brand Portfolio**: Verified live data for Louis Vuitton, Adidas, Nike, Gucci, Prada, and Dior.
- **Entity Linking**: Zero-null integrity between Products, Brands, Categories, and Variants.

### 2.2 POS Transactional Engine
- **Idempotency**: `X-Idempotency-Key` requirement for safe network retries.
- **Inventory Deduction**: Verified real-time atomic decrement on successful checkout.
- **Tax Logic**: Fixed 10.00% Tax-Exclusive (VAT) calculation engine.

### 2.3 Access Tiers (RBAC)
- **Level 1 (Public)**: High-speed catalog and status discovery.
- **Level 2 (Staff)**: Transactional endpoints for sales and shifts.
- **Level 3 (Manager)**: Operational control over inventory and catalog.
- **Level 4 (Admin)**: Full system pulse, security logs, and master command.

## 3. Mandatory Vercel Deployment Hardening
To ensure boot-stability in serverless environments, the following logic is enforced in `api/index.php`:
1. **URL Decomposer**: Manually parses `DATABASE_URL` to prevent port-parsing errors.
2. **ENV Injection**: Forcefully injects `APP_KEY` and drivers into `$_ENV` to bypass cold-start latency.
3. **Bootstrap Redirect**: Forcibly moves `bootstrap/cache` to `/tmp/cache` to handle read-only filesystems.

## 4. Maintenance Protocols
- **Testing**: Use `api_validator.php` (internal tool) for full-stack health audits.
- **Logging**: Dual-channel logging (Standard Laravel + Dedicated POS Audit Trail).
- **Audit**: Immutable trail for every price change or inventory adjustment.
