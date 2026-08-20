# OutfitShop Frontend UX/UI Master Prompt Skill

## Purpose

This skill governs how the AI agent designs, scaffolds, and implements the
OutfitShop MIS & POS frontend. Every decision — layout, component, routing,
state, API call — must be derived from this document. Do not deviate from these
conventions without explicit user instruction.

---

## 1. Tech Stack (Non-Negotiable)

| Layer | Choice |
|---|---|
| Framework | Next.js 14+ (App Router) |
| Language | TypeScript (strict mode) |
| Styling | Tailwind CSS + shadcn/ui |
| State / Server | TanStack Query v5 (react-query) |
| Forms | React Hook Form + Zod |
| HTTP client | Axios with an interceptor that injects `Bearer` token |
| Auth | JWT stored in `httpOnly` cookie via a thin `/api/auth/*` Next.js route handler (never localStorage) |
| Charts | Recharts |
| Icons | Lucide React |
| Table | TanStack Table v8 |
| Barcode scan | `@zxing/browser` (camera) + `react-barcode` (display) |
| Receipt print | `react-to-print` |
| Drag & drop | `@dnd-kit/core` (product image sort) |
| Notifications | `sonner` (toast) |

---

## 2. Design Inspiration & Visual Language

Inspired by **Farfetch**, **SSENSE**, and **ASOS** storefront aesthetics combined
with a dark, dense MIS dashboard similar to **Vercel Dashboard** and **Linear**.

### Color Tokens

```css
/* globals.css */
:root {
  --color-bg:          #0A0A0A;   /* near-black canvas */
  --color-surface:     #111111;   /* card / panel background */
  --color-border:      #1E1E1E;   /* subtle dividers */
  --color-accent:      #C8A96E;   /* gold — brand accent (clothes luxury) */
  --color-accent-soft: #F5ECD7;   /* light gold tint for hover states */
  --color-text-hi:     #F2F2F2;   /* primary readable text */
  --color-text-lo:     #717171;   /* muted / label text */
  --color-success:     #22C55E;
  --color-warning:     #F59E0B;
  --color-danger:      #EF4444;
  --color-info:        #3B82F6;
}
```

### Typography

- Headings: `Inter` (700 / 600)
- Body: `Inter` (400 / 500)
- Monospace (prices, SKUs): `JetBrains Mono`
- Minimum body size: 14px; line height 1.6

### Motion

Use Framer Motion for:
- Page transitions (fade + slight y-slide, 180ms)
- Modal open/close (scale 0.95 -> 1, 150ms)
- Card hover lift (`hover:translate-y-[-2px]`, `transition-transform duration-200`)

---

## 3. RBAC Role Hierarchy

```
PUBLIC < STAFF < CASHIER < MANAGER < ADMIN
```

After login the API returns `data.user.role`. Store this in the auth context.
Route guards and navigation items render conditionally based on role.

| Role | Entry Point | Primary UX Metaphor |
|---|---|---|
| STAFF | `/staff` | Product browsing, cart, wishlist, payment lookup |
| CASHIER | `/pos` | Touch-friendly POS terminal — full screen checkout |
| MANAGER | `/manager` | Data-heavy MIS — analytics, catalog CRUD, inventory |
| ADMIN | `/admin` | System control panel — employees, monitoring, logs |

---

## 4. Project File Structure

