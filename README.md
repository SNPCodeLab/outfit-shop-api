# DEVELOPER API

REST API specification for retail inventory management, point-of-sale transactional checkout, and financial reporting.

---

## 1. Core Architecture

- **POS Transactions**: High-concurrency checkout with pessimistic row locking and idempotency key protection.
- **Inventory Matrix**: 2D variant tracking (Size x Color) with 4-tier quantity lifecycle (On Hand, Reserved, Available, Incoming).
- **Payments**: Dynamic EMVCo KHQR and Bakong payment payloads.
- **Financial Valuation**: Purchased cost versus resale retail valuation and shift Z-Reports.
- **Access Control**: 4-tier RBAC (Guest, Cashier, Manager, Admin) via Bearer tokens.

---

## 2. API Conventions

- **Protocol**: JSON REST over HTTPS
- **Payload Format**: application/json
- **Idempotency**: X-Idempotency-Key header on mutations
- **Authentication**: Authorization: Bearer <token>

---

## 3. Response Envelope

### Success
```json
{
  "success": true,
  "status_code": 200,
  "request_id": "req_uuid",
  "data": {}
}
```

### Error
```json
{
  "success": false,
  "status_code": 422,
  "request_id": "req_uuid",
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Validation failed",
    "detail": {
      "fields": []
    }
  }
}
```

---

## 4. RBAC Matrix

| Role | Scope | Rate Limit |
| :--- | :--- | :--- |
| **Guest** | Public catalog, variants matrix, cart, wishlist | 30 req/min |
| **Cashier / Staff** | POS checkout, customer profiles, order history, receipts, KHQR | 100 req/min |
| **Manager** | Catalog mutations, stock adjustments, purchase orders, valuation | 200 req/min |
| **Admin** | Employee management, system pulse telemetry, security logs | 300 req/min |

---

## 5. Endpoints

### Catalog & Cart
- `GET /health` — Uptime and database connectivity
- `GET /products` — Product catalog with filtering and pagination
- `GET /products/{id}/matrix` — 2D variant stock matrix
- `GET /cart` — Active cart session
- `POST /cart` — Add item to cart

### POS & Operations
- `POST /auth/login` — Exchange credentials for Bearer token
- `POST /orders/checkout` — Atomic POS checkout with stock deduction
- `POST /payments/khqr` — Generate dynamic KHQR payment payload
- `GET /cloudinary/folders` — List all 24 root brand/catalog Cloudinary folders
- `GET /cloudinary/assets` — Query and stream 1,843 Cloudinary image assets with cursor pagination
- `GET /reports/inventory-valuation` — Cost versus retail inventory valuation
- `POST /stock-movements/adjust` — Stock adjustment with audit ledger
- `GET /admin/master-pulse` — APM telemetry and error tracking

---

## 6. Agent Skills & Automation Protocols

- **agent-ai-core-conventions**: Authoritative governance, coding standards, and double-checkpoint workflow.
- **api-endpoint-audit-protocol**: Automated endpoint validation and CRUD lifecycle tests across all RBAC tiers.
- **checkpoint-push-protocol**: Pre-push verification of schema integrity, authentication, and style compliance.
- **csms-backend-enterprise-delivery**: Enterprise API design, transactional locking, and frontend packaging.
- **neon-primary-connection**: Database connection configuration and pooling rules.
- **postman-collection-protocol**: Postman collection builder and synchronization rules.
- **salesbinder-pos-architecture**: 4-tier stock lifecycle and inventory asset valuation formulas.
- **ssmis-architecture-docs**: Academic and system architecture reporting standards.
- **ssmis-db-schema**: Authoritative database schema and relational constraints.
