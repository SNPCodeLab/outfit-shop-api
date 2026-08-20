# API Conventions

Base URL (local): `http://127.0.0.1:8000/api/v1`  
Base URL (production): `https://api.kesararamwithdigital.tech/api/v1`

---

## HTTP Standards

| Action | Method | Path example | Description |
|---|---|---|---|
| **List** | `GET` | `/products` | Fetches a paginated collection of resources. |
| **Show** | `GET` | `/products/{id}` | Fetches a single resource by ID. |
| **Create** | `POST` | `/products` | Persists a new resource. Returns `201 Created`. |
| **Update** | `PUT` | `/products/{id}` | Replaces an existing resource. |
| **Patch** | `PATCH` | `/products/{id}` | Partially updates a resource. |
| **Delete** | `DELETE` | `/products/{id}` | Removes a resource. |
| **Action** | `POST` | `/sales/checkout` | Triggers a complex domain action (POS, Auth). |

---

## Authentication (Sanctum)

The API uses **Laravel Sanctum** Personal Access Tokens (Bearer).

1. **Login**: `POST /api/v1/auth/login`
2. **Token Usage**: Send `Authorization: Bearer <token>` in all private request headers.
3. **Logout**: `POST /api/v1/auth/logout` revokes the current session token.
4. **Token Rotation**: `POST /api/v1/auth/refresh` invalidates the current token and issues a new one.

### Access Levels (RBAC)

| Role | Access Tier |
|---|---|
| **PUBLIC** | Read-only catalog, health, and login. No token required. |
| **CASHIER** | POS checkout, customer registry, and shift management. |
| **MANAGER** | Inventory CRUD, purchase orders, and operational reports. |
| **ADMIN** | Employee management, audit logs, and system configuration. |

---

## Global Response Envelope

Every API response is wrapped in a standardized JSON envelope provided by `ApiResponse`.

### Success Response (`2xx`)

```json
{
  "success": true,
  "status_code": 200,
  "request_id": "9b1deb4d-3b7d-4bad-9bdd-2b0d7b3dcb6d",
  "timestamp": "2026-08-20T14:00:00+00:00",
  "message": "Operation completed successfully",
  "data": { ... },
  "meta": {
    "system": "OutfitShop-Backend-API",
    "api_version": "Version: 1.2.0",
    "processing_time_ms": 12
  }
}
```

### Error Response (`4xx` / `5xx`)

```json
{
  "success": false,
  "status_code": 422,
  "request_id": "9b1deb4d-3b7d-4bad-9bdd-2b0d7b3dcb6d",
  "timestamp": "2026-08-20T14:00:00+00:00",
  "message": "The provided data failed validation.",
  "error": {
    "code": "VALIDATION_ERROR",
    "type": "ValidationException",
    "message": "The given data was invalid.",
    "detail": {
      "email": ["The email address format is invalid."]
    }
  }
}
```

---

## Common Error Slugs

Clients should handle these machine-readable error codes:

| Code | HTTP | Description |
|---|---|---|
| `AUTHENTICATION_FAILED` | 401 | Invalid or expired Bearer token. |
| `FORBIDDEN_ACCESS` | 403 | Authenticated but lacks required role/permission. |
| `RESOURCE_NOT_FOUND` | 404 | The requested entity does not exist. |
| `VALIDATION_ERROR` | 422 | Input data failed schema validation rules. |
| `RATE_LIMIT_EXCEEDED` | 429 | Too many requests; respect the `Retry-After` header. |
| `INSUFFICIENT_STOCK` | 409 | POS checkout failed due to low inventory levels. |
| `SHIFT_CLOSED` | 403 | Cashier attempted a sale without an open shift. |
| `INTERNAL_SERVER_ERROR`| 500 | Unhandled server exception. |

---

## Best Practices

1. **Accept Header**: Always send `Accept: application/json`.
2. **Content-Type**: Always send `Content-Type: application/json` for write requests.
3. **Idempotency**: POS checkout requires an `X-Idempotency-Key` to prevent duplicate transactions on retry.
4. **Pagination**: Large collections are paginated by default. Look for `meta.pagination` and `links` in the response.
5. **Rate Limiting**: Public endpoints are limited to 60 req/min. Authenticated roles have higher thresholds.
