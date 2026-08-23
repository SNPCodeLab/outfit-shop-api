---
name: static-test-rbac-protocol
description: >
  Authoritative Protocol for AI Agents and automated test suites. Strictly forbids
  creating ephemeral, temporary, or throwaway test employees in the database.
  Mandates using ONLY the 4 locked, permanent RBAC employee accounts and 2 system users
  across all audits, test suites, Postman sweeps, and runtime validations.
---

# Static Test RBAC Protocol (Zero-Ephemeral Accounts Policy)

This skill defines the strict policy for authentication and RBAC testing across OutfitShop SS-MIS.

---

## 1. Absolute Golden Rule: NEVER Create Ephemeral Test Employees

> [!CAUTION]
> **STRICT BAN ON EPHEMERAL / TEMPORARY TEST STAFF**
> - **DO NOT** create temporary test employees (e.g. `TESTAUDIT-EMP-*`, `empdebug*`, `rtemp*`, `hashfix*`).
> - **DO NOT** create and then soft-delete test staff records.
> - **DO NOT** generate randomized or disposable employee accounts in automated scripts or manual testing.
> - All testing, audits, lifecycle runs, and API sweeps **MUST EXCLUSIVELY** reuse the fixed, pre-seeded accounts detailed below.

---

## 2. Locked Static Accounts (Single Source of Truth)

The database maintains exactly 4 permanent RBAC employee profiles and 2 system user profiles.

### 2.1 The 4 Core RBAC Employee Accounts

| Employee ID | Name | Role | Position | System Email | Username |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `1` | **System Administrator** | `ADMIN` | Chief Executive Officer | `admin@api.kesararamwithdigital.tech` | `admin` |
| `2` | **Sokha Dara** | `MANAGER` | Store Operations Manager | `manager@api.kesararamwithdigital.tech` | `manager` |
| `3` | **Bopha Rathana** | `CASHIER` | Senior POS Operator | `cashier@api.kesararamwithdigital.tech` | `cashier` |
| `4` | **Vibol Sok** | `STAFF` | Inventory Assistant | `staff@api.kesararamwithdigital.tech` | `staff` |

### 2.2 System Dashboard Users

| User ID | Name | Role / Level | System Email |
| :--- | :--- | :--- | :--- |
| `2` | **Frontend Developer** | `User (Staff / Developer)` | `frontend@api.kesararamwithdigital.tech` |
| `3` | **Admin User** | `Superadmin (Admin)` | `superadmin@api.kesararamwithdigital.tech` |

---

## 3. How to Test Employee CRUD Without Polluting the DB

When testing the `POST /api/v1/employees`, `PATCH /api/v1/employees/{id}`, or `DELETE /api/v1/employees/{id}` endpoints:

1. **Validation Probes (Recommended)**:
   - Send empty or malformed payloads to verify that validation correctly returns `422 Unprocessable Entity`.
   - Send unauthorized tokens to verify `403 Forbidden` / `401 Unauthorized`.
2. **Transactional Dry-Runs (If Mutation Needed in Unit Tests)**:
   - In PHPUnit tests, use `RefreshDatabase` or `DatabaseTransactions` so transactions roll back automatically at test teardown.
3. **Never Mutate Production Employee Table with Disposable Rows**:
   - Live scripts (`endpoint_audit.py`, `lifecycle_audit.py`) must only read from existing accounts (`ID 1..4`) and must NEVER insert disposable rows into the live database.

---

## 4. Periodic Health Assertion

Any database audit or CI health check MUST verify that NO unauthorized or soft-deleted test accounts exist:

```sql
SELECT employee_id, employee_name, email, role, status, deleted_at 
FROM employees;
```

**Pass Criteria**:
- Exactly 4 active rows exist (`employee_id` 1, 2, 3, 4).
- `deleted_at` is `NULL` for all rows.
- Zero rows with `@test.local` or temporary prefixes.

---

## 5. Telemetry & Log Cleanliness Standard

- Automated load/stress tests and route sweeps produce thousands of rows in `api_logs`, `audit_logs`, and `personal_access_tokens`.
- Whenever completing major audits or preparing for handoff/production, run the clean truncation command:
  ```sql
  TRUNCATE TABLE api_logs, audit_logs, personal_access_tokens, sessions, cache, cache_locks RESTART IDENTITY CASCADE;
  ```
