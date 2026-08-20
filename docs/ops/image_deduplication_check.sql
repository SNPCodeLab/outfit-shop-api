-- =============================================================================
-- image_deduplication_check.sql
-- Find duplicate image links for same brand and product
-- =============================================================================

SELECT
    brand_id,
    product_id,
    image_url,
    COUNT(*) AS duplicate_count,
    ARRAY_AGG(image_id) AS duplicate_ids
FROM product_images
GROUP BY brand_id, product_id, image_url
HAVING COUNT(*) > 1;

-- Check for images with same public_id but different URLs (Condition 4)
SELECT
    image_public_id,
    COUNT(*) AS usage_count,
    ARRAY_AGG(image_id) AS image_ids,
    ARRAY_AGG(product_id) AS product_ids
FROM product_images
WHERE image_public_id IS NOT NULL
GROUP BY image_public_id
HAVING COUNT(*) > 1;
