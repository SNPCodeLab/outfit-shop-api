-- =============================================================================
-- brand_product_listing.sql
-- Function to list all products by brand ID with complete image details
-- =============================================================================

DROP FUNCTION IF EXISTS get_brand_products_with_images(BIGINT);

CREATE OR REPLACE FUNCTION get_brand_products_with_images(p_brand_id BIGINT)
RETURNS TABLE(
    product_id BIGINT,
    product_name VARCHAR,
    product_description TEXT,
    brand_name VARCHAR,
    image_urls VARCHAR[],
    total_images BIGINT
) AS $$
BEGIN
    RETURN QUERY
    SELECT
        p.product_id,
        p.product_name,
        p.description as product_description,
        b.brand_name,
        COALESCE(ARRAY_AGG(DISTINCT pi.image_url) FILTER (WHERE pi.image_url IS NOT NULL), '{}') AS image_urls,
        COUNT(DISTINCT pi.image_id) AS total_images
    FROM products p
    INNER JOIN brands b ON p.brand_id = b.brand_id
    LEFT JOIN product_images pi ON p.product_id = pi.product_id
    WHERE p.brand_id = p_brand_id
    GROUP BY p.product_id, p.product_name, p.description, b.brand_name;
END;
$$ LANGUAGE plpgsql;

-- Usage Example:
-- SELECT * FROM get_brand_products_with_images(1);
