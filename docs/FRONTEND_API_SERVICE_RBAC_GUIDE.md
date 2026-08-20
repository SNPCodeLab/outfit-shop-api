<div align="center">
  <img src="https://res.cloudinary.com/od8t271n/image/upload/v1787064621/bleu-SNPCodeLab.png" alt="OutfitShop Primary Logo" width="260" />
  <h1>OutfitShop API — Frontend Integration Guide & RBAC Contract</h1>
  <p>Official specifications for connecting web storefronts, mobile apps, and POS terminals to <strong>OutfitShop Ecommerce Clothing API</strong>.</p>
</div>

---

## 🎨 Official Brand Assets

| Asset Type | Resource URL |
| :--- | :--- |
| **Primary Logo** | `https://res.cloudinary.com/od8t271n/image/upload/v1787064621/bleu-SNPCodeLab.png` |
| **Animated Cycle** | `https://res.cloudinary.com/od8t271n/image/upload/v1787062663/default-cycle-SNPCodeLab.gif` |
| **Secondary Logo** | `https://res.cloudinary.com/od8t271n/image/upload/v1787062664/bleu-SNPCodeLab.gif` |
| **Vector Icon** | `https://res.cloudinary.com/od8t271n/image/upload/v1787062662/anime-SNPCodeLab.svg` |
| **Brand Video** | `https://res.cloudinary.com/od8t271n/video/upload/v1787062665/default-cycle-SNPCodeLab.mp4` |

> **Base Gateway URL**: `https://api.kesararamwithdigital.tech/api/v1` (Production) / `http://127.0.0.1:8000/api/v1` (Local Dev)  
> **Auth Header**: `Authorization: Bearer <sanctum_token>`  
> **Default Tax Policy**: `10.00% Tax-Exclusive (VAT)`

---

## 1. Role-Based Access Control (RBAC) Permission Matrix

| Role | Hierarchy Level | Primary Client Portal Interface | Allowed CRUD Operations |
| :--- | :---: | :--- | :--- |
| **PUBLIC / GUEST** | Level 0 | Storefront Showcase & Checkout | **R** on public products, categories, brands, cart, wishlist |
| **STAFF** | Level 1 | Floor & Warehouse Stock Lookup (Mobile / Tablet) | **R** on inventory, products, variants, customer lookup, stock pulse |
| **CASHIER** | Level 2 | Touch & Barcode POS Cash Register | **CR** on POS/Order checkout, customer creation, receipt printing, shifts |
| **MANAGER** | Level 3 | Store Controller (Catalog, Purchasing, Reports) | **CRUD** on catalog, suppliers, purchases, stock adjustments, void orders |
| **ADMIN** | Level 4 | Master Command & Security Executive Console | **CRUD** on all entities + staff timesheets, RBAC users, tax & broadcast |

---

## 2. Comprehensive Endpoint Directory & Permission Guards

### 2.1 Authentication & System

| Method | Endpoint Path | Role Guard | Permission / Action | Purpose |
| :--- | :--- | :---: | :--- | :--- |
| `POST` | `/auth/login` | **PUBLIC** | Rate-limited (10/min) | Staff, Customer & Admin login (returns Sanctum Bearer token + role) |
| `GET` | `/health` | **PUBLIC** | `None` | System health check, database ping & Cloudinary CDN status |
| `GET` | `/status` | **PUBLIC** | `None` | App metadata, official OutfitShop brand logo & version details |
| `GET` | `/settings/audio-cues`| **PUBLIC** | `None` | High-definition POS audio feedback sound effects (.wav) |
| `GET` | `/alerts/active` | **AUTHENTICATED** | `alerts.view` | Live broadcast alerts & emergency reminders banner |

---

### 2.2 Catalog, Shopping Cart & Wishlist