```
src/
  app/
    (auth)/
      login/
        page.tsx            # Shared login page — role-aware redirect
    (staff)/
      layout.tsx            # Staff shell (sidebar + header)
      page.tsx              # Staff home: product browsing
      products/
        page.tsx
        [id]/page.tsx
      cart/page.tsx
      wishlist/page.tsx
      payments/page.tsx
    (pos)/
      layout.tsx            # Cashier shell (full-screen, minimal chrome)
      page.tsx              # POS main terminal
      shifts/page.tsx
      orders/page.tsx
      customers/page.tsx
      invoices/page.tsx
      gift-cards/page.tsx
    (manager)/
      layout.tsx            # Manager shell
      page.tsx              # Manager dashboard (analytics overview)
      analytics/page.tsx
      catalog/
        products/page.tsx
        categories/page.tsx
        brands/page.tsx
        sizes-colors/page.tsx
        bundles/page.tsx
        promotions/page.tsx
        variants/[productId]/page.tsx
      inventory/
        page.tsx
        movements/page.tsx
        transfers/page.tsx
        batches/page.tsx
        purchases/page.tsx
        suppliers/page.tsx
      reports/
        page.tsx
        mis/page.tsx
        exports/page.tsx
      branches/page.tsx
      banners/page.tsx
      audit-logs/page.tsx
      ai-intelligence/page.tsx
      webhooks/page.tsx
      gdpr/page.tsx
    (admin)/
      layout.tsx
      page.tsx              # Admin overview
      employees/page.tsx
      monitoring/page.tsx
      users/page.tsx
  components/
    ui/                     # shadcn primitives (Button, Input, Badge, etc.)
    layout/
      Sidebar.tsx
      Header.tsx
      RoleShell.tsx         # wraps layout per role
    pos/
      PosKeypad.tsx
      ProductSearchBar.tsx
      CartPanel.tsx
      PaymentModal.tsx
      ReceiptPrinter.tsx
      BarcodeScanner.tsx
      ShiftSummary.tsx
    catalog/
      ProductCard.tsx       # Farfetch-style clothing card
      ProductGrid.tsx
      ProductTable.tsx
      VariantMatrix.tsx     # size x color grid
      ImageUploader.tsx
    dashboard/
      MetricCard.tsx
      SalesChart.tsx
      TopProductsTable.tsx
      AlertBanner.tsx
      ForecastWidget.tsx
    forms/
      ProductForm.tsx
      VariantForm.tsx
      CustomerForm.tsx
      EmployeeForm.tsx
      SupplierForm.tsx
      PurchaseForm.tsx
      TransferForm.tsx
      PromotionForm.tsx
    shared/
      DataTable.tsx         # TanStack Table wrapper
      ConfirmModal.tsx
      EmptyState.tsx
      LoadingSpinner.tsx
      StatusBadge.tsx
      RoleBadge.tsx
      CurrencyDisplay.tsx
      Pagination.tsx
      ExportButton.tsx
      SearchInput.tsx
  hooks/
    useAuth.ts
    useRole.ts             # returns { role, can }
    useProducts.ts
    useCart.ts
    useOrders.ts
    usePosShift.ts
    useDashboard.ts
    useAnalytics.ts
  lib/
    api.ts                 # Axios instance + interceptors
    auth.ts                # token helpers (server-side cookie set/get)
    rbac.ts                # permission map
    utils.ts
    validators/            # Zod schemas mirroring API request bodies
  providers/
    AuthProvider.tsx
    QueryProvider.tsx
    ThemeProvider.tsx
  types/
    api.ts                 # Response envelope types
    models.ts              # Product, Variant, Order, etc.
    rbac.ts
middleware.ts              # Next.js middleware — enforce auth + role redirect
```

---

## 5. Authentication Flow

### Login Page (`/login`)

Design: Full-page centered card on dark background. Left panel has a large
editorial clothing image (like SSENSE homepage). Right panel has the form.

```
Fields:  username OR email  +  password
Button:  "Sign In" (full width, gold accent)
Error:   inline below field, red text
Loading: spinner inside button
```

On success the API returns:
```json
{
  "success": true,
  "data": {
    "access_token": "...",
    "user": { "id": 1, "username": "admin", "role": "admin", "email": "..." }
  }
}
```

Save `access_token` via `/api/auth/set-cookie` route handler (httpOnly cookie).
Save `user` object in React context.

Role-based redirect after login:

```ts
const redirectMap = {
  admin:    '/admin',
  manager:  '/manager',
  cashier:  '/pos',
  staff:    '/staff',
};
```

---

## 6. API Client Setup

```ts
// lib/api.ts
import axios from 'axios';

const api = axios.create({
  baseURL: process.env.NEXT_PUBLIC_API_URL, // https://api.kesararamwithdigital.tech/api/v1
  timeout: 15_000,
  headers: { 'Content-Type': 'application/json' },
});

api.interceptors.request.use((config) => {
  // Token injected server-side via cookie on route handlers,
  // or from memory on client
  const token = getTokenFromMemory();
  if (token) config.headers.Authorization = `Bearer ${token}`;
  return config;
});

api.interceptors.response.use(
  (res) => res,
  (err) => {
    if (err.response?.status === 401) {
      window.location.href = '/login';
    }
    return Promise.reject(err);
  }
);

export default api;
```

