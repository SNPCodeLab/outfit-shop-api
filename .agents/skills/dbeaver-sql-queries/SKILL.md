---
name: dbeaver-sql-queries
description: How to visually view data, open the SQL Editor, write and execute custom SQL queries (SELECT, INSERT, UPDATE, JOINs), and filter tables visually in DBeaver. Use whenever querying or inspecting database tables in DBeaver GUI.
---

# DBeaver Visual Data & SQL Query Guide

This skill provides step-by-step instructions for opening the SQL Editor, writing and executing SQL queries, and visually editing table data in **DBeaver GUI**.

---

## 📑 1. How to View Table Data Visually (No SQL Needed)

To view your data in a spreadsheet grid (like Excel) without writing code:

1. Expand your connection on the left pane: **Connections** ➔ **Schemas** ➔ **public** ➔ **Tables**.
2. **Double-click** any table (e.g., `products`, `sale_headers`, `customers`).
3. Click the **Data** tab at the top of the middle panel.
4. **Filter rows visually**: Type a condition in the top filter bar (e.g. `quantity > 10` or `status = 'ACTIVE'`) and press `Enter`.
5. **Edit cell values visually**: Double-click any grid cell, change the value, and click **Save Script / Apply (`Cmd + S` / `Ctrl + S`)** at the bottom toolbar.

---

## ⚡ 2. How to Open the SQL Query Editor in DBeaver

1. Select your database connection in the left **Connections** panel.
2. Open the SQL Editor using one of these options:
   * **Toolbar Icon**: Click the **SQL** button with a page icon at the top left toolbar.
   * **Menu**: Click **SQL Editor** ➔ **New SQL Script**.
   * **Keyboard Shortcut**: Press `F4` (or `Cmd + ]` on Mac).

---

## 💻 3. Standard SQL Query Examples for SS-MIS

### 1. Read / View Records (`SELECT`)

```sql
-- View all products
SELECT * FROM products;

-- View recent sales ordered by date
SELECT sale_id, customer_id, total_amount, created_at 
FROM sale_headers 
ORDER BY created_at DESC;

-- View low stock product variants
SELECT variant_id, sku, sale_price, quantity 
FROM product_variants 
WHERE quantity <= 5;
```

### 2. Relational Join Query (`JOIN`)

```sql
-- Combine Products with Variant details (Size, Color, Price, Stock)
SELECT 
    p.product_name,
    c.category_name,
    pv.sku,
    pv.sale_price,
    pv.quantity
FROM products p
JOIN categories c ON p.category_id = c.category_id
JOIN product_variants pv ON p.product_id = pv.product_id
ORDER BY p.product_name ASC;
```

### 3. Insert New Record (`INSERT`)

```sql
INSERT INTO categories (category_name, description, created_at, updated_at) 
VALUES ('Outerwear', 'Jackets, coats, and hoodies', NOW(), NOW());
```

### 4. Update Existing Record (`UPDATE`)

```sql
UPDATE product_variants 
SET sale_price = 29.99 
WHERE sku = 'TSHIRT-BLK-M';
```

---

## ▶️ 4. How to Execute Queries in DBeaver

| Action | Shortcut (Mac) | Shortcut (Windows/Linux) | DBeaver Button |
| :--- | :--- | :--- | :--- |
| **Execute Current Statement** | `Cmd + Enter` | `Ctrl + Enter` | Orange Play icon (single arrow) |
| **Execute Entire Script** | `Cmd + Shift + Enter` | `Alt + X` | Blue Double-Play icon |
| **Format SQL Code** | `Cmd + Shift + F` | `Ctrl + Shift + F` | Right-click ➔ Format ➔ Format SQL |

---

## 🔍 5. Exporting Results

1. Run your `SELECT` query in the SQL Editor.
2. At the bottom of the result panel, click **Export Data** (bottom toolbar icon).
3. Select format: **CSV**, **JSON**, **Excel**, or **HTML**.
4. Choose target file destination and click **Proceed**.
