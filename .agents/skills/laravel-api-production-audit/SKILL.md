---
name: laravel-api-production-audit
description: Comprehensive enterprise audit framework for Laravel REST API backends. Analyzes 11 architectural domains, performs gap analysis, generates a 4-week improvement roadmap, delivers frontend handoff checklists, and enforces production readiness KPIs.
---

# 🔎 Laravel API Production Readiness & Enterprise Audit Skill

This skill provides an exhaustive, automated framework to audit, score, and harden Laravel-based RESTful API backends for enterprise production readiness.

---

## 🎯 Triggers & Invocations
Activate this skill whenever the user asks:
- *"audit my api"*
- *"production ready check"*
- *"api improvement"*
- *"enterprise grade api"*
- *"backend gap analysis"*
- *"how to prepare my Laravel API for frontend handoff"*

---

## 🏛️ The 11 Audit Domains Framework

### 1. Response Format & API Enveloping
- **JSON Envelope Consistency**: Global format `{ success: bool, data: mixed, message: string, request_id: uuid }`.
- **Distributed Tracing**: `X-Request-Id` attached to request and response headers.
- **Error Code Dictionary**: Standardized string machine error codes (e.g., `ERR_INSUFFICIENT_STOCK`, `ERR_UNAUTHENTICATED`).
- **HTTP Status Codes**: Strict compliance (`200 OK`, `201 Created`, `400 Bad Request`, `401 Unauthorized`, `403 Forbidden`, `404 Not Found`, `422 Unprocessable`, `429 Too Many Requests`).
- **Pagination**: Structured `pagination: { total, per_page, current_page, last_page }`.
- **Filtering / Sorting Syntax**: JSON:API style `filter[...]`, `sort=-created_at,name`, and `include=...`.

### 2. Authentication & Role-Based Access Control (RBAC)
- **Token Expiry & Rotation**: Short-lived access tokens with single-use refresh token rotation (`POST /auth/refresh`).
- **Multi-Device Tracking & Revocation**: Named token creation with global kill-switch (`POST /auth/revoke-all`).
- **Dynamic 4-Tier Rate Limiting**: Role-based limits (`ADMIN: 300`, `MANAGER: 200`, `CASHIER: 100`, `STAFF: 50`, `PUBLIC: 30`).
- **Account Lockout Protection**: Automatic 15-minute lock upon 10 consecutive failed password attempts (`ERR_ACCOUNT_LOCKED`).
- **Multi-Factor Authentication (2FA)**: TOTP verification for high-privilege administrative accounts.

### 3. Database Architecture & Concurrency Safety
- **Index Optimization**: Foreign key constraints and composite indexes on high-frequency filter columns.
- **Eager Loading**: Elimination of N+1 query performance bottlenecks using `with([...])`.
- **Pessimistic Locking**: `lockForUpdate()` on critical inventory and balance deductions during concurrent checkouts.
- **Automated Backup Strategy**: Nightly scheduled compressed dumps with S3/R2 cloud replication and retention pruning.
- **Database Migrations & Seeders**: Fully reproducible schema lifecycle without raw manual SQL dependencies.

### 4. Caching & Memory Layer
- **Redis Integration**: High-speed memory caching for catalog, categories, and 2D matrix grids.
- **Cache Invalidation Lifecycle**: Event-driven cache purging on model update/create/delete events.
- **Cache Hit Target**: `>85%` cache hit ratio on read-heavy public endpoints.

### 5. Asynchronous Queues & Background Workers
- **Heavy Operation Offloading**: PDF reports, Excel spreadsheets, Cloudinary media processing, and email notifications dispatched to `app/Jobs/`.
- **Job Reliability**: Configured retry policies, timeouts, and failed job database tables.

### 6. Security Hardening & Data Protection
- **Security Headers**: HSTS (`max-age=31536000`), CSP, `X-Content-Type-Options: nosniff`, `X-Frame-Options: SAMEORIGIN`, `X-XSS-Protection`.
- **Admin IP Whitelisting**: CIDR restriction middleware on administrative endpoints.
- **Data Privacy (GDPR / PCI-DSS)**: Right to Data Portability export, Right to Erasure anonymization, and Zero Card Storage tokenization.
- **Input Validation & Sanitization**: Form requests, SQL injection prepared statements, and MIME file upload validations.

### 7. Performance & Latency Telemetry
- **Cached Response Target**: `<50ms`.
- **Uncached Query Target**: `<200ms`.
- **Memory Footprint**: Peak consumption `<64MB`.
- **APM Telemetry**: Real-time tracking of P95 latency and slowest endpoints (`GET /admin/performance`).

### 8. Testing & Quality Assurance
- **Automated Test Coverage**: Unit and Feature test suites covering all RBAC levels and transactional POS checkouts.
- **Pass Rate**: `100%` pass rate with zero unhandled regression breaks.

### 9. Monitoring & Observability
- **Structured Domain Logging**: 5 separated log channels (`pos.log`, `inventory.log`, `purchasing.log`, `security.log`, `admin.log`).
- **Live Health Endpoint**: Zero-dependency `GET /up` verifying database and storage connectivity.
- **API Analytics**: Tracking peak traffic hours, top endpoints, and role activity distribution.

- **API Documentation & Developer Experience (DX)**: Standardized error codes and Postman collections.

