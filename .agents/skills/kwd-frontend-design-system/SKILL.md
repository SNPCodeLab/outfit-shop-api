---
name: kwd-frontend-design-system
description: >
  Frontend design system, architectural specification, and UI/UX styling rules for KhmeRiel (Khmer + Riel) Clothing MIS & POS.
  Covers the dual-interface architecture:
  1. Internal Admin & Staff Controller Portal (Dashboard, Inventory, Sales, Reports, Audit, Employees) using Modern Enterprise UI (shadcn/ui + SalesBinder-style inventory matrices; NEVER use Neo-Brutalism in Portal 1).
  2. Customer-Facing Storefront & Product Showcase using Ralph Lauren-style luxury 3:4 catalog cards with micro color swatches and quick-shop drawers, fused with Neo-Brutalist design tokens.
  STRICT RULES:
  - BRAND SCOPE: The brand name "KhmeRiel (Clothing MIS & POS)" is strictly for FRONTEND use only. NEVER use or mention this brand name in Backend API responses, endpoints, routes, status responses, or backend guides.
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
| **Visual Architecture** | Strict separation of concerns between **Portal 1 (Modern Enterprise via shadcn/ui)** and **Portal 2 (Ralph Lauren + Neo-Brutalism)**. |

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
    │ • NO black shadows           │                            │ • NO black shadows           │
    │ • Zero emojis                │                            │ • Zero emojis                │
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

## 3. Strict Universal Styling Directives (Both Portals)

1. **NEVER USE EMOJIS**: Do not use any emoji characters anywhere in UI components, headers, buttons, toast alerts, status badges, or copy. Always use FontAwesome icons.
2. **DO NOT USE BLACK SHADOWS**: Absolutely no pitch-black drop shadows (`shadow-[..._#000000]`). Maintain flat crisp surfaces (`shadow-none`) or minimal tonal elevation (`shadow-sm`).
3. **BORDER-RADIUS: 3px**: Every container, card, modal, input, button, badge, table row container, and dropdown must use `border-radius: 3px;` (`rounded-[3px]`).
4. **FONTAWESOME ICON LIBRARY**: All iconography must use the official FontAwesome React library (`@fortawesome/react-fontawesome` & `@fortawesome/free-solid-svg-icons` / `@fortawesome/free-brands-svg-icons`).

---

## 4. shadcn/ui Component Recommendations for Portal 1 (Controller & POS)

> **CRITICAL RULE**: Portal 1 must look like a sleek, ultra-fast modern SaaS enterprise application (Linear, Vercel Dashboard, Stripe, SalesBinder). **DO NOT apply Neo-Brutalism to Portal 1.**

### 4.1 Recommended shadcn/ui Theme Configuration (`globals.css`)
Configure Tailwind and shadcn CSS variables with `--radius: 3px` and clean Slate/Zinc neutrals:

```css
@layer base {
  :root {
    --background: 0 0% 100%;
    --foreground: 222.2 84% 4.9%;
    --card: 0 0% 100%;
    --card-foreground: 222.2 84% 4.9%;
    --popover: 0 0% 100%;
    --popover-foreground: 222.2 84% 4.9%;
    --primary: 222.2 47.4% 11.2%;
    --primary-foreground: 210 40% 98%;
    --secondary: 210 40% 96.1%;
    --secondary-foreground: 222.2 47.4% 11.2%;
    --muted: 210 40% 96.1%;
    --muted-foreground: 215.4 16.3% 46.9%;
    --accent: 210 40% 96.1%;
    --accent-foreground: 222.2 47.4% 11.2%;
    --destructive: 0 84.2% 60.2%;
    --destructive-foreground: 210 40% 98%;
    --border: 214.3 31.8% 91.4%;
    --input: 214.3 31.8% 91.4%;
    --ring: 222.2 84% 4.9%;
    --radius: 3px; /* Locked 3px radius */
  }
}
```

---

### 4.2 Module-by-Module shadcn/ui Component Breakdown

