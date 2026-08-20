-- =============================================================================
-- 04_validation.sql
-- SS-MIS / OutfitShop Enterprise API Post-Fix Validation
-- =============================================================================

-- 1. Check for missing indexes (Expected: 0 records or only neon_auth ones)
SELECT conname, conrelid::regclass AS table_name, a.attname AS column_name, 'HIGH' AS severity, 'Missing index on FK column' AS issue
FROM pg_constraint c
JOIN pg_attribute a ON a.attnum = ANY(c.conkey) AND a.attrelid = c.conrelid
WHERE contype = 'f'
AND NOT EXISTS (SELECT 1 FROM pg_index i WHERE i.indrelid = c.conrelid AND a.attnum = ANY(i.indkey));

-- 2. Verify all constraints are validated
SELECT conname, conrelid::regclass AS table_name, 'MEDIUM' AS severity, 'Constraint is NOT VALIDATED' AS issue
FROM pg_constraint
WHERE contype = 'f' AND convalidated = false;

-- 3. Row count verification (Consistency check)
SELECT 'products' as table_name, count(*) FROM products
UNION ALL
SELECT 'purchase_headers', count(*) FROM purchase_headers
UNION ALL
SELECT 'stock_movements', count(*) FROM stock_movements;
