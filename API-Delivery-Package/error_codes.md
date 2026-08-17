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
