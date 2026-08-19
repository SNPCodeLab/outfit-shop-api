---
name: procedural-sql-scaling-protocol
description: Advanced protocol for high-volume database catalog scaling using PostgreSQL procedural blocks. Covers array-based batching, regex-based dynamic categorization, and modulo-driven asset distribution to reach 500+ product milestones with 100% integrity.
---

# Procedural SQL Scaling Protocol

Use this protocol when expanding the database catalog by hundreds of items across multiple categories while maintaining perfect referential integrity.

## 1. High-Volume Batching (Array Pattern)
Instead of individual `INSERT` statements, use a procedural block with an array of item names. This reduces script size and ensures atomic execution.

```sql
DO $$
DECLARE
    batch TEXT[] := ARRAY['Item 1', 'Item 2', ...];
    p_name TEXT;
BEGIN
    FOREACH p_name IN ARRAY batch LOOP
        -- Logic here
    END LOOP;
END $$;
```

## 2. Dynamic Categorization
Use `CASE` with `ILIKE` patterns within the loop to automatically assign categories based on naming conventions (e.g., items with "Sneaker" go to *Footwear*).

```sql
SELECT category_id INTO cat_id FROM categories 
WHERE slug = (CASE 
    WHEN p_name ILIKE '%Sneaker%' THEN 'luxury-footwear'
    WHEN p_name ILIKE '%Bag%' THEN 'luxury-handbags'
    ELSE 'general-category'
END);
```

## 3. Modulo-Driven Asset Distribution
When linking images or variants from a finite pool, use the `product_id` and the modulo operator (`%`) to ensure a diverse distribution of assets across the catalog.

```sql
INSERT INTO product_images (product_id, image_url, ...)
VALUES (prod_id, img_pool[(prod_id % array_length(img_pool, 1)) + 1], ...);
```

## 4. Idempotency & Conflict Resolution
- Always use `ON CONFLICT DO NOTHING` for products to allow the script to be resumed.
- Use `RETURNING product_id INTO prod_id` only if the record is new.
- If record exists, use a separate "Healing" query to verify children.

## 5. Milestone Verification
Verify the scale of the expansion:
```sql
SELECT 
    b.brand_name, 
    COUNT(p.product_id) as total_products,
    COUNT(pi.image_id) as total_image_links
FROM brands b
LEFT JOIN products p ON b.brand_id = p.brand_id
LEFT JOIN product_images pi ON p.product_id = pi.product_id
GROUP BY b.brand_name;
```
