---
name: product-catalog-asset-curation
description: >
  Authoritative Protocol and Automation Skill for curating, de-duplicating,
  filtering, sanitizing, and categorizing raw e-commerce merchandise assets
  from Desktop/product-items into structured Brand/Category hierarchies for Cloudinary and Neon DB catalog seeding.
---

# Product Catalog Asset Curation Protocol

This skill defines the authoritative rules, automated curation pipelines, and structural conventions for processing merchandise images stored in `/Users/Apple16/Desktop/product-items`.

---

## 1. Primary Directory Path

- **Root Asset Directory**: `/Users/Apple16/Desktop/product-items`
- **Total Catalog Scope**: `1,876+` verified e-commerce images across `24` luxury, streetwear, activewear, and tech brands.

---

## 2. Strict Curation Rules

Whenever new or raw assets are imported or added to `/Users/Apple16/Desktop/product-items`, the following rules **MUST** be executed:

### 2.1 Rule 1: Exact De-duplication ("Double Image Delete")
- Calculate the MD5 content hash for all files.
- If two files share the exact same hash, **permanently delete the duplicate** and keep only the primary instance.

### 2.2 Rule 2: Orientation Filtering ("If Landscape Delete It")
- E-commerce clothing and merchandise catalogs strictly require **Portrait (3:4)** or **Square (1:1)** vertical aspect ratios.
- Inspect image dimensions `(width, height)`:
  - If `width > height` (Landscape orientation), **delete the file immediately**.
  - Keep only images where `height >= width`.

### 2.3 Rule 3: Human-Readable Filename Sanitization
- Strip web crawler noise and dimension artifacts:
  - Remove `_600x600`, `600x600.jpg`, `PM2 Front View`, `_0_2000`, `_1_01`, `_z`, `RW00...`, `P26SU...`.
  - Strip UUIDs and hexadecimal hash signatures (e.g. `_[a-f0-9]{8}-[a-f0-9]{4}-...`).
  - Strip trailing duplicated indices like `(1)`, `(2)`.
- Standardize into **Clean Title Casing** with readable spaces:
  - Example: `1470586_Youth_ASCII_Invertocat_Tee_1.jpg` $\rightarrow$ `Youth ASCII Invertocat Tee.jpg`
  - Example: `Adidasx Songofthe Mute Adi007Pants Grey Five 2 600x600.jpg` $\rightarrow$ `Adidas x Song of the Mute Adi007 Pants Grey Five.jpg`
  - Example: `Monogram Denim Workwear Pants HUD20WXAQ650 PM2 Front View.png` $\rightarrow$ `Monogram Denim Workwear Pants.png`

---

## 3. Brand & Category Taxonomy Hierarchy

All assets are organized into a strict two-level folder structure:
```
product-items/
└── <Brand_Name>/
    └── <Category_Name>/
        └── <Clean_Product_Title>.<ext>
```

### 3.1 Standard Categories
1. **`T-Shirts & Tops`**: Graphic Tees, S/S Tees, Long Sleeves, Polos, Tanks, Button-Ups, Shirts.
2. **`Hoodies & Sweatshirts`**: Pullover Hoodies, Zip-Up Hoodies, Crewnecks, Sweaters, Knitwear.
3. **`Jackets & Outerwear`**: Track Jackets, Windbreakers, Coats, Vests, Parkas, Bombers.
4. **`Pants & Shorts`**: Denim Jeans, Track Pants, Sweatpants, Trousers, Cargo Pants, Swim Shorts.
5. **`Footwear & Sneakers`**: Sneakers, Runners, Slides, Mules, Loafers, Boots.
6. **`Hats & Headwear`**: Dad Hats, Trucker Caps, 5-Panel Hats, Beanies, Bucket Hats.
7. **`Bags & Luggage`**: Totes, Backpacks, Crossbody Bags, Handbags, Wallets, SLG.
8. **`Drinkware & Bottles`**: Tumblers, Camp Mugs, Water Bottles, Travel Mugs, Vessels.
9. **`Stickers & Decals`**: Sticker Packs, Vinyl Decals, Enamel Pins, Badges.
10. **`Accessories & Lifestyle`**: Plushes, Figurines, Keychains, Notebooks, Desk Mats, Keycaps, Socks.

