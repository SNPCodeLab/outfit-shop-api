---
name: oracle-clothing-sales-db
description: Oracle Database schema design, standards, data types, normalization, constraint definitions, header/detail transaction structures, historical price preservation, stock movement tracking, and query references for Clothing Sales Management Systems. Use whenever writing, designing, converting, or querying Oracle SQL for clothing/retail POS and inventory systems.
---

# Oracle Clothing Sales Management System — Database Skill

## Purpose

Use this skill when designing, reviewing, improving, or generating an Oracle Database schema and SQL for a **Clothing Sales Management System**.

The goal is to produce a normalized, practical, maintainable relational database suitable for:

- Clothing product management
- Size and color variants
- Inventory tracking
- Supplier and purchase management
- Customer and employee management
- Sales and sales details
- Payments
- Stock movement history

The database must remain simple enough for an academic project while following real-world relational database practices.

---

## 1. Database Platform

Target database:

**Oracle Database**

Use Oracle SQL data types and syntax.

### Preferred Oracle data types

| Requirement | Oracle type |
|---|---|
| Identifier | `NUMBER(10)` |
| Short text | `VARCHAR2(n)` |
| Money | `NUMBER(12,2)` |
| Quantity | `NUMBER(10)` |
| Date/time | `TIMESTAMP` |
| Long text | `CLOB` when genuinely necessary |
| Boolean-like status | `VARCHAR2(...)` with `CHECK` constraint |

Do not use SQL Server-specific syntax such as:

- `IDENTITY(1,1)`
- `VARCHAR`
- `DATETIME`
- `TOP`
- `GO`
- `dbo.`
- SQL Server `ISNULL()`

Use Oracle-compatible alternatives.

---

## 2. Core Design Principle

Do not store size, color, and stock directly on the main `PRODUCT` entity.

Bad design:

```text
PRODUCT
- ProductID
- ProductName
- SizeID
- ColorID
- Quantity
- CostPrice
- SalePrice
```

Preferred design:

```text
PRODUCT
    |
    +---- PRODUCT_VARIANT
              |
              +---- SIZE
              |
              +---- COLOR
```

A single clothing product can therefore have many combinations.

Example:

```text
Product: Basic T-Shirt

Small / Black
Medium / Black
Large / Black
Small / White
Medium / White
Large / White
```

Each combination should have its own SKU, price, and stock quantity.

---

## 3. Standard Entity Model

The recommended entities are:

```text
CATEGORY
PRODUCT
CLOTHING_SIZE
COLOR
PRODUCT_VARIANT

SUPPLIER
EMPLOYEE
CUSTOMER

PURCHASE_HEADER
PURCHASE_DETAIL

SALE_HEADER
SALE_DETAIL

PAYMENT

STOCK_MOVEMENT
```

### Relationship model

```text
CATEGORY 1 ─── N PRODUCT

PRODUCT 1 ─── N PRODUCT_VARIANT

CLOTHING_SIZE 1 ─── N PRODUCT_VARIANT

COLOR 1 ─── N PRODUCT_VARIANT

SUPPLIER 1 ─── N PURCHASE_HEADER

EMPLOYEE 1 ─── N PURCHASE_HEADER

PURCHASE_HEADER 1 ─── N PURCHASE_DETAIL

PRODUCT_VARIANT 1 ─── N PURCHASE_DETAIL

CUSTOMER 1 ─── N SALE_HEADER

EMPLOYEE 1 ─── N SALE_HEADER

SALE_HEADER 1 ─── N SALE_DETAIL

PRODUCT_VARIANT 1 ─── N SALE_DETAIL

SALE_HEADER 1 ─── N PAYMENT

PRODUCT_VARIANT 1 ─── N STOCK_MOVEMENT
```

---

## 4. Naming Standard

Use:

- singular table names
- lowercase snake_case
- `_id` for primary and foreign key identifiers
- descriptive names
- consistent naming across all tables

Examples:

```text
product
product_variant
purchase_header
purchase_detail
sale_header
sale_detail
stock_movement
```

Primary key:

```text
product_id
```

Foreign key:

```text
category_id
supplier_id
employee_id
variant_id
```

Avoid inconsistent names such as:

```text
ProductID
productId
PRODUCT_ID
Product_Id
```

Use one naming convention throughout the project.

---

## 5. Primary Key Standard

Every entity must have a primary key.

Recommended Oracle pattern:

```sql
id NUMBER(10)
    GENERATED ALWAYS AS IDENTITY
    CONSTRAINT pk_table_name PRIMARY KEY
```

Example:

```sql
product_id NUMBER(10)
    GENERATED ALWAYS AS IDENTITY
    CONSTRAINT pk_product PRIMARY KEY
```

Do not use business values such as phone numbers, SKU codes, or names as primary keys.

