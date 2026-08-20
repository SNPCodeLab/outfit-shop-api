-- =============================================================================
-- missing_images_report.sql
-- Identify brands and products with no associated images
-- =============================================================================

-- 1. Products with no images
SELECT
    p.product_id,
    p.product_name,
    b.brand_name
FROM products p
INNER JOIN brands b ON p.brand_id = b.brand_id
LEFT JOIN product_images pi ON p.product_id = pi.product_id
WHERE pi.image_id IS NULL;

-- 2. Brands with missing Cloudinary images (Condition 7)
SELECT
    b.brand_id,
    b.brand_name,
    COUNT(p.product_id) AS total_products,
    COUNT(DISTINCT pi.product_id) AS products_with_images,
    COUNT(p.product_id) - COUNT(DISTINCT pi.product_id) AS products_missing_images
FROM brands b
LEFT JOIN products p ON b.brand_id = p.brand_id
LEFT JOIN product_images pi ON p.product_id = pi.product_id
GROUP BY b.brand_id, b.brand_name
HAVING COUNT(pi.image_id) = 0 OR COUNT(p.product_id) > COUNT(DISTINCT pi.product_id);
