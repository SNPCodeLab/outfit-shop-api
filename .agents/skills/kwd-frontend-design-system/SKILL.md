---
name: kwd-frontend-design-system
description: >
  Frontend design system, architectural specification, and UI/UX styling rules for KhmeRiel (Khmer + Riel) Clothing MIS & POS.
  Covers the dual-interface architecture:
  1. Internal Admin & Staff Controller Portal (Dashboard, Inventory, Sales, Reports, Audit, Employees) using SalesBinder-style inventory matrices + shadcn/ui + Neo-Brutalist design tokens.
  2. Customer-Facing Storefront & Product Showcase using Ralph Lauren-style luxury 3:4 catalog cards with micro color swatches and quick-shop drawers, fused with the nadanada.me Neo-Brutalism aesthetic.
  Includes RBAC route guards, component specifications, color tokens, and the master copy-ready Next.js build prompt.
---

# KhmeRiel — Frontend Architecture & Neo-Brutalism Design System

## 1. Brand Identity: KhmeRiel (Clothing MIS & POS)

| Property | Value |
|---|---|
| **Brand Name** | **KhmeRiel** (*Khmer Culture + Riel Currency*) |
| **Official Brand Logo** | `https://res.cloudinary.com/od8t271n/image/upload/v1786898754/KhmerRiel.png` |
| **Subtitle / Domain** | **KhmeRiel • Clothing & POS MIS** |
| **Frontend Portal URL** | `https://app.kesararamwithdigital.tech` |
| **Backend REST API Gateway**| `https://api.kesararamwithdigital.tech/api/v1` |
| **Visual Identity** | Fusion of **Ralph Lauren luxury fashion catalog UX** (3:4 portrait tiles, micro color swatches, quick view) + **SalesBinder apparel matrices** + **nadanada.me / shadcn Neo-Brutalist design tokens**. |

---

## 2. Dual-Interface Architecture

```
                          ┌──────────────────────────────────────────────┐
                          │         KhmeRiel Next.js Web App             │
                          │        app.kesararamwithdigital.tech         │
                          └──────────────────────┬───────────────────────┘
                                                 │
                   ┌─────────────────────────────┴─────────────────────────────┐
                   ▼                                                           ▼
    ┌──────────────────────────────┐                            ┌──────────────────────────────┐
    │   PHASE 1: CONTROLLER PORTAL │                            │   PHASE 2: STORE SHOWCASE    │
    │   (Admin / Manager / Staff)  │                            │      (Customer / Public)     │
    │                              │                            │                              │
    │ • SalesBinder-style Size ×   │                            │ • Ralph Lauren-style Luxury  │
    │   Color Variant Matrices     │                            │   Catalog Grid (3:4 Ratio)   │
    │ • Continuous Barcode Scanner │                            │ • Interactive Color Swatches │
    │ • RBAC Role-Gated Controller │                            │ • Quick-Shop Slide-Up Drawer │
    │ • Dashboard, Inventory, POS, │                            │ • Category & Size Filters    │
    │   Sales, Reports, Audit Logs │                            │ • Tactile Neo-Brutalist Look │
    └──────────────┬───────────────┘                            └──────────────┬───────────────┘
                   │                                                           │
                   └─────────────────────────────┬─────────────────────────────┘
                                                 │
                                                 │ Sanctum Bearer Token / HTTPS JSON
                                                 ▼
                          ┌──────────────────────────────────────────────┐
                          │     CSMS-API Backend (Laravel 11 Engine)     │
                          │        api.kesararamwithdigital.tech         │
                          └──────────────────────────────────────────────┘
```

---

## 3. Ralph Lauren-Inspired Fashion Catalog Design (Storefront)

From analyzing **Ralph Lauren (`ralphlauren.com/men-clothing`)**, we adopt these luxury fashion e-commerce UX standards and infuse them into our Neo-Brutalism system:

