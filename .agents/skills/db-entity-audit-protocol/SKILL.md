---
name: db-entity-audit-protocol
description: Protocol for performing a comprehensive system-wide database entity audit. Tracks record counts across all core tables (Catalog, Users, Sales, Logs) and calculates catalog richness metrics (e.g., images-to-product ratios) to verify operational readiness.
---

# Database Entity Audit Protocol

Use this protocol to assess the current scale, health, and readiness of the SS-MIS database environment.

## 1. Global Entity Discovery
Run a full-table count to establish the current data footprint.

### Audit Query (Artisan Tinker / SQL)
```php
$tables = [
    'brands', 'categories', 'products', 'product_variants', 
    'product_images', 'employees', 'customers', 'sale_headers', 
    'purchase_headers', 'api_logs', 'audit_logs'
];

foreach ($tables as $table) {
    echo strtoupper($table) . ": " . DB::table($table)->count() . "\n";
}
```

## 2. Catalog Richness Metrics
Evaluate the quality of the master data by checking relationship density.

| Metric | Calculation | Ideal Ratio |
| :--- | :--- | :--- |
| **Visual Density** | `count(product_images) / count(products)` | **> 3.0** |
| **Variant Depth** | `count(product_variants) / count(products)` | **> 1.0** |
| **Active Percentage** | `count(active_products) / count(total_products)` | **100% (Stage)** |

## 3. Operational Readiness Assessment
Verify the state of different architectural layers:

- **Master Layer (Catalog)**: High record count indicates a "Rich" catalog.
- **Identity Layer (Users)**: Presence of 4 standard roles (Admin, Manager, Cashier, Staff) is required for UAT.
- **Transactional Layer (Sales/Finance)**: Should be "Clean" (0-low count) before a production push or fresh audit period.
- **Telemetry Layer (Logs)**: Activity in `api_logs` confirms the gateway is receiving traffic.

## 4. Reporting Standard
Always output a summary table and a detailed breakdown categorized by functional area (Storefront vs. POS vs. Backoffice).
