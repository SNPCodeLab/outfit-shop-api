---
name: standard-user-reset-protocol
description: Protocol for wiping all existing user/employee data and seeding the 4 standard RBAC roles (Admin, Manager, Cashier, Staff) with verified local email domains and standardized credentials.
---

# Standard User Reset Protocol

Use this protocol to reset the identity layer of the system to a known good state for testing or production handovers.

## 1. Wipe & Seed Logic
Always perform the wipe and seed in a single transaction block to ensure the system is never left without an admin account.

### Key Steps:
1.  **Truncate**: Wipe `employees` and `users` tables.
2.  **Hashing**: Use a standard `password_hash` (e.g., `$2y$12$jRg4MlzbF1E+N+h86+fGqkM+8/BxWNmbu+Hvk0UWHSg=` for `Secret123`).
3.  **Local Domain**: Use the official project domain for emails: `@api.kesararamwithdigital.tech`.

## 2. SQL Reference
```sql
TRUNCATE TABLE employees CASCADE;
INSERT INTO employees (...) VALUES (...);
```

## 3. Local Storage Security
- Actual credentials and sensitive seeding scripts must be stored in `LOCAL_CREDENTIALS.md`.
- Ensure `LOCAL_CREDENTIALS.md` is listed in `.gitignore` to prevent leakage to the remote repository.

## 4. Verification
After seeding, verify only 4 accounts exist:
```sql
SELECT role, username, email FROM employees;
```
