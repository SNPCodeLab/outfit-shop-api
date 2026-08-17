---
name: kwd-frontend-design-system
description: >
  Frontend design system, architectural specification, and UI/UX styling rules for KhmeRiel (Khmer + Riel) Clothing MIS & POS.
  Covers the dual-interface architecture:
  1. Internal Admin & Staff Controller Portal (Dashboard, Inventory, Sales, Reports, Audit, Employees) using Modern Enterprise UI (shadcn/ui + SalesBinder-style inventory matrices; NEVER use Neo-Brutalism in Portal 1).
  2. Customer-Facing Storefront & Product Showcase using Ralph Lauren-style luxury 3:4 catalog cards with micro color swatches and quick-shop drawers, fused with Neo-Brutalist design tokens.
  STRICT RULES:
  - NEO-BRUTALISM SCOPE: Apply Neo-Brutalism ONLY to PORTAL 2 (Store Showcase / Public). NEVER use Neo-Brutalism in PORTAL 1 (Admin/Staff Portal).
  - NO black shadows (shadow-none or subtle tonal elevation only; no hard pitch-black drop shadows).
  - NEVER use emoji in UI, copy, buttons, badges, or code.
  - USE FontAwesome icons library (@fortawesome/react-fontawesome & @fortawesome/free-solid-svg-icons).
  - BORDER-RADIUS: 3px across all containers, cards, inputs, buttons, dialogs, and swatches.
---

# KhmeRiel — Frontend Architecture & Design System

## 1. Brand Identity: KhmeRiel (Clothing MIS & POS)

| Property | Value |
|---|---|
| **Brand Name** | **KhmeRiel** (Khmer Culture + Riel Currency) |
| **Official Brand Logo** | `https://res.cloudinary.com/od8t271n/image/upload/v1786898754/KhmerRiel.png` |
| **Subtitle / Domain** | **KhmeRiel • Clothing & POS MIS** |
| **Frontend Portal URL** | `https://app.kesararamwithdigital.tech` |
| **Backend REST API Gateway**| `https://api.kesararamwithdigital.tech/api/v1` |
| **Visual Architecture** | Strict separation of concerns between **Portal 1 (Modern Enterprise)** and **Portal 2 (Ralph Lauren + Neo-Brutalism)**. |

---

## 2. Dual-Interface Architecture & Strict Visual Scope

```
                          ┌──────────────────────────────────────────────┐
                          │         KhmeRiel Next.js Web App             │
                          │        app.kesararamwithdigital.tech         │
                          └──────────────────────┬───────────────────────┘
                                                 │
                   ┌─────────────────────────────┴─────────────────────────────┐
                   ▼                                                           ▼
    ┌──────────────────────────────┐                            ┌──────────────────────────────┐
    │   PORTAL 1: CONTROLLER PORTAL│                            │   PORTAL 2: STORE SHOWCASE   │
    │   (Admin / Manager / Staff)  │                            │      (Customer / Public)     │
    │                              │                            │                              │
    │ • STYLE: Modern Enterprise   │                            │ • STYLE: Luxury Neo-Brutalism│
    │   shadcn/ui + SalesBinder    │                            │   Ralph Lauren 3:4 Grid      │
    │ • Crisp subtle slate borders │                            │ • Solid 2.5px black borders  │
    │ • High-density data tables   │                            │ • Canary yellow (#FEE227)    │
    │ • Clean professional forms   │                            │ • Warm cream canvas (#FAF7F0)│
    │ • NEVER USE NEO-BRUTALISM    │                            │ • ONLY APPLY NEO-BRUTALISM   │
    │   IN PORTAL 1                │                            │   IN PORTAL 2                │
    │ • FontAwesome & 3px Radius   │                            │ • FontAwesome & 3px Radius   │
    └──────────────┬───────────────┘                            └──────────────┬───────────────┘
                   │                                                           │
                   └─────────────────────────────┬─────────────────────────────┘
                                                 │
                                                 │ Sanctum Bearer Token / HTTPS JSON
                                                 ▼
                          ┌──────────────────────────────────────────────┐
                          │     SS-MIS Backend (Laravel 11 Engine)       │
                          │        api.kesararamwithdigital.tech         │
                          └──────────────────────────────────────────────┘
```

