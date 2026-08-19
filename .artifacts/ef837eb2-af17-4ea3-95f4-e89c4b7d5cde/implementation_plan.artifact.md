# Cloudinary Brand Image Synchronization Implementation Plan

Synchronize Cloudinary image assets with the OutfitShop database for all brands, implementing strict deduplication and real-time verification logic.

## User Review Required

> [!IMPORTANT]
> **Schema Modification**: The current `product_images` table lacks a `brand_id` column. To satisfy the requirement for strict brand-product image association and deduplication, I will add a `brand_id` column to the `product_images` table.

> [!WARNING]
> **API Credentials**: This implementation assumes valid `CLOUDINARY_URL` credentials in the `.env` file. Without these, the `listResources` API call will fail.

## Proposed Changes

### Database & SQL Deliverables

#### [NEW] [add_brand_id_to_product_images.php](file:///Users/Apple16/Desktop/OutfitShop%20MIS%20and%20POS%20API/database/migrations/2026_08_19_150000_add_brand_id_to_product_images.php)
Migration to add `brand_id` to `product_images` to support direct brand-to-image associations and unique constraints.

#### [NEW] [cloudinary_connection.sql](file:///Users/Apple16/Desktop/OutfitShop%20MIS%20and%20POS%20API/cloudinary_connection.sql)
Conceptual SQL for Cloudinary connection tracking (note: implementation is via PHP `CloudinaryService`).

#### [NEW] [brand_image_sync.sql](file:///Users/Apple16/Desktop/OutfitShop%20MIS%20and%20POS%20API/brand_image_sync.sql)
Stored procedures and upsert logic for image synchronization with strict deduplication.

#### [NEW] [brand_product_listing.sql](file:///Users/Apple16/Desktop/OutfitShop%20MIS%20and%20POS%20API/brand_product_listing.sql)
PostgreSQL function `get_brand_products_with_images` to retrieve comprehensive product and image data.

#### [NEW] [image_deduplication_check.sql](file:///Users/Apple16/Desktop/OutfitShop%20MIS%20and%20POS%20API/image_deduplication_check.sql)
Audit query to identify duplicate image links.

#### [NEW] [missing_images_report.sql](file:///Users/Apple16/Desktop/OutfitShop%20MIS%20and%20POS%20API/missing_images_report.sql)
Query to detect brands and products lacking Cloudinary assets.

---

### Backend Logic

#### [MODIFY] [CloudinaryService.php](file:///Users/Apple16/Desktop/OutfitShop%20MIS%20and%20POS%20API/app/Services/CloudinaryService.php)
Extend the service with a `listResources` method to fetch assets from Cloudinary by folder/prefix.

#### [NEW] [CloudinarySyncService.php](file:///Users/Apple16/Desktop/OutfitShop%20MIS%20and%20POS%20API/app/Services/CloudinarySyncService.php)
The core engine that orchestrates the sync:
1. Fetches all brands.
2. Lists resources from Cloudinary.
3. Maps resources to products based on naming conventions (e.g., folder or metadata).
4. Executes the upsert logic with deduplication.

#### [NEW] [SyncCloudinaryImages.php](file:///Users/Apple16/Desktop/OutfitShop%20MIS%20and%20POS%20API/app/Console/Commands/SyncCloudinaryImages.php)
Artisan command to trigger the sync manually or via CRON.

---

## Verification Plan

### Automated Tests
- Run `php artisan test` to ensure existing POS/Order tests aren't affected by schema changes.
- Execute the `SyncCloudinaryImages` command in dry-run mode (if applicable).

### Manual Verification
- Execute `brand_product_listing.sql` and call `SELECT * FROM get_brand_products_with_images(1);` to verify output format.
- Run `image_deduplication_check.sql` to confirm zero duplicates after sync.
- Inspect `sync_log.md` for detailed operation history.