### 3.1 Luxury Catalog Principles
1. **3:4 Portrait Image Tiles**: Every clothing item renders in a high-resolution 3:4 portrait aspect ratio on clean studio backgrounds (`#F0EFED` / `#FAF7F0`).
2. **Interactive Color Swatches**: Micro circular color swatches (`COLORS` table) positioned directly under the title. Hovering or clicking a swatch switches the preview image and updates active variant SKU.
3. **Quick Shop / ជ្រើសរើសទំហំ Bar**: A canary yellow action button (`#FEE227`) that opens a bottom drawer or modal to select sizes (S, M, L, XL, 2XL) and add to cart without leaving the catalog page.
4. **Sticky Filter & Sort Toolbar**: Top sticky bar with clean Neo-Brutalist dropdowns:
   - `Category` (Shirts, Polos, Trousers, Hoodies)
   - `Size (S - 2XL)`
   - `Color Swatches`
   - `Price Range`
   - `Sort By` (Newest, Price: Low to High, Bestselling)

---

## 4. Visual Design DNA: Neo-Brutalism (nadanada.me & shadcn-retro)

| Element | Specification | Tailwind CSS Utility |
|---|---|---|
| **Borders** | Solid 2.5px stark pitch black on every container and button | `border-2 border-black` or `border-[2.5px] border-black` |
| **Drop Shadows** | Hard offset rectangular shadow (**0px blur radius**) | `shadow-[4px_4px_0px_0px_#000000]` |
| **Active Click** | Physical 3D push-down translation | `hover:translate-x-[1px] hover:translate-y-[1px] active:translate-x-[4px] active:translate-y-[4px] active:shadow-none transition-all` |
| **Canvas Background** | Warm Cream / Off-White with faint subtle grid | `bg-[#FAF7F0]` with `bg-grid-slate-200/50` |
| **Primary Accent** | Canary Yellow | `#FEE227` / `#FFE600` |
| **Typography** | Kantumruy Pro / Siemreap (Khmer) + Inter (English) | `font-black uppercase tracking-tight` |

### Color Tokens

```css
:root {
  --canvas: #FAF7F0;
  --surface: #FFFFFF;
  --ink: #000000;
  --accent-yellow: #FEE227;
  
  /* Status & Role Badges */
  --badge-green: #86EFAC;   /* Success / Cashier Role / Paid / In Stock */
  --badge-blue: #93C5FD;    /* Info / Manager Role / Purchase / API */
  --badge-amber: #FDE047;   /* Warning / Low Stock / Adjustment / Pending */
  --badge-red: #FCA5A5;     /* Danger / Admin Role / Voided / Out of Stock / Delete */
  --badge-gray: #E5E7EB;    /* Staff / Public */
}
```

---

## 5. Master Single-Prompt to Generate the Next.js Frontend

*(Copy this prompt into any AI/coding environment to build the entire KhmeRiel frontend)*

```markdown
Act as a Principal Full-Stack Engineer and World-Class UI/UX Designer. Build a complete, production-ready Next.js 14+ (App Router, TypeScript) frontend application for "KhmeRiel" (Clothing & POS MIS) deployed at `https://app.kesararamwithdigital.tech`.

