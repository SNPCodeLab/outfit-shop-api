# KhmeRiel MIS & POS — SalesBinder Help Centre & Knowledge Base

> **How can we help?**  
> Find clear answers, step-by-step guides, and practical tips to help you get more from KhmeRiel Store Stock & Point-of-Sale Information System.

---

## 🌐 Live Access URLs

| Portal / Document | URL | Description |
| :--- | :--- | :--- |
| **Interactive Help Centre Web** | [`https://api.kesararamwithdigital.tech/guide`](https://api.kesararamwithdigital.tech/guide) | Full-featured Knowledge Base & API guide |
| **JSON Guide Data Endpoint** | [`https://api.kesararamwithdigital.tech/api/v1/guide`](https://api.kesararamwithdigital.tech/api/v1/guide) | Programmatic JSON directory |
| **Storefront Showcase App** | [`https://app.kesararamwithdigital.tech`](https://app.kesararamwithdigital.tech) | Customer luxury showcase & shopping |
| **Production API Gateway** | [`https://api.kesararamwithdigital.tech/api/v1`](https://api.kesararamwithdigital.tech/api/v1) | 129 RESTful endpoints |
| **Postman Collection** | [`postman/production.json`](file:///Users/Apple16/Desktop/SS_MIS/postman/production.json) | 1-Click test suite with environment |

---

## 1. Explore by Topic (Core Knowledge Categories)

### 1.1 Getting Started
* **Base URL**: `https://api.kesararamwithdigital.tech/api/v1`
* **Authentication**: Sanctum Bearer Token via `Authorization: Bearer <token>`.
* **Headers**: `Accept: application/json`, `Content-Type: application/json`.
* **Standard Response Envelope**:
```json
{
  "success": true,
  "data": { ... },
  "message": "Operation executed successfully",
  "status_code": 200
}
```

---

### 1.2 Accounts & Customer CRM
* **Customer Accounts**: Maintain VIP status, address, telephone, and purchase history.
* **Customer Search**: `GET /customers?search=012888999` (Instant phone lookup).
* **VIP Loyalty Balance**: `GET /customers/{id}/loyalty` (Tracks spend velocity, current points balance, and loyalty discount value).
* **Points Redemption**: `POST /customers/{id}/redeem-points` (Applies loyalty discount at checkout).

---

### 1.3 Inventory Management & Variations Matrix
* **4-Tier Quantity Lifecycle**:
  $$\text{Available (Net Sellable)} = \text{On Hand (Physical)} - \text{Reserved (Locked in Orders)}$$
  $$\text{Incoming} = \text{Units on Open Purchase Orders}$$
* **2D Matrix Grid**: `GET /products/{id}/matrix` (Returns size $\times$ color grid for rapid mass editing).
* **Barcode Scanning**: `GET /variants/barcode/{barcode}` (Instant UPC/EAN/SKU lookup).
* **Low Stock Alerts**: `GET /variants/low-stock` (Lists items at or below reorder threshold).
* **FIFO Expiry & Lot Batches**: `GET /inventory/expiring-soon` (Tracks lot codes and expiration dates).

---

### 1.4 Invoices & Estimates (SalesBinder Billing Engine)
* **Quotation Estimates**: `POST /estimates` (Creates a quote and calculates 10% VAT tax without deducting physical stock).
* **1-Click Convert to Invoice**: `POST /estimates/{id}/convert` (Locks inventory, deducts stock, creates stock movement ledger entries, and generates payment status).
* **A4 Printable Tax Invoice**: `GET /sales/{id}/invoice-pdf` (Renders high-resolution printable A4 document with bill-to customer details and VAT breakdown).
* **Thermal ESC/POS Receipts**: `GET /sales/{id}/receipt-thermal` (80mm standard format).

---

### 1.5 Purchasing & Suppliers
* **Master Suppliers**: `GET /suppliers`, `POST /suppliers`.
* **Purchase Orders**: `POST /purchases` (Line items, agreed cost prices, expected delivery dates).
* **Smart Replenishment**: `GET /inventory/restock-recommendations` (Velocity-based AI replenishment recommendations).
* **Auto-Generate Purchase Order**: `POST /purchases/auto-generate` (Creates vendor PO in 1 click).

---

### 1.6 Locations & Multi-Store Branches
* **Branch Locations**: `GET /branches` (Phnom Penh Flagship, Siem Reap Boutique, Central Warehouse).
* **Per-Branch Stock**: `GET /branches/{id}/stock` (Isolates inventory counts per store location).
* **Omnichannel Click-and-Collect**: `POST /shipping/create` (Dispatches home delivery or store pickup).

---

### 1.7 Kitting & Bundling
* **Bundle Catalog**: `GET /bundles` (Assembled multi-item combo packs, e.g. Silk Tie + Cufflinks).
* **Dynamic Assembly Availability**: Automatically calculates kit stock based on the minimum available individual components.

---

### 1.8 Reports & Financial Valuation
* **Asset Valuation**: `GET /inventory/statistics`
  * **Purchased Value (Cost Basis)**: $\sum (\text{Stock Quantity} \times \text{Cost Price})$
  * **Resale Value (Retail Asset)**: $\sum (\text{Stock Quantity} \times \text{Sale Price})$
  * **Gross Profit Margin %**: Real-time margin computation.
* **Role-Pulse Live Analytics**: `GET /dashboard/role-pulse` (Cashier scanning velocity and payment method distribution).

---

### 1.9 Security, RBAC & Audit Ledger
* **4-Tier RBAC Access**:
  * **Level 1 (Public / Guest)**: Catalog browsing and wishlist.
  * **Level 2 (Cashier / Staff)**: POS checkout, shifts, customers, and receipts.
  * **Level 3 (Manager)**: Inventory adjustments, purchases, pricing tiers, discounts, and reports.
  * **Level 4 (Admin)**: Employee HR, system settings, user provisioning, and full system override.
* **Immutable Audit Trail**: `GET /audit-logs` (Captures before/after JSON diffs, employee IDs, and IP addresses).

---

## 2. Popular Topics Index

| Topic | Direct Endpoint / Resource |
| :--- | :--- |
| **Quantity Overview** | `GET /variants` (Returns `on_hand`, `reserved`, `available`, `incoming`) |
| **Estimates / Quotes** | `POST /estimates` |
| **Convert Estimate** | `POST /estimates/{id}/convert` |
| **Printable A4 Invoice** | `GET /sales/{id}/invoice-pdf` |
| **Bakong KHQR** | `GET /sales/{id}/khqr` |
| **Thermal Receipt** | `GET /sales/{id}/receipt-thermal` |
| **Financial Valuation** | `GET /inventory/statistics` |
| **Restock Forecast** | `GET /inventory/restock-recommendations` |
| **Auto Purchase Order** | `POST /purchases/auto-generate` |
| **Cash Drawer Shifts** | `GET /shifts/current`, `POST /shifts/open`, `POST /shifts/close` |
| **2D Variant Matrix** | `GET /products/{id}/matrix` |
| **Audit Logs** | `GET /audit-logs` |

---

## 3. Support & Contact

* **Email Support**: `support@kesararamwithdigital.tech`
* **API Documentation**: [`https://api.kesararamwithdigital.tech/guide`](https://api.kesararamwithdigital.tech/guide)
* **Production Host**: `api.kesararamwithdigital.tech`
