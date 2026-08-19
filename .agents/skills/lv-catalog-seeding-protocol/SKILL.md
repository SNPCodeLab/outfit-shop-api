---
name: lv-catalog-seeding-protocol
description: High-volume luxury brand catalog seeding protocol. Handles idempotent insertion of brands, categories, and products, with automated high-resolution image mapping from Cloudinary assets.
---

# Luxury Catalog Seeding Protocol

Use this protocol to populate the SS-MIS database with authentic luxury brand data while maintaining referential integrity and preventing duplicates.

## 1. Idempotent Data Structure
Always wrap high-volume inserts in a `DO $$` PostgreSQL block to allow for variable capturing (IDs) and complex conditional logic.

### Brand & Category Setup
```sql
INSERT INTO brands (brand_name, slug, ...) VALUES (...) ON CONFLICT (slug) DO NOTHING;
INSERT INTO categories (category_name, slug, ...) VALUES (...) ON CONFLICT (category_name) DO NOTHING;
```

## 2. Product-Image Linking
Each product should be linked to at least 5 unique images to provide a rich frontend experience.

```sql
INSERT INTO products (...) VALUES (...) ON CONFLICT DO NOTHING RETURNING product_id INTO prod_id;

IF prod_id IS NOT NULL THEN
    INSERT INTO product_images (brand_id, product_id, image_url, ...)
    VALUES (lv_id, prod_id, 'https://res.cloudinary.com/...', ...);
END IF;
```

## 3. Deduplication Logic
- **Products**: Use `ON CONFLICT (brand_id, product_name)` or check existence via subquery.
- **Images**: Use `ON CONFLICT (brand_id, product_id, image_url)` to prevent duplicate links.

## 4. Verification
After seeding, verify totals:
```sql
SELECT b.brand_name, c.category_name, COUNT(p.product_id)
FROM products p
JOIN brands b ON p.brand_id = b.brand_id
JOIN categories c ON p.category_id = c.category_id
GROUP BY b.brand_name, c.category_name;
```