---

## 7. Shared Base Component Rules

Every component must:
1. Be typed with TypeScript interfaces (no `any`).
2. Accept a `className` prop for Tailwind override.
3. Handle loading, error, and empty states explicitly.
4. Use `sonner` toast for mutation success/error feedback.
5. Be accessible: proper `aria-label`, keyboard navigation, focus rings.

### ProductCard (Clothing Style — Farfetch-Inspired)

```
+---------------------------+
|  [Product Image]          |
|  hover: zoom 1.04         |
|  top-left: NEW / SALE badge|
+---------------------------+
|  Brand Name (muted, small)|
|  Product Name (bold)      |
|  Price  /  Was-Price      |
|  [S] [M] [L] [XL] sizes   |
|  [Add to Cart]  [Wishlist]|
+---------------------------+
```

- Image uses Next.js `<Image>` with `object-cover`.
- Sizes shown as small pill badges, disabled if out of stock.
- Color swatches shown as 16px circles below sizes.
- On hover: reveal quick-add overlay.

---

## 8. Role-Specific Layouts & Screens

---

### 8.1 STAFF Layout

Sidebar (left, 240px):
- Logo top
- Nav items: Browse Products, Cart, Wishlist, Payments, Receipts
- Bottom: user avatar + name + role badge + logout

Header:
- Search bar (fuzzy product search — `GET /products?search=`)
- Cart icon with item count badge
- Currency display

**Staff Home — Product Browse:**
- Grid of ProductCards (3 col desktop, 2 tablet, 1 mobile)
- Left filter panel: Category, Brand, Size, Color, Price range
- Top bar: sort dropdown, view toggle (grid / table)
- Pagination at bottom

**Staff Cart:**
- Line item list with quantity stepper
- Totals panel: subtotal, discount, total
- "Proceed to Payment" button

**Staff Payments:**
- Payment status lookup by order/receipt number
- Initiate payment: `POST /payments/initiate`
- Supported methods: cash, card, QR, gift card

---

### 8.2 CASHIER / POS Layout

Full-screen, no sidebar. Top navigation strip only.

```
+--------------------------------------------------+
| [Logo]  [Shift: Open/Close]  [Cashier: Jane]     |
+---------------------+----------------------------+
|  PRODUCT SEARCH     |   CART / ORDER PANEL       |
|  [Barcode Input]    |   ------------------       |
|  Product Grid       |   Item 1  x2   $40.00      |
|  (touch-friendly    |   Item 2  x1   $25.00      |
|   large cards)      |   ------------------       |
|                     |   Subtotal:    $65.00       |
|                     |   Discount:    -$5.00       |
|                     |   TOTAL:       $60.00       |
+---------------------+                            |
                       |   [CASH] [CARD] [QR]      |
                       |   [Gift Card] [Split]      |
                       |   [CHECKOUT]              |
                       +----------------------------+
```

**POS Screen rules:**
- Minimum tap target: 48px
- Font size minimum 16px for item names
- Barcode scanner input auto-focuses on scan
- Cart updates optimistically
- Payment modal slides up from bottom (mobile-style)
- Receipt prints via browser print dialog or thermal printer URL

**POS Shift:**
- Open shift: enter opening float (cash count)
- Close shift: shows summary (sales count, total cash, card, etc.)
- `POST /shifts/open` / `POST /shifts/close/:id`

**Customers Panel (POS):**
- Quick search customer by phone/name
- Create new customer inline: name, phone, email
- Attach customer to current order

**Orders / Invoices:**
- List today's orders (table)
- Void order button (if cashier has permission — `DELETE /orders/:id/void`)
- Print invoice: `GET /invoices/:id`
- Estimate builder: `POST /estimates`

---

### 8.3 MANAGER Layout

Sidebar (256px, collapsible):