---

## 3. Strict Interface Styling Rules

### 3.1 Universal Rules (Applies to BOTH Portal 1 and Portal 2)
* **NEVER USE EMOJIS**: Do not use any emoji characters anywhere in UI components, headers, buttons, toast alerts, status badges, or copy. Always use FontAwesome icons.
* **DO NOT USE BLACK SHADOWS**: Absolutely no pitch-black drop shadows (`shadow-[..._#000000]`). Maintain flat crisp surfaces (`shadow-none`) or minimal tonal elevation.
* **BORDER-RADIUS: 3px**: Every container, card, modal, input, button, badge, table row container, and dropdown must use `border-radius: 3px;` (`rounded-[3px]`).
* **FONTAWESOME ICON LIBRARY**: All iconography must use the official FontAwesome React library (`@fortawesome/react-fontawesome` & `@fortawesome/free-solid-svg-icons` / `@fortawesome/free-brands-svg-icons`).

---

### 3.2 Portal 1: Internal Admin & Staff Controller Portal (Admin / Manager / Staff)
> **CRITICAL DIRECTIVE**: **NEVER USE NEO-BRUTALISM IN PORTAL 1.**

* **Target Routes**: `/dashboard`, `/pos`, `/inventory`, `/purchases`, `/sales`, `/employees`, `/audit-logs`, `/admin/*`
* **Design Philosophy**: High-density, professional, modern enterprise SaaS UI (inspired by Linear, Vercel Dashboard, and SalesBinder).
* **Styling Tokens**:
  * **Canvas & Surface**: Crisp clean white (`bg-white`) and soft neutral backgrounds (`bg-slate-50` / `bg-zinc-50`).
  * **Borders**: Clean, subtle 1px border outlines (`border border-slate-200` or `border-zinc-200`).
  * **Border Radius**: `rounded-[3px]`.
  * **Shadows**: `shadow-none` or subtle neutral elevation (`shadow-sm`).
  * **Tables & Matrices**: Compact, high-density 2D Size × Color inventory matrix with muted gridlines and monospaced numeric stock counters.
  * **Buttons & Inputs**: Professional shadcn/ui style components with `rounded-[3px]` and subtle focus rings.

---

### 3.3 Portal 2: Customer-Facing Storefront & Product Showcase (Customer / Public)
> **CRITICAL DIRECTIVE**: **ONLY APPLY NEO-BRUTALISM DESIGN TOKENS TO PORTAL 2.**

* **Target Routes**: `/` (Storefront Home), `/shop`, `/product/[id]`, `/lookbook`, `/cart`, `/checkout`
* **Design Philosophy**: Fusion of **Ralph Lauren luxury fashion catalog UX** + **Tactile Neo-Brutalist structural tokens**.
* **Styling Tokens**:
  * **3:4 Portrait Image Tiles**: Every clothing item renders in a high-resolution 3:4 portrait aspect ratio on clean studio backgrounds (`#F0EFED` / `#FAF7F0`) using `object-contain`.
  * **Canvas Background**: Warm Cream / Off-White (`bg-[#FAF7F0]`) with a subtle 20px grid line pattern.
  * **Primary Accent**: Canary Yellow (`#FEE227` / `#FFE600`) for CTAs, hero badges, and quick-shop triggers.
  * **Borders**: Solid 2.5px stark pitch black (`border-[2.5px] border-black`).
  * **Border Radius**: Exactly 3px (`rounded-[3px]`).
  * **Shadows**: **NO BLACK SHADOWS** (`shadow-none`).
  * **Interactive Color Swatches**: Micro circular color swatches (`COLORS` table) positioned directly under the title with `rounded-[3px]` and 1.5px black border.
  * **Quick Shop Bar**: Canary yellow action button with FontAwesome shopping bag icon that triggers a bottom drawer or modal to select sizes (S, M, L, XL, 2XL).

---

## 4. Master Single-Prompt to Generate the Next.js Frontend

*(Copy this prompt into any AI/coding environment to build the entire KhmeRiel frontend)*