### 1. BRAND IDENTITY & VISUAL DESIGN DNA
- Brand Name: KhmeRiel (Khmer + Riel)
- Brand Logo URL: https://res.cloudinary.com/od8t271n/image/upload/v1786898754/KhmerRiel.png
- Tagline: KhmeRiel • Clothing & POS MIS
- Web App URL: https://app.kesararamwithdigital.tech
- Design Style: Fusion of Ralph Lauren Luxury Catalog UX + SalesBinder Inventory Matrix + nadanada.me / shadcn Neo-Brutalism.
- Canvas: Warm Cream background (#FAF7F0) with a subtle 20px grid line pattern.
- Primary Accent: Canary Yellow (#FEE227 / #FFE600) for CTAs, active badges, and highlight cards.
- Surface: Pure White (#FFFFFF) for tables, dialogs, modals, and product cards.
- Outlines: Solid 2.5px stark pitch black (border-2 border-black).
- Shadows: Hard rectangular box shadows with ZERO blur (shadow-[4px_4px_0px_0px_#000]).
- Micro-Interactions: 3D physical tactile push-down effect on all buttons and cards (hover:translate-x-[1px] hover:translate-y-[1px] active:translate-x-[4px] active:translate-y-[4px] active:shadow-none transition-all).
- Status Badges: Mint Green (#86EFAC: Paid/Cashier/In Stock), Soft Blue (#93C5FD: Manager/Purchase), Pastel Amber (#FDE047: Low Stock/Pending), Pastel Red (#FCA5A5: Admin/Void/Out of Stock).
- Typography: Kantumruy Pro / Siemreap (Khmer) + Inter (English) with bold uppercase tracking.
- Top Ticker Tape: Full-width black marquee bar with bold white uppercase text (⚡ KHMERIEL • REAL-TIME POS ENGINE • ACID-COMPLIANT STOCK • REST API v1).

### 2. BACKEND API INTEGRATION & AUTHENTICATION
- Production Base URL: https://api.kesararamwithdigital.tech/api/v1
- Authentication: Laravel Sanctum Bearer Token via Authorization: Bearer <token> header.
- Token Persistence: Store access_token in localStorage.getItem('auth_token') and authenticated user profile in auth_user.
- Global Axios Interceptor (lib/api.ts):
  - Automatically inject Bearer token on every outgoing request.
  - Intercept 401 Unauthorized responses to clear local storage and redirect user to /login.
  - Handle standard JSON error schema: { success: false, message: "...", error_code: "..." }.

### 3. ROLE-BASED ACCESS CONTROL (RBAC) MATRIX
Implement a strict useRoleGuard(minRole) client-side route guard enforcing the 4-tier access ladder:
- ADMIN (Rank 4): Full access to Employee CRUD (/admin/employees), Security Audit Logs (/admin/audit), and User Account Creation.
- MANAGER (Rank 3): Access to MIS Analytics Dashboard (/dashboard), Catalog & Variant Matrix CRUD (/inventory), Supplier Purchasing (/reports), Stock Adjustments (/inventory/adjust), and Transaction Voiding (/sales/void).
- CASHIER (Rank 2): Access to POS Barcode Checkout Counter (/pos), Customer Registration (/customers), and Sales Receipts (/sales).
- STAFF (Rank 1): Read-only product catalog lookup.

### 4. CORE SCREENS & MODULES TO BUILD
1. Authentication Portal (app/(auth)/login/page.tsx): Neo-Brutalist card with username/email and password fields, validation error banners, and loading button. Consumes POST /auth/login.
2. Executive MIS Dashboard (app/(dashboard)/dashboard/page.tsx - Manager+): 4 Neo-Brutalist Stat Cards consuming GET /dashboard/stats, 7-Day API Request Traffic chart, and 20 Recent Requests Data Table with latency and HTTP status pills.
3. High-Speed POS Checkout Counter (app/(dashboard)/pos/page.tsx - Cashier+): Continuous auto-focus Barcode/SKU scanner input consuming GET /variants/barcode/{barcode}, audio feedback cues from GET /settings/audio-cues, coupon voucher verification via POST /promotions/verify-coupon, real-time cart manager, customer selector, payment method selector (CASH, CARD, QR, ABA) with change calculation, and checkout submission consuming POST /sales/checkout with instant printable receipt modal.
4. SalesBinder-Style Matrix Inventory Controller (app/(dashboard)/inventory/page.tsx - Manager+): Searchable catalog table from GET /products and GET /variants, Apparel Size (S-2XL) × Color Matrix Grid with real-time stock balances consuming GET /products/{id}/matrix, low-stock warnings from GET /variants/low-stock, batch expiry monitor from GET /inventory/expiring-soon, and stock adjustment dialog consuming POST /stock-movements/adjust.
5. Ralph Lauren-Style Storefront Catalog (app/(dashboard)/shop/page.tsx - Public / All): 4-column luxury product grid with tall 3:4 portrait aspect ratio clothing photography, interactive micro color swatches, hover "Quick View / ជ្រើសរើសទំហំ" size drawer, hero banner carousels from GET /marketing/banners, brand directory from GET /brands, combo bundles from GET /bundles, and sticky filter bar.
6. Product Detail & Lookbook Showcase (app/(dashboard)/shop/[id]/page.tsx - Public / All): Multi-angle photo gallery from GET /products/{id}/images, colorway swatches from GET /products/{id}/colorways, customer reviews & star ratings from GET /products/{id}/reviews, and one-click wishlist saving via POST /wishlist/toggle.
7. Sales History & Void Manager (app/(dashboard)/sales/page.tsx - Cashier / Manager): Paginated transactions from GET /sales with expandable line items and manager void action consuming POST /sales/{id}/void.
8. Multi-Branch & Store Management (app/(dashboard)/branches/page.tsx - Manager+): Branch locator and inventory levels across stores from GET /branches and GET /branches/{id}/stock.
9. Employee & Audit Administration (app/(dashboard)/admin/page.tsx - Admin Only): Staff management table from GET/POST/PUT/DELETE /employees and security audit log feed from GET /audit-logs.

### 5. COMPLETE API ENDPOINT DIRECTORY
| Module | Method & Endpoint | Description |
|---|---|---|
| **Auth** | `POST /auth/login`, `POST /auth/logout`, `GET /auth/me` | Bearer token session authentication |
| **Catalog** | `GET /products`, `GET /products/{id}`, `GET /products/{id}/matrix`, `GET /products/{id}/colorways`, `GET /products/{id}/download` | Omnichannel catalog & luxury matrix |
| **Brands** | `GET /brands`, `GET /brands/{id}`, `POST/PUT/DELETE /brands` | Multi-brand directory and lookbooks |
| **Bundles** | `GET /bundles`, `GET /bundles/{id}`, `POST/DELETE /bundles` | Combo packs and gift sets |
| **Promotions** | `GET /promotions/active`, `POST /promotions/verify-coupon` | POS discounts & voucher verification |
| **Reviews** | `GET /products/{id}/reviews`, `POST /products/{id}/reviews` | Customer ratings & feedback |
| **Branches** | `GET /branches`, `GET /branches/{id}/stock` | Multi-store location inventory |
| **Wishlist** | `GET /wishlist`, `POST /wishlist/toggle` | Saved items and lookbook bookmarks |
| **Batches** | `GET /inventory/expiring-soon`, `GET/POST /variants/{id}/batches` | FMCG FIFO expiry & lot tracking |
| **CMS** | `GET /marketing/banners`, `POST/DELETE /marketing/banners` | Storefront hero carousel banners |
| **Settings** | `GET /settings/audio-cues` | POS sound FX & currency exchange rate |
| **POS Sales** | `POST /sales/checkout`, `GET /sales`, `POST /sales/{id}/void` | ACID transactional checkout & receipts |
| **Inventory** | `GET /variants`, `GET /variants/low-stock`, `POST /stock-movements/adjust` | Stock movements & adjustments |

### 5. PROJECT FILE STRUCTURE TO GENERATE
```text
src/
├── app/
│   ├── layout.tsx
│   ├── globals.css
│   ├── (auth)/
│   │   └── login/page.tsx
│   └── (dashboard)/
│       ├── layout.tsx
│       ├── dashboard/page.tsx
│       ├── pos/page.tsx
│       ├── inventory/page.tsx
│       ├── shop/page.tsx
│       ├── sales/page.tsx
│       └── admin/
│           ├── employees/page.tsx
│           └── audit/page.tsx
├── components/
│   ├── ui/
│   ├── Navbar.tsx
│   ├── TickerTape.tsx
│   ├── POSCart.tsx
│   ├── VariantMatrix.tsx
│   └── ProductTile.tsx
├── hooks/
│   └── useRoleGuard.ts
└── lib/
    ├── api.ts
    └── utils.ts
```

Provide the complete, copy-paste ready code for tailwind.config.js, globals.css, lib/api.ts, hooks/useRoleGuard.ts, components/Navbar.tsx, components/TickerTape.tsx, components/VariantMatrix.tsx, components/ProductTile.tsx, and the primary /dashboard, /pos, and /shop screen implementations.
```

---

## 6. Official Cloudinary CDN Asset Registry & URL Schema

### 6.1 CDN Endpoint Architecture
All media assets are served via high-speed global Edge CDN on Cloudinary:
* **Cloud Name**: `od8t271n`
* **Base URL Format**: `https://res.cloudinary.com/od8t271n/image/upload/v{timestamp}/{public_id}.{format}`
* **Audio URL Format**: `https://res.cloudinary.com/od8t271n/video/upload/v{timestamp}/{public_id}.wav`

### 6.2 Standardized Public ID Naming Convention
All assets follow the strict uppercase semantic format:
`KHMERIEL_{DEPARTMENT}_{STYLE_NAME}_{COLORWAY}_{VIEW_TYPE}_{SEQUENCE_CODE}`

* **Department Types**: `TOPS`, `BOTTOMS`, `DRESSES`, `SKIRTS`, `OUTERWEAR`, `KNITWEAR`, `BAGS`, `SHOES`, `ACCESSORIES`, `MENS_CLASSIC_POLO`, `MENS_DESIGNER_COLLECTION`, `MENS_RLX_GOLF`, `MENS_US_OPEN_TENNIS`, `DIGITAL_BOOK_PUBLICATION`, `MARKETING_BANNER`, `AUDIO_FX`
* **View Types**:
  * `LOOK`: On-model editorial & lookbook photography
  * `FLAT`: Clean product lay-down / cutout photography
  * `DETAIL`: Macro fabric & stitching closeups
  * `IMG`: Catalog grid photography

### 6.3 Live Cloudinary Verified Assets (566+ Active)
```text
Dresses & Gowns:
• KHMERIEL_DRESSES_BACKLESS_SILK_EVENING_DRESS_BLACK_LOOK_cloth_056..059 (.png)
• KHMERIEL_DRESSES_BACKLESS_SILK_EVENING_DRESS_WHITE_LOOK_cloth_061..064 (.png)
• KHMERIEL_DRESSES_CRINOLINE_COLUMN_GOWN_TERRACOTTA_LOOK_cloth_092..096 (.png)
• KHMERIEL_DRESSES_LONG_SLEEVE_CRINOLINE_DRESS_WHITE_LOOK_cloth_079..085 (.png)
• KHMERIEL_DRESSES_V_NECK_SILK_MAXI_DRESS_NAVY_LOOK_cloth_103..107 (.png)

Tops, Shirts & Outerwear:
• KHMERIEL_TOPS_FLUID_DRAPED_SILK_BLOUSE_CREAM_BEIGE_LOOK_cloth_003..012 (.png)
• KHMERIEL_TOPS_OVERSIZED_POPLIN_SHIRT_LOOK_cloth_015..022 (.png)
• KHMERIEL_TOPS_CLASSIC_EMBROIDERED_SILK_SHIRT_BLACK_LOOK_cloth_025..033 (.png)
• KHMERIEL_OUTERWEAR_COLLARED_TAILORED_JACKET_SAGE_GREEN_LOOK_cloth_038..047 (.png)

Bottoms, Culottes & Skirts:
• KHMERIEL_BOTTOMS_MONOGRAM_SILK_CULOTTES_BLACK_LOOK_cloth_259..268 (.png)
• KHMERIEL_BOTTOMS_MONOGRAM_SILK_CULOTTES_IVORY_LOOK_cloth_271..277 (.png)
• KHMERIEL_BOTTOMS_WIDE_TAILORED_SHORTS_CREAM_BEIGE_LOOK_cloth_222..229 (.png)
• KHMERIEL_SKIRTS_CRINOLINE_FLARE_SKIRT_TERRACOTTA_LOOK_cloth_295..298 (.png)
• KHMERIEL_SKIRTS_FLUTED_KNIT_MIDI_SKIRT_BLACK_LOOK_cloth_306..313 (.png)
• KHMERIEL_SKIRTS_BEADED_EVENING_MIDI_SKIRT_BLACK_LOOK_cloth_279..282 (.png)

Luxury Leather Bags & Shoes:
• KHMERIEL_BAGS_MINI_T_LOCK_CROSSBODY_BAG_BLACK_LOOK_cloth_127..128 (.png)
• KHMERIEL_BAGS_MINI_T_LOCK_CROSSBODY_BAG_OFF_WHITE_LOOK_cloth_134..135 (.png)
• KHMERIEL_BAGS_THREE_COMPARTMENT_LEATHER_TOTE_BLACK_LOOK_cloth_142..143 (.png)
• KHMERIEL_BAGS_T_LOCK_LEATHER_CLUTCH_BAG_OFF_WHITE_LOOK_cloth_150..151 (.png)
• KHMERIEL_BAGS_ROAM_LEATHER_EVERYDAY_TOTE_WARM_TAUPE_LOOK_cloth_120..121 (.png)
• KHMERIEL_SHOES_T_STRAP_NAPPA_LEATHER_SANDALS_DARK_BROWN_LOOK_cloth_180..183 (.png)
• KHMERIEL_SHOES_NAPLACK_LEATHER_BALLERINAS_BUTTERCUP_YELLOW_LOOK_cloth_162..165 (.png)

Digital eBooks & Audio:
• KHMERIEL_DIGITAL_BOOK_PUBLICATION_EN_*.pdf (26+ Full eBooks)
• KHMERIEL_POS_SCANNER_AUDIO_FX_*.wav (3 POS Audio Cues)
```