```
Dashboard        (overview metrics)
Analytics        (forecasting, trends)
Catalog
  Products
  Categories
  Brands
  Sizes & Colors
  Bundles
  Promotions
  Variants
Inventory
  Stock Movements
  Transfers
  Batches (FIFO)
  Purchases
  Suppliers
Reports
  MIS Reports
  File Exports
Branches
Banners
Audit Logs
AI Intelligence
Webhooks
GDPR
```

**Manager Dashboard:**

```
+--[ Revenue Today ]--+--[ Orders Today ]--+--[ Low Stock ]--+
|   $12,450           |   84 orders        |   7 items       |
+---------------------+--------------------+-----------------+
|  SALES CHART (7-day bar chart — Recharts)                  |
+------------------------------------------------------------+
|  TOP PRODUCTS TABLE        |  RECENT ORDERS TABLE          |
+----------------------------+-------------------------------+
|  AI FORECAST WIDGET        |  ALERTS (low stock, returns)  |
+----------------------------+-------------------------------+
```

MetricCard component:
```
[Icon]  Label
        Value (large, bold, accent color)
        % change vs yesterday (green/red arrow)
```

**Analytics Page:**
- Date range picker
- Revenue by category (pie chart)
- Sales over time (line chart)
- Top customers by spend (table)
- `GET /analytics/sales`, `GET /analytics/forecasting`

**Products CRUD:**
- Table view with columns: Image, Name, Brand, Category, Price, Stock, Status, Actions
- Row actions: Edit, Manage Variants, View Images, Delete
- "New Product" drawer slides from right (ProductForm)
- ProductForm fields: name, description, brand, category, base_price, is_active
- After create: auto-navigate to variant matrix

**Variant Matrix:**
```
         S    M    L    XL
White   [edit][edit][edit][edit]
Black   [edit][edit][edit][edit]
Red     [edit][edit][edit][edit]
```
Each cell: SKU, price, stock qty — click to edit inline or open drawer.

**Inventory — Stock Transfers (5-stage):**
Status flow: `draft -> pending -> in_transit -> received -> completed`
UI shows a horizontal stepper with current stage highlighted.

**MIS Reports:**
- `GET /reports/sales-summary`
- `GET /reports/inventory-valuation`
- `GET /reports/product-performance`
- `GET /reports/category-revenue`
- `GET /reports/staff-performance`
- `GET /reports/customer-analytics`
- `GET /reports/purchase-analysis`
- `GET /reports/financial-summary`
- `GET /reports/daily-summary`
Each report: date filter + export button (CSV/PDF)

**AI Intelligence:**
- Demand forecast
- Reorder suggestions
- Bundle performance
- Customer segmentation
`GET /ai/demand-forecast`, `GET /ai/recommendations`

---

### 8.4 ADMIN Layout

Extends Manager layout, plus:

Sidebar additions:
```
Employees       (CRUD)
User Accounts
System Monitor
```

**Admin Dashboard:**
Inherits Manager dashboard + adds system health widgets:
- DB connections, API latency (p95), active sessions count
- `GET /admin/system-metrics`
- `GET /admin/system-health`
- `GET /admin/active-sessions`

**Employees Page:**
Table: name, role badge, branch, status, actions
Form fields: first_name, last_name, username, email, phone, role, branch_id, hire_date, salary

**System Monitoring:**
- Real-time-style metrics cards (polling every 30s via `useQuery refetchInterval`)
- `GET /admin/performance-report`
- `GET /admin/security-events`

---

## 9. API Endpoint Mapping Reference

### Base URL
```
https://api.kesararamwithdigital.tech/api/v1
```

### Auth
```
POST   /auth/login          { username|email, password } => { access_token, user }
POST   /auth/logout
GET    /auth/me
```

### Products (read — STAFF+)
```
GET    /products            ?search=&category_id=&brand_id=&size_id=&color_id=&min_price=&max_price=&page=&per_page=
GET    /products/:id
GET    /products/:id/variants
GET    /products/:id/images
GET    /products/barcode/:barcode
GET    /variants/:id
```

### Categories, Brands, Sizes, Colors (read — STAFF+)
```
GET    /categories
GET    /brands
GET    /sizes
GET    /colors
```