```markdown
Act as a Principal Full-Stack Engineer and World-Class UI/UX Designer. Build a complete, production-ready Next.js 14+ (App Router, TypeScript) frontend application for "KhmeRiel" (Clothing & POS MIS) deployed at `https://app.kesararamwithdigital.tech` connecting to `https://api.kesararamwithdigital.tech/api/v1`.

### 1. DUAL-INTERFACE ARCHITECTURAL RULES (STRICT SEPARATION)
The application has TWO distinctly styled interfaces:

A. PORTAL 1: Internal Admin & Staff Controller (/dashboard, /pos, /inventory, /sales, /admin)
- Style: Modern Enterprise SaaS UI (shadcn/ui + SalesBinder matrix).
- NEVER use Neo-Brutalism in Portal 1.
- Canvas: Clean white (bg-white) and subtle neutral slate (bg-slate-50).
- Borders: Subtle 1px neutral borders (border border-slate-200).
- Border Radius: Exactly 3px (rounded-[3px]).
- Shadows: Flat (shadow-none) or subtle soft elevation (shadow-sm). No black drop shadows.
- Icons: FontAwesome solid icons.
- Emojis: NEVER use emojis anywhere.

B. PORTAL 2: Customer-Facing Store Showcase (/shop, /product/[id], /)
- Style: Ralph Lauren Luxury Catalog UX + Neo-Brutalism.
- ONLY apply Neo-Brutalism tokens to Portal 2.
- Canvas: Warm Cream (#FAF7F0) with subtle grid background.
- Primary Accent: Canary Yellow (#FEE227 / #FFE600).
- Borders: Solid 2.5px stark black (border-[2.5px] border-black).
- Border Radius: Exactly 3px (rounded-[3px]).
- Shadows: NO black drop shadows (shadow-none).
- Photography: 3:4 portrait aspect ratio images with object-contain.
- Swatches: Micro interactive color swatches with rounded-[3px].
- Icons: FontAwesome solid icons.
- Emojis: NEVER use emojis anywhere.

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

### 4. CORE SCREENS TO BUILD
1. Authentication Portal (app/(auth)/login/page.tsx): Clean card with 3px border-radius, email/password fields, FontAwesome icons, and POST /auth/login.
2. Executive MIS Dashboard (app/(dashboard)/dashboard/page.tsx - Portal 1 Enterprise Style): 4 stat cards consuming GET /dashboard/stats, 7-Day API Request Traffic chart, and Recent Requests Data Table.
3. High-Speed POS Checkout Counter (app/(dashboard)/pos/page.tsx - Portal 1 Enterprise Style): Continuous auto-focus Barcode scanner input (GET /variants/barcode/{barcode}), sound cues (GET /settings/audio-cues), coupon verify (POST /promotions/verify-coupon), cart manager, payment methods (KHQR, Cash, Card), and checkout submission (POST /sales/checkout).
4. SalesBinder-Style Matrix Inventory (app/(dashboard)/inventory/page.tsx - Portal 1 Enterprise Style): Searchable table (GET /products), 2D Size × Color Grid (GET /products/{id}/matrix), low-stock warning (GET /variants/low-stock), and adjustment dialog (POST /stock-movements/adjust).
5. Ralph Lauren Luxury Storefront (app/(shop)/page.tsx - Portal 2 Neo-Brutalist Style): 4-column luxury grid with 3:4 portrait images, micro color swatches, Quick-Shop drawer, banners (GET /marketing/banners), brands (GET /brands), and bundles (GET /bundles).
6. Product Detail (app/(shop)/[id]/page.tsx - Portal 2 Neo-Brutalist Style): Multi-angle photo gallery (GET /products/{id}/images), colorways (GET /products/{id}/colorways), reviews (GET /products/{id}/reviews), and wishlist toggle (POST /wishlist/toggle).
7. Sales History (app/(dashboard)/sales/page.tsx - Portal 1 Enterprise Style): Paginated sales from GET /sales and void action POST /sales/{id}/void.
8. Employees & Audit (app/(dashboard)/admin/page.tsx - Portal 1 Enterprise Style): Staff CRUD and audit logs (GET /audit-logs).

Provide the complete, copy-paste ready code for tailwind.config.js, globals.css, lib/api.ts, hooks/useRoleGuard.ts, and screen components.
```
