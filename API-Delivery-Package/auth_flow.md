# Authentication Flow & 4-Tier RBAC Guide

The system uses **Laravel Sanctum Bearer Tokens** with **Spatie Role-Based Access Control (RBAC)**.

---

## 🔐 1. The 4 Access Tiers

```
┌────────────────────────────────────────────────────────────────────────────┐
│ TIER 0: PUBLIC / GUEST                                                     │
│ • Endpoints: /products, /categories, /variants, /brands, /marketing/banners│
│ • Permissions: Read-only catalog discovery. No token required.             │
├────────────────────────────────────────────────────────────────────────────┤
│ TIER 1: STAFF (Floor & Warehouse)                                          │
│ • Endpoints: /stock-movements, /variants/barcode/{barcode}, /alerts/active │
│ • Permissions: Real-time stock lookups, shelf replenishment logs.          │
├────────────────────────────────────────────────────────────────────────────┤
│ TIER 2: CASHIER (Touch POS Register)                                       │
│ • Endpoints: /sales/checkout, /shifts/open, /shifts/close, /customers [CR] │
│ • Permissions: Fast checkout, 10% VAT calculation, Z-Report shifts.        │
├────────────────────────────────────────────────────────────────────────────┤
│ TIER 3: MANAGER (Store Operations & Purchasing)                            │
│ • Endpoints: /purchases, /suppliers, /stock-movements/adjust, /sales/void  │
│ • Permissions: Catalog CRUD, supplier PO lifecycle, inventory adjustments. │
├────────────────────────────────────────────────────────────────────────────┤
│ TIER 4: ADMIN (Master Executive Console)                                   │
│ • Endpoints: /employees, /auth/register, /audit-logs, /dashboard/stats     │
│ • Permissions: Full system superuser, timesheets, security audit trail.    │
└────────────────────────────────────────────────────────────────────────────┘
```

---

## 🔄 2. Complete Authentication Lifecycle

### Step 1: Login Request
**`POST /api/v1/auth/login`**

```json
{
  "username": "cashier1",
  "password": "Password123!"
}
```

### Step 2: Login Response (`200 OK`)
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "token": "1|q6wE8rT0yU1iO3pA5sD7fG9hJ...",
    "employee": {
      "employee_id": 1,
      "employee_name": "Sokha Chan",
      "position": "CASHIER",
      "role": "CASHIER",
      "store_id": 1
    }
  },
  "request_id": "9b1deb4d-3b7d-4bad-9bdd-2b0d7b3dcb6d"
}
```

### Step 3: Attaching Header to Subsequent Requests
For all authenticated endpoints, attach:
```http
Authorization: Bearer 1|q6wE8rT0yU1iO3pA5sD7fG9hJ...
```

### Step 4: Token Validation & Current User Profile
**`GET /api/v1/auth/me`**
Returns current authenticated employee details and verified role.

### Step 5: Logout & Token Invalidation
**`POST /api/v1/auth/logout`**
Deletes current Sanctum access token from the database. Client must wipe local token storage.
