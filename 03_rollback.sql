-- =============================================================================
-- 03_rollback.sql
-- SS-MIS / OutfitShop Enterprise API Relationship Rollback
-- =============================================================================

-- Drop indexes created in 02_migrate_fix.sql
DROP INDEX CONCURRENTLY IF EXISTS idx_products_category_id;
DROP INDEX CONCURRENTLY IF EXISTS idx_purchase_headers_employee_id;
DROP INDEX CONCURRENTLY IF EXISTS idx_stock_movements_employee_id;
DROP INDEX CONCURRENTLY IF EXISTS idx_products_brand_id;
DROP INDEX CONCURRENTLY IF EXISTS idx_product_images_variant_id;
DROP INDEX CONCURRENTLY IF EXISTS idx_bundle_items_bundle_id;
DROP INDEX CONCURRENTLY IF EXISTS idx_bundle_items_variant_id;
DROP INDEX CONCURRENTLY IF EXISTS idx_product_reviews_customer_id;
DROP INDEX CONCURRENTLY IF EXISTS idx_customer_wishlists_variant_id;
DROP INDEX CONCURRENTLY IF EXISTS idx_pos_shifts_branch_id;
DROP INDEX CONCURRENTLY IF EXISTS idx_customer_loyalty_logs_sale_id;
DROP INDEX CONCURRENTLY IF EXISTS idx_gift_cards_purchaser_customer_id;
DROP INDEX CONCURRENTLY IF EXISTS idx_shipping_orders_sale_id;
DROP INDEX CONCURRENTLY IF EXISTS idx_shipping_orders_branch_id;

SELECT 'Database relationship fixes rolled back successfully.' as status;