#### A. POS Cash Register Screen (`/pos`)
* **`Input` (Barcode Scanner)**: Large, auto-focus input with prefix FontAwesome barcode icon (`faBarcode`), styled with `h-12 text-lg font-mono rounded-[3px] border-slate-300 focus-visible:ring-1 focus-visible:ring-slate-900`.
* **`Card` & `CardContent` (Cart Manager)**: Structured cart container with `rounded-[3px] border border-slate-200 shadow-none`.
* **`ScrollArea`**: Smooth, non-overflowing scrolling container for high-speed cart item listing.
* **`Badge`**: Clean status pills for 10% VAT (`variant="outline" className="rounded-[3px] font-mono text-xs"`).
* **`Tabs`, `TabsList`, `TabsTrigger`**: Clean tabbed switcher for tender payment methods (`Cash USD`, `Cash KHR`, `ABA KHQR`, `Card`) with `rounded-[3px]`.
* **`Dialog`, `DialogContent`, `DialogHeader`, `DialogTitle`**:
  * **Bakong Dynamic KHQR Modal**: Centered modal with high-res QR code, live total amount display, countdown timer, and status spinner.
  * **Thermal Receipt Preview Modal**: Monospaced 80mm ESC/POS layout preview with one-click thermal print button.

#### B. Inventory 2D Matrix Screen (`/inventory`)
* **`Table`, `TableHeader`, `TableRow`, `TableHead`, `TableBody`, `TableCell`**: SalesBinder-style 2D grid matrix. Size columns (S, M, L, XL, 2XL) across Color rows with high-density compact cells (`py-2 px-3 text-sm font-mono`).
* **`Popover`, `PopoverTrigger`, `PopoverContent`**: Quick-adjust stock drawer on cell click without reloading the full page.
* **`Sheet`, `SheetContent`, `SheetHeader`**: Slide-out drawer on the right side for creating new product SKUs, uploading gallery images, and managing FIFO batches.
* **`Tooltip`, `TooltipTrigger`, `TooltipContent`**: Shows reorder levels, cost prices, and margin percentages on cell hover.

#### C. Executive MIS Dashboard (`/dashboard`)
* **`Card` (KPI Stat Tiles)**: 4 clean KPI cards with subtle top border highlight, displaying Daily Revenue, Total Orders, Average Basket Size, and Low Stock Alerts.
* **`Separator`**: Clean horizontal dividers between sections.
* **`DropdownMenu`**: Date range selector (`Today`, `Last 7 Days`, `This Month`, `Custom Range`).
* **Recharts Integration**: Clean line, bar, and donut charts styled with Tailwind slate/neutral palettes.

#### D. Admin & Security Console (`/admin/*`)
* **`DataTable` (TanStack Table)**: Paginated table with multi-column sorting, search filters, and row selection.
* **`AlertDialog`, `AlertDialogAction`, `AlertDialogCancel`**: Confirmation prompt for destructive operations (Voiding invoices, deleting employees).
* **`Form`, `FormField`, `FormItem`, `FormLabel`, `FormControl`, `FormMessage`**: Integrated with `react-hook-form` + `zod` for strictly typed employee creation and coupon provisioning.

---

## 5. Summary Table: Portal 1 vs. Portal 2 Component Specification

| UI Component | Portal 1: Controller & POS (Admin/Manager/Staff) | Portal 2: Storefront Showcase (Customer/Public) |
| :--- | :--- | :--- |
| **Framework Style** | **shadcn/ui Enterprise SaaS** | **Ralph Lauren Luxury + Neo-Brutalism** |
| **Borders** | Subtle `1px` neutral (`border border-slate-200`) | Solid `2.5px` stark black (`border-[2.5px] border-black`) |
| **Border Radius** | Exactly `3px` (`rounded-[3px]`) | Exactly `3px` (`rounded-[3px]`) |
| **Canvas Background** | Pure White (`bg-white`) / Soft Slate (`bg-slate-50`) | Warm Cream (`bg-[#FAF7F0]`) with 20px grid lines |
| **Accent Color** | Slate 900 (`bg-slate-900 text-white`) | Canary Yellow (`#FEE227` / `#FFE600`) |
| **Shadows** | `shadow-none` or subtle `shadow-sm` (**NO black shadows**) | `shadow-none` (**NO black shadows**) |
| **Icons** | FontAwesome Solid Icons (`@fortawesome/...`) | FontAwesome Solid Icons (`@fortawesome/...`) |
| **Emojis** | **STRICTLY PROHIBITED** | **STRICTLY PROHIBITED** |
| **Product Layout** | SalesBinder 2D Matrix Table Grid | 3:4 Portrait Aspect Ratio Luxury Fashion Cards |
| **Cart Interaction** | High-speed POS Barcode Counter | Quick-Shop Slide-Up Drawer with Color Swatches |
