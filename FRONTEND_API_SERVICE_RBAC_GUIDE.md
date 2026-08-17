# KhmeRiel MIS & POS — Frontend API Service & RBAC Contract

> **Base Gateway URL**: `https://api.kesararamwithdigital.tech/api/v1` (Production) / `http://127.0.0.1:8000/api/v1` (Local Dev)  
> **Auth Header**: `Authorization: Bearer <sanctum_token>`  
> **Default Tax Policy**: `10.00% Tax-Exclusive (VAT)`

---

## 1. Role-Based Access Control (RBAC) Permission Matrix

| Role | Hierarchy Level | Primary Client Portal Interface | Allowed CRUD Operations |
| :--- | :---: | :--- | :--- |
| **🌐 PUBLIC / GUEST** | Level 0 | Luxury Storefront Showcase (Catalog & Swatches) | **R** on public products, categories, brands, marketing banners |
| **📦 STAFF** | Level 1 | Floor & Warehouse Stock Lookup (Mobile / Tablet) | **R** on inventory, products, variants, customer lookup, stock pulse |
| **💳 CASHIER** | Level 2 | Touch & Barcode POS Cash Register | **CR** on POS checkout, customer creation, receipt printing, shifts |
| **👔 MANAGER** | Level 3 | Store Controller (Catalog, Purchasing, Reports) | **CRUD** on catalog, suppliers, purchases, stock adjustments, void sales |
| **👑 ADMIN** | Level 4 | Master Command & Security Executive Console | **CRUD** on all entities + staff timesheets, RBAC users, tax & broadcast |

---

## 2. Comprehensive Endpoint Directory & Permission Guards

### 2.1 Authentication & System

| Method | Endpoint Path | Role Guard | Permission / Action | Purpose |
| :--- | :--- | :---: | :--- | :--- |
| `POST` | `/auth/login` | **PUBLIC** | Rate-limited (10/min) | Staff & Admin login (returns Sanctum Bearer token + role) |
| `GET` | `/health` | **PUBLIC** | `None` | System health check, database ping & Cloudinary CDN status |
| `GET` | `/status` | **PUBLIC** | `None` | App metadata, official brand logo & version details |
| `GET` | `/settings/audio-cues`| **PUBLIC** | `None` | High-definition POS audio feedback sound effects (.wav) |
| `GET` | `/alerts/active` | **AUTHENTICATED** | `alerts.view` | Live broadcast alerts & emergency reminders banner |

---

### 2.2 Catalog & Luxury Fashion Media

| Method | Endpoint Path | Role Guard | Permission | Frontend Client Action |
| :--- | :--- | :---: | :--- | :--- |
| `GET` | `/categories` | **PUBLIC** | `categories.view` | Fetch all 9 apparel & beverage categories |
| `GET` | `/brands` | **PUBLIC** | `brands.view` | Fetch all 5 verified brands (KhmeRiel, Ralph Lauren, etc.) |
| `GET` | `/clothing-sizes` | **PUBLIC** | `clothing-sizes.view` | Fetch 5 core luxury sizes (`S`, `M`, `L`, `XL`, `OS`) |
| `GET` | `/colors` | **PUBLIC** | `colors.view` | Fetch 3 luxury colors (`Black`, `White`, `Gold`) |
| `GET` | `/products` | **PUBLIC** | `products.view` | List active products with Cloudinary CDN photos & prices |
| `GET` | `/products/{id}` | **PUBLIC** | `products.view` | Single product detail with size/color variant matrix |
| `GET` | `/products/{id}/matrix` | **PUBLIC** | `products.view` | SalesBinder-style 2D Size $\times$ Color stock grid |
| `GET` | `/variants/barcode/{barcode}` | **AUTHENTICATED** | `variants.view` | Continuous high-speed barcode scanner lookup |
| `POST` | `/products` | **MANAGER / ADMIN** | `products.create` | Add new product to catalog |
| `PUT` | `/products/{id}` | **MANAGER / ADMIN** | `products.update` | Edit product attributes, description or brand |
| `DELETE`| `/products/{id}` | **MANAGER / ADMIN** | `products.delete` | Archive / soft-delete product |
| `POST` | `/uploads/image` | **MANAGER / ADMIN** | `uploads.create` | Upload photoshoot media directly to Cloudinary CDN |

---

### 2.3 POS Checkout, Payments & Cash Register

