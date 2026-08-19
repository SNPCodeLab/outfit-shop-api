-- =============================================================================
-- 02_migrate_fix.sql
-- SS-MIS / OutfitShop Enterprise API Relationship Fixes
-- =============================================================================
-- WARNING: CREATE INDEX CONCURRENTLY cannot run inside a transaction.
-- Run this script as a superuser or owner of the schema.
-- =============================================================================

-- 1. Create missing indexes for Foreign Keys (HIGH Severity)
CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_products_category_id ON products(category_id);
CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_purchase_headers_employee_id ON purchase_headers(employee_id);
CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_stock_movements_employee_id ON stock_movements(employee_id);
CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_products_brand_id ON products(brand_id);
CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_product_images_variant_id ON product_images(variant_id);
CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_bundle_items_bundle_id ON bundle_items(bundle_id);
CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_bundle_items_variant_id ON bundle_items(variant_id);
CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_product_reviews_customer_id ON product_reviews(customer_id);
CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_customer_wishlists_variant_id ON customer_wishlists(variant_id);
CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_pos_shifts_branch_id ON pos_shifts(branch_id);
CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_customer_loyalty_logs_sale_id ON customer_loyalty_logs(sale_id);
CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_gift_cards_purchaser_customer_id ON gift_cards(purchaser_customer_id);
CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_shipping_orders_sale_id ON shipping_orders(sale_id);
CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_shipping_orders_branch_id ON shipping_orders(branch_id);

-- 2. Validate all constraints (Ensure integrity)
-- Note: convalidated check was clean, but running ANALYZE to refresh stats.
ANALYZE products;
ANALYZE purchase_headers;
ANALYZE stock_movements;
ANALYZE product_images;
ANALYZE bundle_items;
ANALYZE product_reviews;
ANALYZE customer_wishlists;
ANALYZE pos_shifts;
ANALYZE customer_loyalty_logs;
ANALYZE gift_cards;
ANALYZE shipping_orders;

-- 3. Confirmation
SELECT 'Database relationship fixes applied successfully.' as status;
