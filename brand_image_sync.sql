-- =============================================================================
-- brand_image_sync.sql
-- Strict Deduplication & Upsert Logic for Brand Images
-- =============================================================================

-- TRANSACTION: Sync brand images with strict deduplication
-- This pattern is executed by CloudinarySyncService.

-- 1. Check if image already exists before insertion (Condition 1 & 4)
-- SELECT COUNT(*) FROM product_images
-- WHERE image_url = '[CLOUDINARY_URL]'
-- OR image_public_id = '[PUBLIC_ID]'
-- OR (brand_id = [BRAND_ID] AND product_id = [PRODUCT_ID] AND image_url = '[CLOUDINARY_URL]');

-- 2. Insert image only if it does not exist (Condition 2)
-- INSERT INTO product_images (brand_id, product_id, image_url, image_public_id, created_at, updated_at)
-- SELECT :brand_id, :product_id, :image_url, :public_id, NOW(), NOW()
-- WHERE NOT EXISTS (
--     SELECT 1 FROM product_images
--     WHERE image_url = :image_url
--     AND brand_id = :brand_id
--     AND product_id = :product_id
-- );

-- 3. Update existing image association instead of duplicate insert (Condition 3)
-- Uses the unique constraint uk_brand_product_image_url
-- INSERT INTO product_images (brand_id, product_id, image_url, image_public_id, created_at, updated_at)
-- VALUES (:brand_id, :product_id, :image_url, :public_id, NOW(), NOW())
-- ON CONFLICT (brand_id, product_id, image_url)
-- DO UPDATE SET
--     image_public_id = EXCLUDED.image_public_id,
--     updated_at = NOW();

-- Clean up orphaned images (Optional: Real-time sync cleanup)
-- DELETE FROM product_images
-- WHERE image_public_id NOT IN (:active_public_ids);