| Method | Endpoint Path | Role Guard | Permission | Frontend Client Action |
| :--- | :--- | :---: | :--- | :--- |
| `POST` | `/sales/checkout` | **CASHIER+** | `sales.checkout` | Process POS sale, auto-calculate 10% VAT & deduct stock |
| `GET` | `/sales` | **CASHIER+** | `sales.view` | View today's register sales history |
| `GET` | `/sales/{id}` | **CASHIER+** | `sales.view` | Fetch invoice breakdown with tax, discounts & payments |
| `GET` | `/sales/{id}/receipt-thermal` | **CASHIER+** | `sales.view` | Render 80mm ESC/POS thermal receipt format |
| `GET` | `/sales/{id}/khqr` | **CASHIER+** | `sales.view` | Generate dynamic ABA PayWay / Bakong KHQR code |
| `POST` | `/sales/{id}/void` | **MANAGER / ADMIN**| `sales.void` | Void mistaken sale & instantly restore inventory |
| `GET` | `/shifts/current` | **CASHIER+** | `shifts.view` | Check if cash drawer shift is currently open |
| `POST` | `/shifts/open` | **CASHIER+** | `shifts.open` | Open register shift with starting cash float (USD/KHR) |
| `POST` | `/shifts/drop-cash` | **CASHIER+** | `shifts.drop` | Mid-shift cash drop / safe transfer |
| `POST` | `/shifts/close` | **CASHIER+** | `shifts.close` | Close shift, count drawer cash & print Z-Report |

---

### 2.4 Inventory, Stock Movement & Restock Forecasting

| Method | Endpoint Path | Role Guard | Permission | Frontend Client Action |
| :--- | :--- | :---: | :--- | :--- |
| `GET` | `/stock-movements` | **STAFF+** | `stock-movements.view` | View real-time inventory ledger and movement log |
| `POST` | `/stock-movements/adjust` | **MANAGER / ADMIN**| `stock-movements.adjust` | Manual stock count adjustment (+/- units) |
| `GET` | `/inventory/restock-recommendations` | **MANAGER / ADMIN**| `purchases.view` | Restock forecast algorithm based on run-rate |
| `POST` | `/purchases/auto-generate` | **MANAGER / ADMIN**| `purchases.create` | Auto-generate purchase orders to suppliers |
| `GET` | `/suppliers` | **MANAGER / ADMIN**| `suppliers.view` | Directory of master suppliers & contacts |
| `POST` | `/suppliers` | **MANAGER / ADMIN**| `suppliers.create` | Register new textile or beverage supplier |

---

### 2.5 Live Role-Pulse & Master Tracking

| Method | Endpoint Path | Role Guard | Features Provided |
| :--- | :--- | :---: | :--- |
| `GET` | `/dashboard/role-pulse` | **AUTHENTICATED** | Returns **Pie Chart** and **Agile Trend Graph** auto-tailored to the user's role (Cashier, Staff, Manager, Admin). |
| `GET` | `/admin/master-pulse` | **ADMIN ONLY** | • Staff Working Hours & Timesheet tracking<br>• Financial Waterfall Diagram (GMV to Net Profit)<br>• Agile Sprint Revenue Velocity<br>• Size $\times$ Color Matrix Coverage Tracker |
| `POST` | `/admin/broadcast-alert` | **ADMIN ONLY** | Broadcast flash sales, emergency notices, or reminders to all logged-in users. |

---

### 2.6 User & Employee Management

| Method | Endpoint Path | Role Guard | Permission | Frontend Client Action |
| :--- | :--- | :---: | :--- | :--- |
| `GET` | `/employees` | **ADMIN ONLY** | `employees.view` | List all staff members, positions & contacts |
| `POST` | `/employees` | **ADMIN ONLY** | `employees.create` | Create new staff profile |
| `PUT` | `/employees/{id}` | **ADMIN ONLY** | `employees.update` | Update staff details, phone or store assignment |
| `DELETE`| `/employees/{id}` | **ADMIN ONLY** | `employees.delete` | Deactivate staff employee profile |
| `POST` | `/auth/register` | **ADMIN ONLY** | `users.create` | Create new portal user login & assign RBAC Role |
| `GET` | `/audit-logs` | **ADMIN ONLY** | `audit-logs.view` | Immutable system audit log trail |

---

## 3. Sample JSON Payloads for Frontend Implementation

### 3.1 POS Checkout Request (`POST /api/v1/sales/checkout`)
```json
{
  "customer_id": 1,
  "payment_method": "ABA",
  "payment_amount": 74.25,
  "overall_discount": 0.00,
  "tax_rate": 10.00,
  "items": [
    {
      "variant_id": 1,
      "quantity": 1,
      "discount": 0.00
    },
    {
      "variant_id": 7,
      "quantity": 1,
      "discount": 0.00
    }
  ]
}
```

### 3.2 POS Checkout Response (10% Tax-Exclusive Breakdown)
```json
{
  "success": true,
  "message": "POS Checkout completed successfully",
  "data": {
    "sale_id": 101,
    "sale_date": "2026-08-17T07:25:00.000000Z",
    "total_amount": 67.50,
    "discount": 0.00,
    "tax_rate": 10.00,
    "tax_amount": 6.75,
    "grand_total": 74.25,
    "status": "COMPLETED"
  }
}
```

### 3.3 Admin Broadcast Alert Request (`POST /api/v1/admin/broadcast-alert`)
```json
{
  "title": "Evening Shift Restock Notice",
  "message": "All staff please verify Silk Shirt stock in Rack A-04 before closing shift.",
  "priority": "HIGH",
  "target_role": "ALL"
}
```
