# 📜 OutfitShop-Backend-API Changelog

All notable changes to the **OutfitShop-Backend-API** are documented here using Semantic Versioning.

---

## [v1.5.0] — 2026-08-18 (OutfitShop-Backend-API Rebranding & Omnichannel Expansion)
### Added
- **OutfitShop Branding & CDN Assets**: Injected official OutfitShop logo, vector icon, animated cycle, and video assets across all API response envelopes (`meta.brand`), email templates, and error pages.
- **Shopping Cart Module**: Full CRUD for customer and guest session carts (`/api/v1/cart`, `/api/v1/cart/items`, `/api/v1/cart/clear`) with automatic 10% VAT calculation.
- **Enhanced Wishlist**: Dedicated endpoints for saving, toggling, and managing customer favorites.
- **Orders Endpoints Migration**: Migrated sales endpoints to `/api/v1/orders/*` with full backward-compatible aliases for `/api/v1/sales/*`.
- **System Identification**: Updated all response envelopes and status endpoints to `"OutfitShop-Backend-API"`.

## [v1.4.0] — 2026-08-17 (Enterprise Release)
### Added
- **AI Intelligence Suite**: 30-day predictive sales forecasting, fraud anomaly detection, smart restock velocity, RFM customer segmentation, dynamic markdown pricing.
- **Offline POS Sync**: 72-hour offline authentication sync tokens and batch transaction push with conflict resolution.
- **Multi-Currency**: USD primary & KHR secondary with National Bank of Cambodia benchmark rate (4,100 KHR) and physical cash drawer rounding.
- **Multi-Language Localization**: Full response translation into English (`en`), Khmer (`km`), and Chinese (`zh`) via `Accept-Language` headers.
- **Multi-Store Stock Transfers**: 5-stage workflow (Request $\rightarrow$ Approve $\rightarrow$ Pick $\rightarrow$ Ship $\rightarrow$ Receive) with atomic stock reservation.
- **Advanced MIS Reports**: 7 financial and operational analytics domains.
- **Privacy & Compliance**: GDPR data export (Right to Portability), Right to Erasure (anonymization with 7-year audit retention), PCI-DSS non-storage compliance.
- **APM Performance & API Analytics**: Real-time latency, error rate, cache hit ratio, and traffic analytics.

---

## [v1.3.0] — 2026-08-17
### Added
- **Dynamic 4-Tier Rate Limiting**: Role-based throttling (`ADMIN: 300`, `MANAGER: 200`, `CASHIER: 100`, `STAFF: 50`, `PUBLIC: 30`).
- **Token Rotation & Security**: Single-use token rotation (`/auth/refresh`) and global kill-switch (`/auth/revoke-all`).
- **Automated Database Backups**: Nightly compressed PostgreSQL dump with automated S3 sync and 30-day retention pruning.
- **Redis Caching Layer**: High-speed caching for catalog, categories, and 2D variant matrices.
- **Asynchronous Queue Jobs**: Background jobs for reports, media, stock opname, and customer notifications.
- **HMAC-SHA256 Webhooks**: Event subscription engine for stock alerts, POs, and shift discrepancies.
- **Real-Time WebSocket Broadcasting**: Channel broadcasts for inventory changes, sales, and shift updates.
- **5 Dedicated Logging Channels**: `pos.log`, `inventory.log`, `purchasing.log`, `security.log`, `admin.log`.

---

## [v1.2.0] — 2026-08-17
### Added
- Distributed tracing UUID v4 `request_id` across all responses.
- Concurrency-proof POS checkout with pessimistic row-locking (`lockForUpdate()`) and idempotency keys.
- Pre- and post-stock balances (`stock_before` and `stock_after`) on all ledger movements.
