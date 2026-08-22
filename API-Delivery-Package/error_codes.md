# Machine-Readable Error Code Dictionary

All error responses return standard HTTP status codes along with a specific machine-readable `error_code` string inside the JSON body.

## 📋 Error Response Schema

```json
{
  "success": false,
  "message": "Insufficient stock for SKU [SHIRT-BLK-M]. Requested: 5, Available: 2.",
  "error_code": "ERR_INSUFFICIENT_STOCK",
  "errors": {
    "items.0.quantity": [
      "Quantity exceeds current physical availability"
    ]
  },
  "request_id": "9b1deb4d-3b7d-4bad-9bdd-2b0d7b3dcb6d"
}
```

---

## 🗂️ Complete Error Codes Directory

| HTTP Status | `error_code` | Trigger Condition | Recommended Frontend Action |
| :---: | :--- | :--- | :--- |
| `400` | `ERR_INSUFFICIENT_STOCK` | Sale requested more units than On Hand physical inventory | Show stock warning modal; clamp quantity selector to maximum available |
| `400` | `ERR_SHIFT_ALREADY_OPEN` | Cashier tried to open a register shift while one is already active | Redirect cashier directly to the POS sales register screen |
| `400` | `ERR_NO_OPEN_SHIFT` | Cashier tried to process checkout or close shift without an active shift | Display "Open Register Shift" dialog prompt |
| `400` | `ERR_ALREADY_CONVERTED` | Estimate was already converted to an invoice | Disable the "Convert to Invoice" button; show paid invoice badge |
| `400` | `ERR_STOCK_ADJUST_FAILED`| Manual stock adjustment failed or violated inventory constraints | Check quantity sign (+/-) and allowed adjustment enum types |
| `400` | `ERR_INVALID_PAYMENT` | Payment amount tendered is less than the sale grand total | Prompt user to tender full or higher payment amount |
| `401` | `ERR_UNAUTHENTICATED` | Token missing, invalid, expired, or revoked | Clear client-side token storage and redirect to `/login?expired=1` |
| `403` | `ERR_FORBIDDEN` | User role lacks required permission (e.g. Cashier accessing `/employees`) | Hide restricted controls or display "Access Restricted" alert |
| `404` | `ERR_NOT_FOUND` | Variant, product, customer, or sale ID not found in database | Display 404 resource empty state |
| `422` | `ERR_VALIDATION_FAILED` | Laravel request validation failed on payload fields | Highlight specific input fields in red using the `errors` dictionary |
| `429` | `ERR_TOO_MANY_REQUESTS` | Rate limit exceeded (e.g. > 10 login attempts/min) | Display exponential backoff timer countdown |
| `500` | `ERR_INTERNAL_SERVER` | Unhandled database or server exception | Prompt user to retry; log `request_id` to error reporting system |

---

## v1.2 Standards Hardening — New Error Codes (2026-08-22)

Codes introduced by the REST-standards, security, and identity rounds. All use
the standard envelope (`success:false`, `error.code`, `error.detail`).

| HTTP Status | `error.code` | Trigger Condition | Recommended Frontend Action |
| :---: | :--- | :--- | :--- |
| `401` | `AUTHENTICATION_FAILED` | Bad credentials at login (generic message - no field hints), missing/invalid Bearer token, or invalid 2FA code context | Clear token, redirect to login; do not reveal which field failed |
| `401` | `INVALID_TWO_FACTOR_CODE` | Submitted 2FA code does not match the TOTP secret (real RFC 6238 verification, ±30s window) | Keep user on 2FA screen, allow retry; code rotates every 30s |
| `401` | `INVALID_RESET_TOKEN` | Password-reset token wrong, expired, or already used (single-use) | Restart the forgot-password flow |
| `403` | `FORBIDDEN_ACCESS` | Role/permission/ability gate rejected the request (e.g. STAFF writing customers, token lacking `sales.void`) | Hide the action; surface "insufficient permission" |
| `409` | `DUPLICATE_BRANCH_CODE` | Store branch create with an existing `branch_code` | Offer to open the existing branch record |
| `409` | `TWO_FACTOR_NOT_CONFIGURED` | Verify-2FA called before setup-2FA | Direct user to POST `/auth/2fa/setup` first |
| `422` | `ERR_CHECKOUT_RULE_VIOLATION` | POS business rule at checkout: insufficient stock, invalid quantity, unknown variant, voiding an already-voided sale/estimate | Show the business message (e.g. "Insufficient stock for SKU [...]"), correct and retry |
| `422` | `VALIDATION_ERROR` | Request validation failed; `error.detail.fields[]` lists each field, rule, and message | Inline field errors next to inputs |
| `423` | `ACCOUNT_LOCKED` | 10 failed logins; 15-minute lockout | Show lockout countdown; do not allow retry until release |
| `429` | `TOO_MANY_REQUESTS` | Rate limit exceeded (PUBLIC 30 / STAFF 50 / CASHIER 100 / MANAGER 200 / ADMIN 300 per minute); `Retry-After` header present | Back off for `Retry-After` seconds; queue retries |
| `500` | `INTERNAL_SERVER_ERROR` | Unexpected server fault (generic message in production; debug detail only when APP_DEBUG) | Retry once, then report `request_id` to ops |

Legacy aliases (`/sales/*`, `/shipping/*`, `/cart/clear`, `/gift-cards/check`,
`/gift-cards/issue`, `GET /payments/khqr`, `/status`, `/docs`,
`/compliance/*`) now respond with `Deprecation: true` and `Sunset` headers
(sunset date 2027-12-31) — migrate clients to the canonical routes.
