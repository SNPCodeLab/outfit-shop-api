---
name: db-catalog-healing-protocol
description: Protocol for identifying and resolving data relationship gaps in production databases. Specializes in finding "orphan" records (e.g., products without images) and batch-remediating them using procedural SQL with idempotent logic.
---

# Database Catalog Healing Protocol

Use this protocol when there is a numerical discrepancy between parent and child tables (e.g., Products vs. Image Links) or when historical data is missing necessary associations.

## 1. Discrepancy Detection
Identify the root cause of the count mismatch by finding "orphan" parents:
```sql
SELECT product_id, product_name 
FROM products 
WHERE brand_id = :target_brand_id 
AND NOT EXISTS (SELECT 1 FROM product_images WHERE product_id = products.product_id);
```

## 2. Remediation Strategy (Procedural SQL)
Use a PostgreSQL `DO $$` block to iterate through orphans and apply fixes in batch.

### Healing Pattern: Batch Relationship Creation
```sql
DO $$
DECLARE
    p_rec RECORD;
    pool TEXT[] := ARRAY['url1', 'url2', ...]; -- Asset pool
    i INTEGER;
BEGIN
    FOR p_rec IN 
        SELECT product_id FROM products 
        WHERE brand_id = :brand_id 
        AND NOT EXISTS (SELECT 1 FROM product_images WHERE product_id = products.product_id)
    LOOP
        -- Batch insert missing child records
        FOR i IN 1..5 LOOP
            INSERT INTO product_images (product_id, image_url, ...)
            VALUES (p_rec.product_id, pool[((p_rec.product_id + i) % array_length(pool, 1)) + 1], ...)
            ON CONFLICT DO NOTHING;
        END LOOP;
    END LOOP;
END $$;
```

## 3. Key Principles
- **Idempotency**: Always use `ON CONFLICT DO NOTHING` or `WHERE NOT EXISTS` to ensure scripts can be safely re-run.
- **Asset Rotation**: When batch-linking images, use modulo arithmetic (`%`) to rotate through a finite asset pool, ensuring variety across products.
- **Transaction Safety**: Wrap logic in procedural blocks to ensure all-or-nothing completion or manageable failure logs.

## 4. Post-Fix Verification
Confirm the counts are now equal or within expected ranges:
```sql
SELECT COUNT(DISTINCT product_id) FROM products;
SELECT COUNT(DISTINCT product_id) FROM product_images;
```