### Bundles & Promotions (read — STAFF+)
```
GET    /bundles
GET    /promotions
GET    /branches
GET    /inventory/low-stock
GET    /banners
GET    /settings
```

### Cart (STAFF+)
```
GET    /cart
POST   /cart          { variant_id, quantity }
PUT    /cart/:id      { quantity }
DELETE /cart/:id
DELETE /cart/clear
```

### Wishlist (STAFF+)
```
GET    /wishlist
POST   /wishlist      { variant_id }
DELETE /wishlist/:id
DELETE /wishlist/clear
```

### Payments (STAFF+)
```
POST   /payments/initiate
POST   /payments/confirm
GET    /payments/:id/status
GET    /receipts/:id
GET    /receipts/order/:order_id
POST   /receipts/:id/email
POST   /receipts/:id/sms
GET    /gift-cards/:code/validate
```

### Session & Customers (CASHIER+)
```
POST   /auth/session/refresh
GET    /customers                ?search=
POST   /customers
GET    /customers/:id
PUT    /customers/:id
DELETE /customers/:id
POST   /customers/bulk-import
```

### POS Shifts (CASHIER+)
```
GET    /shifts
POST   /shifts/open             { opening_float }
GET    /shifts/:id
POST   /shifts/close/:id        { closing_float }
```

### Orders (CASHIER+)
```
GET    /orders
POST   /orders                  { customer_id?, items: [{variant_id, qty}] }
GET    /orders/:id
PUT    /orders/:id
DELETE /orders/:id
POST   /orders/pos-checkout     { order_id, payment_method, amount_paid }
```

### Invoices & Estimates (CASHIER+)
```
GET    /invoices/:id
POST   /estimates               { customer_id, items }
GET    /estimates/:id
POST   /gift-cards/redeem       { code, order_id }
```

### Shipping (CASHIER+)
```
GET    /shipping/rates          { order_id }
POST   /shipping/create         { order_id, carrier, method }
GET    /shipping/:id/track
POST   /offline-sync            { orders: [...] }
GET    /offline-sync/status
```

### Dashboard (CASHIER+)
```
GET    /dashboard/summary
GET    /dashboard/alerts
```

### Analytics & Forecasting (MANAGER+)
```
GET    /analytics/sales         ?from=&to=
GET    /analytics/forecasting
GET    /analytics/product-trends
```

### Categories / Brands / Sizes / Colors CRUD (MANAGER+)
```
POST   /categories    PUT /categories/:id    DELETE /categories/:id
POST   /brands        PUT /brands/:id        DELETE /brands/:id
POST   /sizes         PUT /sizes/:id         DELETE /sizes/:id
POST   /colors        PUT /colors/:id        DELETE /colors/:id
```

### Products CRUD (MANAGER+)
```
POST   /products
PUT    /products/:id
DELETE /products/:id
POST   /variants                { product_id, size_id, color_id, sku, price, stock }
PUT    /variants/:id
DELETE /variants/:id
GET    /products/:id/variants
```

### Bundles & Promotions CRUD (MANAGER+)
```
POST   /bundles       PUT /bundles/:id
POST   /promotions    PUT /promotions/:id    DELETE /promotions/:id
```

### Suppliers & Purchases (MANAGER+)
```
GET/POST /suppliers   GET/PUT/DELETE /suppliers/:id
GET/POST /purchases   GET/PUT/DELETE /purchases/:id
POST     /purchases/:id/receive
```

### Inventory (MANAGER+)
```
GET    /inventory
POST   /inventory/movement      { variant_id, type, quantity, note }
GET    /inventory/movements
POST   /inventory/bulk-update
GET    /inventory/batches        (FIFO)
POST   /transfers
GET    /transfers
GET    /transfers/:id
PUT    /transfers/:id/status     { status: 'pending'|'in_transit'|'received'|'completed' }
```

### Branches (MANAGER+)
```
GET    /branches
POST   /branches
```

### Images (MANAGER+)
```
POST   /products/:id/images           (Cloudinary upload)
PUT    /products/:id/images/reorder
DELETE /products/:id/images/:image_id
POST   /variants/:id/image
PUT    /products/:id/cover
```