---

## 6. Foreign Key Standard

Foreign keys must explicitly reference the parent table.

Example:

```sql
category_id NUMBER(10) NOT NULL,

CONSTRAINT fk_product_category
    FOREIGN KEY (category_id)
    REFERENCES category(category_id)
```

Use foreign keys to enforce referential integrity.

Do not rely only on application code to maintain relationships.

---

## 7. Product Standard

The `PRODUCT` table stores information that describes the product itself.

It should not store individual size/color stock.

Recommended fields:

```text
product_id
category_id
product_name
brand
description
status
created_at
```

Example:

```sql
CREATE TABLE product (
    product_id NUMBER(10)
        GENERATED ALWAYS AS IDENTITY
        CONSTRAINT pk_product PRIMARY KEY,

    category_id NUMBER(10) NOT NULL,

    product_name VARCHAR2(100) NOT NULL,

    brand VARCHAR2(100),

    description VARCHAR2(255),

    status VARCHAR2(20) DEFAULT 'ACTIVE' NOT NULL,

    created_at TIMESTAMP DEFAULT SYSTIMESTAMP NOT NULL,

    CONSTRAINT fk_product_category
        FOREIGN KEY (category_id)
        REFERENCES category(category_id),

    CONSTRAINT ck_product_status
        CHECK (status IN ('ACTIVE', 'INACTIVE'))
);
```

---

## 8. Product Variant Standard

`PRODUCT_VARIANT` represents a sellable inventory item.

Recommended fields:

```text
variant_id
product_id
size_id
color_id
sku
cost_price
sale_price
quantity
reorder_level
```

A variant should have a unique SKU.

Example:

```sql
CREATE TABLE product_variant (
    variant_id NUMBER(10)
        GENERATED ALWAYS AS IDENTITY
        CONSTRAINT pk_product_variant PRIMARY KEY,

    product_id NUMBER(10) NOT NULL,

    size_id NUMBER(10) NOT NULL,

    color_id NUMBER(10) NOT NULL,

    sku VARCHAR2(50) NOT NULL,

    cost_price NUMBER(12,2) DEFAULT 0 NOT NULL,

    sale_price NUMBER(12,2) DEFAULT 0 NOT NULL,

    quantity NUMBER(10) DEFAULT 0 NOT NULL,

    reorder_level NUMBER(10) DEFAULT 0 NOT NULL,

    CONSTRAINT fk_variant_product
        FOREIGN KEY (product_id)
        REFERENCES product(product_id),

    CONSTRAINT fk_variant_size
        FOREIGN KEY (size_id)
        REFERENCES clothing_size(size_id),

    CONSTRAINT fk_variant_color
        FOREIGN KEY (color_id)
        REFERENCES color(color_id),

    CONSTRAINT uq_variant_sku
        UNIQUE (sku),

    CONSTRAINT uq_product_size_color
        UNIQUE (product_id, size_id, color_id),

    CONSTRAINT ck_variant_cost
        CHECK (cost_price >= 0),

    CONSTRAINT ck_variant_sale
        CHECK (sale_price >= 0),

    CONSTRAINT ck_variant_quantity
        CHECK (quantity >= 0),

    CONSTRAINT ck_variant_reorder
        CHECK (reorder_level >= 0)
);
```

The unique constraint:

```sql
UNIQUE (product_id, size_id, color_id)
```

prevents duplicate variants such as:

```text
Product 1 + Medium + Black
Product 1 + Medium + Black
```

---

## 9. Transaction Header and Detail Standard

Use a header/detail structure for purchases and sales.

### Purchase

```text
PURCHASE_HEADER
    |
    +---- PURCHASE_DETAIL
```

### Sale

```text
SALE_HEADER
    |
    +---- SALE_DETAIL
```

The header stores transaction-level information.

The detail stores individual products/variants.

This is preferred over putting multiple products into one transaction table.

---

## 10. Purchase Standard

Purchase header:

```text
purchase_id
supplier_id
employee_id
purchase_date
total_amount
status
```

Purchase detail:

```text
purchase_detail_id
purchase_id
variant_id
quantity
cost_price
sub_total
```

Do not depend on the current product price when calculating historical purchases.

Store the actual transaction price in:

```text
purchase_detail.cost_price
```

---

## 11. Sale Standard

Sale header:

```text
sale_id
customer_id
employee_id
sale_date
total_amount
discount
grand_total
status
```

Sale detail:

```text
sale_detail_id
sale_id
variant_id
quantity
unit_price
discount
sub_total
```

Store the actual selling price at the time of sale.

Do not calculate historical sales using the current `product_variant.sale_price`.

This preserves transaction history when prices change later.

---

## 12. Customer Standard

A customer may be optional for a walk-in sale.