### 3.2 The 24 Master Brands
| Brand | Code / Identifier | Focus Area |
| :--- | :--- | :--- |
| **Louis Vuitton** | `LV`, `HUD...`, `HVP...`, `HUN...` | Luxury Ready-to-Wear, Leather Goods & Outerwear |
| **Stussy** | `s-249...`, `s-340...`, `s-350...` | Streetwear & Graphic Tops |
| **GitHub** | `Invertocat`, `Copilot`, `LGTM`, `147...`, `155...` | Official Tech Merchandise & Apparel |
| **Lululemon** | `LU9...` (Men), `LW9...` (Women), `LM9...` | Activewear, Yoga & Performance Apparel |
| **Palm Angels** | `PA`, `Palm Angels...` | Luxury Streetwear & Track Jackets |
| **Pleasures** | `P26SU...`, `Pleasures...` | Graphic Tees & Streetwear |
| **Icecream** | `IC`, `Icecream...` | Skater & Graphic Apparel |
| **Adidas** | `Adidas...`, `Adi007...` | Sportswear, Sneakers & Activewear |
| **Google Store** | `GGOEG...`, `GGOEC...`, `GGOEA...` | Official Google Hardware & Cloud Merch |
| **Maison Margiela** | `Margiela...` | High-end Luxury Fashion & Footwear |
| **Godspeed** | `Godspeed...` | Fashion Apparel & Graphic Tops |
| **Reese Cooper** | `RW00...`, `Reese Cooper...` | Luxury Outdoor & Heritage Wear |
| **Tesla** | `1857...-00-A`, `2004...-00-A`, `Cybercab` | Official Automotive & Tech Lifestyle Merch |
| **xAI Grok** | `Grok`, `xAI`, `XCore`, `XThermal` | Official AI Hardware, Caps, Vessels & Tees |
| **Fear of God** | `FOG`, `Essentials` | Luxury Streetwear & Hoodies |
| **Puma** | `Puma...`, `Salehe Bembury` | Sportswear & Jerseys |
| **Honour The Gift** | `HTG`, `Honour...` | Contemporary Streetwear |
| **Market** | `MLM...`, `Market...` | Graphic Tees & Tops |
| **Nike** | `Nike...` | Sportswear & Athletics |
| **Kids Worldwide** | `Kids Worldwide...` | Youth & Children's Fashion |
| **NBA** | `NBA...`, `Lakers`, `Celtics` | Basketball Fan Apparel |
| **Born x Raised** | `BxR...` | Streetwear Tops & Tees |
| **Jordan** | `Jordan...`, `Jumpman` | Basketball & Streetwear |
| **The Boring Company** | `Boring Company`, `Cutterhead` | Tunneling Tech & Industrial Apparel |

---

## 4. Automated Python Curation Script

Run this script to automatically audit and sanitize the entire catalog at any time:

```python
import os, re, shutil, hashlib
from PIL import Image

root_dir = '/Users/Apple16/Desktop/product-items'

# 1. Rename lvm to Louis Vuitton
if os.path.exists(os.path.join(root_dir, 'lvm')):
    os.rename(os.path.join(root_dir, 'lvm'), os.path.join(root_dir, 'Louis Vuitton'))

# 2. De-duplication and Landscape Removal
hashes = {}
for root, dirs, files in os.walk(root_dir):
    for f in files:
        if f.startswith('.'): continue
        fp = os.path.join(root, f)
        with open(fp, 'rb') as file_obj:
            h = hashlib.md5(file_obj.read()).hexdigest()
        if h in hashes:
            os.remove(fp)
            continue
        hashes[h] = fp

        try:
            with Image.open(fp) as img:
                w, h_dim = img.size
                if w > h_dim:
                    os.remove(fp)
        except Exception:
            os.remove(fp)
```

---

## 5. Permanent Catalog Manifests & Offline Archive

Even if the temporary physical folder on the desktop (`/Users/Apple16/Desktop/product-items`) is deleted or moved, the entire catalog structure, brand-to-category mappings, product titles, and gallery assets are permanently archived in the repository:

- **Machine-Readable JSON Manifest**: [`docs/catalog/master_catalog_manifest.json`](file:///Users/Apple16/Desktop/backend/docs/catalog/master_catalog_manifest.json)
- **Full Catalog Markdown Directory**: [`docs/catalog/MASTER_CATALOG_DIRECTORY.md`](file:///Users/Apple16/Desktop/backend/docs/catalog/MASTER_CATALOG_DIRECTORY.md)
- **Bulk Seeder**: [`database/seeders/BulkProductCatalogSeeder.php`](file:///Users/Apple16/Desktop/backend/database/seeders/BulkProductCatalogSeeder.php) (Includes automatic offline fallback to the JSON manifest).