### Order Void / Audit / Banners (MANAGER+)
```
DELETE /orders/:id/void
GET    /audit-logs
GET    /audit-logs/:id
POST   /banners        PUT /banners/:id
```

### Reports & Exports (MANAGER+)
```
GET    /reports/sales-summary
GET    /reports/inventory-valuation
GET    /reports/product-performance
GET    /reports/category-revenue
GET    /reports/staff-performance
GET    /reports/customer-analytics
GET    /reports/purchase-analysis
GET    /reports/financial-summary
GET    /reports/daily-summary
GET    /exports/sales             ?format=csv|pdf
GET    /exports/inventory
GET    /exports/customers
GET    /exports/products
```

### AI Intelligence (MANAGER+)
```
GET    /ai/demand-forecast
GET    /ai/recommendations
GET    /ai/bundle-performance
GET    /ai/customer-segments
GET    /ai/reorder-suggestions
```

### GDPR & Webhooks (MANAGER+)
```
GET /gdpr/data-export/:customer_id
POST /gdpr/data-deletion/:customer_id
GET /gdpr/audit-trail
POST /webhooks   GET /webhooks   PUT /webhooks/:id   DELETE /webhooks/:id
```

### Employees (ADMIN)
```
GET    /employees
POST   /employees
GET    /employees/:id
PUT    /employees/:id
DELETE /employees/:id
GET    /employees/:id/performance
GET    /employees/attendance
POST   /employees/bulk-import
```

### Admin System (ADMIN)
```
PUT    /users/:id/reset-password
GET    /admin/system-metrics
GET    /admin/system-health
GET    /admin/active-sessions
GET    /admin/performance-report
GET    /admin/security-events
GET    /admin/usage-statistics
```

---

## 10. RBAC Permission Map

```ts
// lib/rbac.ts
export type Role = 'staff' | 'cashier' | 'manager' | 'admin';

export const PERMISSIONS = {
  // product reading
  'products.read':       ['staff', 'cashier', 'manager', 'admin'],
  // catalog writing
  'products.write':      ['manager', 'admin'],
  'categories.write':    ['manager', 'admin'],
  'brands.write':        ['manager', 'admin'],
  'variants.write':      ['manager', 'admin'],
  'images.upload':       ['manager', 'admin'],
  // POS actions
  'orders.create':       ['cashier', 'manager', 'admin'],
  'orders.void':         ['cashier', 'manager', 'admin'],
  'shifts.manage':       ['cashier', 'manager', 'admin'],
  'customers.write':     ['cashier', 'manager', 'admin'],
  // inventory
  'inventory.write':     ['manager', 'admin'],
  'transfers.manage':    ['manager', 'admin'],
  'purchases.manage':    ['manager', 'admin'],
  'suppliers.manage':    ['manager', 'admin'],
  // reports / analytics
  'reports.view':        ['manager', 'admin'],
  'analytics.view':      ['manager', 'admin'],
  'ai.view':             ['manager', 'admin'],
  'exports.download':    ['manager', 'admin'],
  // admin only
  'employees.manage':    ['admin'],
  'system.monitor':      ['admin'],
  'users.manage':        ['admin'],
} as const;

export function can(role: Role, permission: keyof typeof PERMISSIONS): boolean {
  return (PERMISSIONS[permission] as readonly string[]).includes(role);
}
```

Use `can(role, 'products.write')` in components to conditionally render
action buttons, form fields, or entire pages.

---

## 11. Middleware Route Guard

```ts
// middleware.ts
import { NextRequest, NextResponse } from 'next/server';

const ROLE_PREFIXES: Record<string, string[]> = {
  '/admin':   ['admin'],
  '/manager': ['manager', 'admin'],
  '/pos':     ['cashier', 'manager', 'admin'],
  '/staff':   ['staff', 'cashier', 'manager', 'admin'],
};

export function middleware(request: NextRequest) {
  const token = request.cookies.get('access_token')?.value;
  const role  = request.cookies.get('user_role')?.value;

  if (!token) {
    return NextResponse.redirect(new URL('/login', request.url));
  }

  for (const [prefix, allowed] of Object.entries(ROLE_PREFIXES)) {
    if (request.nextUrl.pathname.startsWith(prefix)) {
      if (!role || !allowed.includes(role)) {
        return NextResponse.redirect(new URL('/unauthorized', request.url));
      }
    }
  }

  return NextResponse.next();
}

export const config = {
  matcher: ['/admin/:path*', '/manager/:path*', '/pos/:path*', '/staff/:path*'],
};
```