Therefore:

```sql
customer_id NUMBER(10)
```

can be nullable in `sale_header`.

Do not force creation of a customer record for every walk-in purchase.

---

## 13. Employee Standard

Employees should have unique login usernames.

Do not store plain-text passwords.

Use:

```text
password_hash
```

instead of:

```text
password
```

Application code should perform secure password hashing.

Recommended employee fields:

```text
employee_id
employee_name
gender
phone
email
position
username
password_hash
status
created_at
```

---

## 14. Payment Standard

Payment belongs to a sale.

Recommended fields:

```text
payment_id
sale_id
payment_date
amount
payment_method
payment_status
reference_number
```

Allow multiple payments when required.

Example:

```text
Sale #1001

Cash        $20
ABA         $15
----------------
Total       $35
```

Therefore:

```text
SALE_HEADER 1 ─── N PAYMENT
```

is more flexible than putting only one payment column in the sale table.

---

## 15. Inventory Standard

For a simple academic project, `product_variant.quantity` may store current stock.

For a stronger design, also maintain:

```text
STOCK_MOVEMENT
```

Movement types may include:

```text
PURCHASE
SALE
RETURN_IN
RETURN_OUT
ADJUSTMENT
```

Example:

```text
+50 PURCHASE
-10 SALE
+2 RETURN_IN
-1 ADJUSTMENT
----------------
41 CURRENT STOCK
```

This provides an audit trail.

---

## 16. Constraints

Use constraints wherever a business rule can be enforced by the database.

Examples:

```sql
CHECK (quantity > 0)
CHECK (cost_price >= 0)
CHECK (sale_price >= 0)
CHECK (discount >= 0)
CHECK (status IN ('ACTIVE', 'INACTIVE'))
```

Prefer database constraints over leaving all validation to application code.

---

## 17. NULL Handling

Use `NULL` when a value is genuinely unknown or not applicable.

Examples:

```text
customer_id in sale_header
customer email
supplier email
employee phone
product brand
```

Do not use fake values such as:

```text
''
'N/A'
'Unknown'
0
```

unless the business rule specifically requires those values.

---

## 18. Money Standard

Use:

```sql
NUMBER(12,2)
```

for monetary values.

Examples:

```text
cost_price
sale_price
unit_price
total_amount
discount
grand_total
payment.amount
```

Never use floating-point data types for financial amounts.

---

## 19. Quantity Standard

Use:

```sql
NUMBER(10)
```

for clothing quantities.

Example:

```text
quantity = 25
```

Do not use decimal quantities unless the business actually sells fractional units.

---

## 20. Status Standard

Use controlled values with `CHECK` constraints.

Example:

```sql
status VARCHAR2(20)
```

with:

```sql
CHECK (
    status IN (
        'PENDING',
        'COMPLETED',
        'CANCELLED'
    )
)
```

Do not allow arbitrary status strings such as:

```text
done
Finished
finish
complete
Completed
```

Use one controlled vocabulary.

---

## 21. Historical Data Rule

Transaction tables must preserve the value used when the transaction occurred.

For example:

```text
Current product price = $15

Sale in January = $12
Sale in February = $14
Sale in March = $15
```

`SALE_DETAIL.unit_price` must preserve:

```text
January  → 12
February → 14
March    → 15
```

Do not retrieve historical prices from the current product record.

---

## 22. Delete Rules

Do not casually use:

```sql
ON DELETE CASCADE
```

on important business transactions.

Sales, purchases, payments, and stock history should normally remain preserved.

Prefer:

```text
ACTIVE
INACTIVE
CANCELLED
REFUNDED
```

or another business status instead of physically deleting historical transactions.

---

## 23. Index Standard

Primary keys and unique constraints already provide supporting indexes.

For frequently joined foreign keys, consider indexes such as:

```sql
CREATE INDEX idx_product_category
    ON product(category_id);

CREATE INDEX idx_variant_product
    ON product_variant(product_id);

CREATE INDEX idx_variant_size
    ON product_variant(size_id);

CREATE INDEX idx_variant_color
    ON product_variant(color_id);

CREATE INDEX idx_purchase_supplier
    ON purchase_header(supplier_id);

CREATE INDEX idx_purchase_employee
    ON purchase_header(employee_id);

CREATE INDEX idx_purchase_detail_variant
    ON purchase_detail(variant_id);

CREATE INDEX idx_sale_customer
    ON sale_header(customer_id);

CREATE INDEX idx_sale_employee
    ON sale_header(employee_id);

CREATE INDEX idx_sale_detail_variant
    ON sale_detail(variant_id);

CREATE INDEX idx_payment_sale
    ON payment(sale_id);

CREATE INDEX idx_stock_variant
    ON stock_movement(variant_id);
```

