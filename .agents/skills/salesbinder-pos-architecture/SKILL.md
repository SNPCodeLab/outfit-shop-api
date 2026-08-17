---
name: salesbinder-pos-architecture
description: >
  SalesBinder-inspired architectural specification for KhmeRiel MIS & POS.
  Covers the 4-tier quantity lifecycle (On Hand, Reserved, Available, Incoming),
  financial asset valuation formulas (Purchased Cost Value vs Resale Retail Value, Gross Margin),
  estimates to invoice 1-click billing workflow, A4 printable PDF invoice rendering,
  and the Help Centre Knowledge Base standards (/guide, /api/v1/guide).
---

# SalesBinder-Inspired MIS & POS Architecture Specification

This skill documents the **SalesBinder core architectural patterns** integrated into the KhmeRiel Store Stock & Point-of-Sale Information System (SS-MIS).

---

## 1. The 4-Tier Quantity Lifecycle Model

Inventory in the system is classified into 4 distinct states rather than a single static number:

```
 ┌────────────────────────────────────────────────────────────────────────────────────────┐
 │                        SALESBINDER 4-TIER QUANTITY LIFECYCLE                           │
 │                                                                                        │
 │    [ ON HAND ]      -      [ RESERVED ]      =      [ AVAILABLE ]    +   [ INCOMING ]  │
 │  Physical count           Held in pending            Net sellable         On open POs  │
 │  in warehouse             quotes/estimates           inventory units      from vendors │
 └────────────────────────────────────────────────────────────────────────────────────────┘
```

### 1.1 State Definitions & Calculations

1. **`On Hand` (Physical Inventory)**:
   - The verified physical count of stock present in the warehouse or store branch.
   - Stored in `product_variants.quantity` or `store_inventories.quantity`.

2. **`Reserved` (Allocated Stock)**:
   - Units locked in approved estimates, draft quotations, unpaid invoices, or click-and-collect orders pending fulfillment.
   - Calculation:
     $$\text{Reserved} = \sum_{\text{status} \in \{\text{'PENDING'}, \text{'ESTIMATE'}, \text{'DRAFT'}\}} \text{sale\_details.quantity}$$

3. **`Available` (Net Sellable Stock)**:
   - The immediate net sellable units that can safely be added to a new POS sale or online cart without overselling.
   - Calculation:
     $$\text{Available} = \max(0, \text{On Hand} - \text{Reserved})$$

4. **`Incoming` (Replenishment in Transit)**:
   - Units ordered from suppliers on open Purchase Orders that have not yet been received into physical stock.
   - Calculation:
     $$\text{Incoming} = \sum_{\text{status} \in \{\text{'PENDING'}, \text{'ORDERED'}, \text{'SHIPPED'}\}} \text{purchase\_details.quantity}$$

---

## 2. Financial Asset Valuation Architecture

The system tracks real-time balance sheet inventory valuations and potential revenue via `GET /api/v1/inventory/statistics`:

### 2.1 Valuation Formulas

* **Purchased Value (Cost Basis Asset)**:
  $$\text{Purchased Value} = \sum (\text{Stock Quantity} \times \text{Cost Price})$$
* **Resale Value (Potential Retail Revenue)**:
  $$\text{Resale Value} = \sum (\text{Stock Quantity} \times \text{Unit Sale Price})$$
* **Potential Gross Profit**:
  $$\text{Gross Profit} = \text{Resale Value} - \text{Purchased Value}$$
* **Gross Profit Margin Percentage**:
  $$\text{Margin \%} = \left( \frac{\text{Gross Profit}}{\text{Resale Value}} \right) \times 100$$

### 2.2 Multi-Dimensional Aggregations
* **By Category**: Category breakdown cards showing SKU count, units on hand, and cost basis valuation.
* **By Location / Branch**: Segregated store asset values for multi-warehouse operations.

---

## 3. Invoices, Estimates & Billing Lifecycle

```
 ┌───────────────┐     1-Click Convert      ┌───────────────┐     Payment Settlement     ┌───────────────┐
 │   ESTIMATE    │  ──────────────────────> │    INVOICE    │  ───────────────────────>  │     PAID      │
 │ (Quote/Draft) │  Deducts stock & ledger  │   (Active)    │   Records KHQR/Cash/Card   │  (Archived)   │
 └───────────────┘                          └───────────────┘                            └───────────────┘
```

### 3.1 Lifecycle Endpoints

* **`POST /api/v1/estimates`**:
  - Creates a formal quotation estimate with line items, customer bill-to details, and 10% VAT calculation.
  - **Does NOT immediately deduct physical inventory** (locks as `Reserved`).
* **`POST /api/v1/estimates/{id}/convert`**:
  - **1-Click Convert**: Verifies stock availability, decrements physical quantity on variants, writes immutable `stock_movements` ledger rows, and updates status to `COMPLETED`.
* **`GET /api/v1/sales/{id}/invoice-pdf`**:
  - Renders a professional, high-resolution A4 printable Tax Invoice view with company branding, invoice #, customer bill-to address, line items breakdown, 10% VAT tax breakdown, and payment status badges.

---

## 4. Help Centre & Developer Knowledge Base

Accessible at:
* **Web UI**: `https://api.kesararamwithdigital.tech/guide` *(or `/kb`)*
* **JSON API**: `https://api.kesararamwithdigital.tech/api/v1/guide` *(or `/api/v1/docs`)*

### 4.1 UI Design Guidelines
* **No Mixed / Rainbow Colors**: Single-tone neutral slate and deep navy monochrome palette (`#0f172a`, `#1e293b`, `#f8fafc`, `#ffffff`).
* **Zero Drop Shadows**: Clean flat cards with crisp `1px` borders (`border: 1px solid #e2e8f0;`).
* **Zero Emojis**: Replaced with FontAwesome SVG icons (`@fortawesome/react-fontawesome` / CDN).
* **`border-radius: 3px`**: Uniform across all cards, buttons, inputs, and pills.