---

## 12. Shared Component Patterns

### DataTable

```tsx
// Re-usable TanStack Table wrapper
// Props: columns, data, isLoading, pagination, onRowClick
// Features: column sorting, global search, row selection, export button
```

### StatusBadge

Map API status strings to color variants:
```ts
active / completed / received  => green
pending / in_transit / draft   => yellow
inactive / void / cancelled    => red
```

### CurrencyDisplay

Always format amounts with the currency symbol returned by `GET /currencies`.
Use `Intl.NumberFormat`.

### ExportButton

Calls `GET /exports/:type?format=csv|pdf`, triggers file download.

---

## 13. Key UX Details

1. **Global search** (staff & manager): searchable across products, orders,
   customers using the `?search=` query param.

2. **Barcode scanner (POS)**: hidden `<input>` captures rapid keystrokes from
   USB/Bluetooth scanner, debounces 60ms, then calls
   `GET /products/barcode/:barcode` and adds to cart.

3. **Offline mode (POS)**: if network fails, orders queue in IndexedDB and sync
   via `POST /offline-sync` on reconnect. Show "Offline" banner.

4. **Low stock alerts**: poll `GET /dashboard/alerts` every 2 minutes,
   show sticky banner if `type === 'low_stock'`.

5. **Shift enforcement**: if `GET /shifts` returns no open shift, the POS
   checkout button is disabled and shows "Open a shift first".

6. **Image upload**: drag-and-drop to Cloudinary via
   `POST /products/:id/images`. Show upload progress, then reorder via
   `PUT /products/:id/images/reorder` using `@dnd-kit`.

7. **Receipt flow**: after checkout success, auto-open print dialog via
   `react-to-print` on the `GET /receipts/order/:id` response rendered as
   thermal receipt HTML.

8. **Manager AI panel**: show forecast cards with trend arrows. If
   `reorder_suggested: true` on a product, highlight row in inventory table
   with a warning badge.

9. **Audit log viewer**: read-only table with filters by user, action type,
   date range. No delete controls — admin read only.

10. **GDPR**: export and deletion trigger confirmation modal with typed
    confirmation ("Type the customer name to confirm deletion").

---

## 14. Environment Variables

```env
NEXT_PUBLIC_API_URL=https://api.kesararamwithdigital.tech/api/v1
NEXT_PUBLIC_APP_NAME=OutfitShop
COOKIE_SECRET=<32-char random string>
NEXT_PUBLIC_CLOUDINARY_CLOUD_NAME=<value>
```

---

## 15. Implementation Instructions for the Agent

When scaffolding this project, follow this order:

1. Initialize Next.js 14 with TypeScript + Tailwind + shadcn/ui
2. Set up `lib/api.ts`, `lib/rbac.ts`, `providers/` and `middleware.ts`
3. Build the login page and auth flow first
4. Build shared components: DataTable, ProductCard, MetricCard, StatusBadge
5. Build STAFF layout + product browse (simplest read-only entry point)
6. Build CASHIER/POS terminal
7. Build MANAGER dashboard + catalog CRUD
8. Build ADMIN system monitor + employee management
9. Wire all API calls using TanStack Query hooks in `hooks/`
10. Add Framer Motion page transitions last

Never hard-code role strings in JSX — always reference `PERMISSIONS` from
`lib/rbac.ts` and the `useRole()` hook.

Never expose `access_token` in client-side JavaScript — keep it in
`httpOnly` cookie only.

Always handle API pagination: `data.meta.current_page`, `data.meta.last_page`,
`data.meta.total` from the standard Laravel paginator envelope.

---

## 16. Standard API Response Envelope

```ts
interface ApiResponse<T> {
  success: boolean;
  message: string;
  data: T;
  meta?: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
}
```

All hooks must unwrap `response.data.data` and expose `response.data.meta`
for pagination.