Do not create indexes on every column automatically. Index based on actual lookup, join, filtering, and reporting requirements.

---

## 24. Recommended Table Order

When writing the SQL script, create parent tables before child tables.

Recommended order:

```text
1. category
2. clothing_size
3. color
4. supplier
5. employee
6. customer
7. product
8. product_variant
9. purchase_header
10. purchase_detail
11. sale_header
12. sale_detail
13. payment
14. stock_movement
```

Drop tables in the reverse dependency order when rebuilding the schema.

---

## 25. Standard SQL Generation Rules

When generating SQL for this project:

1. Use Oracle SQL only.
2. Use `CREATE TABLE`.
3. Use `VARCHAR2`, not `VARCHAR`.
4. Use `NUMBER`, not `INT` or SQL Server-specific types.
5. Use `TIMESTAMP` or `DATE` for dates.
6. Use identity columns for generated numeric IDs.
7. Define primary keys explicitly.
8. Define foreign keys explicitly.
9. Add meaningful constraint names.
10. Add `NOT NULL` to required attributes.
11. Add `CHECK` constraints for controlled values.
12. Add `UNIQUE` constraints for business identifiers such as SKU and username.
13. Keep transaction header/detail tables separate.
14. Preserve historical transaction prices.
15. Do not put size/color combinations directly into `product`.
16. Do not store passwords as plain text.
17. Avoid unnecessary duplication.
18. Prefer normalized relational design.
19. Do not use SQL Server syntax in Oracle projects.
20. Keep SQL readable and logically grouped.

---

## 26. Standard Query Style

Use explicit joins.

Preferred:

```sql
SELECT
    p.product_name,
    pv.sku,
    pv.quantity
FROM product p
JOIN product_variant pv
    ON pv.product_id = p.product_id;
```

Avoid old implicit joins such as:

```sql
SELECT *
FROM product p, product_variant pv
WHERE p.product_id = pv.product_id;
```

Explicit `JOIN ... ON` syntax is clearer and preferred for relational queries.

---

## 27. Example Sales Query

```sql
SELECT
    sh.sale_id,
    sh.sale_date,
    c.customer_name,
    e.employee_name,
    sh.grand_total
FROM sale_header sh
LEFT JOIN customer c
    ON c.customer_id = sh.customer_id
JOIN employee e
    ON e.employee_id = sh.employee_id
ORDER BY sh.sale_date DESC;
```

---

## 28. Example Product Inventory Query

```sql
SELECT
    p.product_name,
    pv.sku,
    s.size_name,
    c.color_name,
    pv.quantity,
    pv.sale_price
FROM product p
JOIN product_variant pv
    ON pv.product_id = p.product_id
JOIN clothing_size s
    ON s.size_id = pv.size_id
JOIN color c
    ON c.color_id = pv.color_id
ORDER BY p.product_name, s.size_name, c.color_name;
```

---

## 29. Example Sales Detail Report

```sql
SELECT
    sh.sale_id,
    sh.sale_date,
    p.product_name,
    s.size_name,
    c.color_name,
    sd.quantity,
    sd.unit_price,
    sd.discount,
    sd.sub_total
FROM sale_header sh
JOIN sale_detail sd
    ON sd.sale_id = sh.sale_id
JOIN product_variant pv
    ON pv.variant_id = sd.variant_id
JOIN product p
    ON p.product_id = pv.product_id
JOIN clothing_size s
    ON s.size_id = pv.size_id
JOIN color c
    ON c.color_id = pv.color_id
ORDER BY sh.sale_id DESC;
```

---

## 30. Example Low-Stock Query

```sql
SELECT
    p.product_name,
    pv.sku,
    pv.quantity,
    pv.reorder_level
FROM product p
JOIN product_variant pv
    ON pv.product_id = p.product_id
WHERE pv.quantity <= pv.reorder_level
ORDER BY pv.quantity ASC;
```

---

## 31. Final Recommended Architecture

Use the following as the standard conceptual model:

```text
CATEGORY
   ↓
PRODUCT
   ↓
PRODUCT_VARIANT
   ├── SIZE
   └── COLOR

SUPPLIER
   ↓
PURCHASE_HEADER
   ↓
PURCHASE_DETAIL
   ↓
PRODUCT_VARIANT

CUSTOMER
   ↓
SALE_HEADER
   ↓
SALE_DETAIL
   ↓
PRODUCT_VARIANT

SALE_HEADER
   ↓
PAYMENT

PRODUCT_VARIANT
   ↓
STOCK_MOVEMENT

EMPLOYEE
   ├── PURCHASE_HEADER
   └── SALE_HEADER
```

This structure should be treated as the baseline for future SQL, ERD, normalization, CRUD, reports, stored procedures, triggers, and application development for the Clothing Sales Management System.