| Method | Endpoint Path | Role Guard | Permission | Frontend Client Action |
| :--- | :--- | :---: | :--- | :--- |
| `GET` | `/categories` | **PUBLIC** | `categories.view` | Fetch all apparel & accessory categories |
| `GET` | `/brands` | **PUBLIC** | `brands.view` | Fetch verified fashion brands |
| `GET` | `/clothing-sizes` | **PUBLIC** | `clothing-sizes.view` | Fetch size codes (`S`, `M`, `L`, `XL`, `XXL`, `OS`) |
| `GET` | `/colors` | **PUBLIC** | `colors.view` | Fetch color swatches with CSS hex codes |
| `GET` | `/products` | **PUBLIC** | `products.view` | List active products with Cloudinary CDN photos & prices |
| `GET` | `/products/{id}` | **PUBLIC** | `products.view` | Single product detail with size/color variant matrix |
| `GET` | `/products/{id}/matrix` | **PUBLIC** | `products.view` | 2D Size $\times$ Color stock grid |
| `GET` | `/cart` | **PUBLIC** | `cart.view` | Retrieve active shopping cart with subtotal & 10% VAT |
| `POST` | `/cart/items` | **PUBLIC** | `cart.edit` | Add product variant to shopping cart |
| `PUT` | `/cart/items/{id}` | **PUBLIC** | `cart.edit` | Update cart item quantity |
| `DELETE`| `/cart/items/{id}` | **PUBLIC** | `cart.edit` | Remove line item from cart |
| `DELETE`| `/cart/clear` | **PUBLIC** | `cart.edit` | Empty entire cart |
| `GET` | `/wishlist` | **PUBLIC** | `wishlist.view` | Fetch saved wishlist items for customer |
| `POST` | `/wishlist/toggle` | **PUBLIC** | `wishlist.edit` | Toggle product in/out of customer wishlist |
| `GET` | `/variants/barcode/{barcode}` | **PUBLIC / AUTH** | `variants.view` | Continuous high-speed barcode scanner lookup |

---

### 2.3 Orders, POS Checkout & Cash Register

| Method | Endpoint Path | Role Guard | Permission | Frontend Client Action |
| :--- | :--- | :---: | :--- | :--- |
| `POST` | `/orders/checkout` | **CASHIER+ / AUTH** | `orders.checkout` | Process order, auto-calculate 10% VAT & deduct stock (alias: `/sales/checkout`) |
| `GET` | `/orders` | **CASHIER+ / AUTH** | `orders.view` | View order history with filters & pagination (alias: `/sales`) |
| `GET` | `/orders/{id}` | **CASHIER+ / AUTH** | `orders.view` | Fetch invoice breakdown with tax, discounts & payments (alias: `/sales/{id}`) |
| `GET` | `/orders/{id}/receipt-thermal` | **PUBLIC / AUTH** | `orders.view` | Render 80mm ESC/POS thermal receipt format |
| `GET` | `/orders/{id}/khqr` | **PUBLIC / AUTH** | `orders.view` | Generate dynamic ABA PayWay / Bakong KHQR code |
| `POST` | `/orders/{id}/void` | **MANAGER / ADMIN**| `orders.void` | Void order & instantly restore inventory (alias: `/sales/{id}/void`) |
| `GET` | `/shifts/current` | **CASHIER+** | `shifts.view` | Check if cash drawer shift is currently open |
| `POST` | `/shifts/open` | **CASHIER+** | `shifts.open` | Open register shift with starting cash float (USD/KHR) |
| `POST` | `/shifts/drop-cash` | **CASHIER+** | `shifts.drop` | Mid-shift cash drop / safe transfer |
| `POST` | `/shifts/close` | **CASHIER+** | `shifts.close` | Close shift, count drawer cash & print Z-Report |

---

## 3. Sample Order Checkout Request Payload (`POST /api/v1/orders/checkout`)

```json
{
  "customer_id": 1,
  "items": [
    {
      "variant_id": 12,
      "quantity": 2,
      "discount": 0.00
    },
    {
      "variant_id": 15,
      "quantity": 1,
      "discount": 5.00
    }
  ],
  "payment_method": "QR",
  "payment_amount": 150.00,
  "overall_discount": 0.00,
  "tax_rate": 10.00,
  "idempotency_key": "ORD-UUID-20260818-001"
}
```
