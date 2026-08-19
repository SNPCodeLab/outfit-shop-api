-- =============================================================================
-- cloudinary_connection.sql
-- SS-MIS / OutfitShop Enterprise API
-- =============================================================================
-- This script outlines the configuration and connectivity parameters for
-- Cloudinary. Note that actual connectivity is managed via Laravel's
-- CloudinaryService using the CLOUDINARY_URL environment variable.
-- =============================================================================

-- Verify if Cloudinary metadata storage (product_images) is ready
SELECT
    column_name,
    data_type,
    is_nullable
FROM
    information_schema.columns
WHERE
    table_name = 'product_images'
ORDER BY
    ordinal_position;

-- Check active brands that will be scanned for Cloudinary assets
SELECT
    brand_id,
    brand_name,
    slug,
    is_featured
FROM
    brands
ORDER BY
    brand_name ASC;