### 11. DevOps, CI/CD & Disaster Recovery
- **6-Stage CI/CD Pipeline**: Automated Lint (Pint) $\rightarrow$ Test (PostgreSQL container) $\rightarrow$ Build $\rightarrow$ Deploy $\rightarrow$ Smoke Test $\rightarrow$ Slack Notification.
- **Disaster Recovery SLA**: Target **RPO `<5 Minutes`** (Point-in-Time Recovery) and **RTO `<30 Minutes`** (Edge DNS failover).

---

## 🔄 7-Step Skill Execution Workflow

When running an audit, execute these 7 steps systematically:

```
[1. DISCOVERY] ──▶ [2. CODE AUDIT] ──▶ [3. SCORING] ──▶ [4. GAP ANALYSIS]
       │
       └──▶ [5. 4-WEEK ROADMAP] ──▶ [6. FRONTEND HANDOFF] ──▶ [7. KPI TARGETS]
```

### Step 1: DISCOVERY
Collect or inspect:
- Tech stack version (Laravel, PHP, Database, Cache).
- Endpoint inventory & RBAC architecture.
- Concurrent user expectations and database size.

### Step 2: CODE AUDIT
Evaluate each of the 11 domains. Mark items as:
- ✅ **Done** (Fully implemented and verified)
- ⚠️ **Partial** (Implemented but lacking edge-case safety or tests)
- ❌ **Missing** (Not implemented)

### Step 3: SCORING
Assign a score from 0 to 10 for each domain:
$$\text{Overall Score} = \frac{\sum \text{Domain Scores}}{110} \times 100\%$$

| Domain | Score (0–10) | Status | Key Strengths / Deficiencies |
| :--- | :---: | :---: | :--- |
| 1. Response Format | /10 | | |
| 2. Auth & RBAC | /10 | | |
| 3. Database Architecture | /10 | | |
| 4. Caching Layer | /10 | | |
| 5. Queue & Workers | /10 | | |
| 6. Security Hardening | /10 | | |
| 7. Performance & Latency | /10 | | |
| 8. Automated Testing | /10 | | |
| 9. Logging & Monitoring | /10 | | |
| 10. API Documentation | /10 | | |
| 11. DevOps & CI/CD | /10 | | |

### Step 4: GAP ANALYSIS
Prioritize findings into four tiers with actionable code fixes:
- 🔴 **CRITICAL** (Must fix before production deployment)
- 🟡 **HIGH** (Should fix before scaling or high traffic)
- 🟢 **MEDIUM** (Post-MVP enhancements)
- ⚪ **LOW** (Nice-to-have DX features)

### Step 5: 4-WEEK IMPROVEMENT ROADMAP
1. **Week 1: Core Foundation & Stability** (Rate limiting, token lifecycle, database backup, Redis caching).
2. **Week 2: Performance & Bulk Processing** (Queue offloading, bulk import/adjust, advanced filtering).
3. **Week 3: Advanced Business Features** (Webhooks, multi-store stock transfers, financial MIS reports).
4. **Week 4: Enterprise Hardening & Handover** (Security hardening, disaster recovery drill, SDK generation, CI/CD).

### Step 6: FRONTEND HANDOFF PACKAGE
Deliver the final handoff checklist:
- [ ] Base URL & Live Health Endpoint verified.
- [ ] Postman JSON collections published.
- [ ] Error Code Dictionary & Localized error messages documented.
- [ ] Auth & Token Rotation flow verified with sample credentials.
- [ ] Rate limits and retry-after headers communicated.
- [ ] Multi-language code snippets provided.

### Step 7: KPI TARGETS
| Metric | Benchmark Target | Production SLA |
| :--- | :---: | :---: |
| **Cached Endpoint Latency** | `< 50 ms` | Guaranteed |
| **Uncached Query Latency** | `< 200 ms` | Guaranteed |
| **Cache Hit Ratio** | `> 85%` | Guaranteed |
| **API Error Rate** | `< 0.2%` | Guaranteed |
| **System Uptime** | `99.9%` | Guaranteed |
| **Test Suite Pass Rate** | `100%` | Guaranteed |
| **Recovery Point Objective (RPO)** | `< 5 mins` | Guaranteed |
| **Recovery Time Objective (RTO)** | `< 30 mins` | Guaranteed |

---

## 📄 Output Report Template

```markdown
# 📊 API Production Readiness Audit Report

## 1. System Context & Overview
- **Backend Framework**: Laravel 11.x (PHP 8.x)
- **Database**: PostgreSQL 16
- **Architecture**: Monolithic Headless RESTful Gateway
- **RBAC Roles**: ADMIN, MANAGER, CASHIER, STAFF, PUBLIC

## 2. 11-Domain Audit Scoring
[Table with all 11 domain scores]

## 3. Prioritized Gap Analysis & Action Plan
### 🔴 Critical Findings
[Issue description, business impact, and Laravel code solution]

### 🟡 High Priority Findings
[Issue description and fix]

## 4. 4-Week Sprint Roadmap
- **Week 1**: Foundation & Security Baseline
- **Week 2**: Performance Optimization & Queues
- **Week 3**: Advanced Business Workflows & Webhooks
- **Week 4**: Final Hardening & Frontend Handover

## 5. Frontend Integration & Handover Checklist
- [x] Versioned API: `GET /api/v1/*`
- [x] Status Dashboard: `GET /status`

## 6. Target Production KPIs
- Latency: <50ms cached | <200ms uncached
- Error Rate: <0.2%
- RPO: <5 min | RTO: <30 min
```
