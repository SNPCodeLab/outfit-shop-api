# OutfitShop API — Product Catalog & Entity Specification Document

<div align="center">
  <img src="https://res.cloudinary.com/od8t271n/image/upload/v1787064621/bleu-SNPCodeLab.png" alt="OutfitShop Primary Logo" width="220" />
</div>

This document provides the complete data structure, entity mindmap, product directory, cart & order relationships, and variant stock matrices for all merchandise in the **OutfitShop Ecommerce Clothing API**.

---

## 1. Product Catalog Entity Mindmap Diagram

```mermaid
flowchart TB
    root["Product Catalog Architecture"]

    subgraph H1["Master Merchandise Headers"]
        PRODUCTS["PRODUCTS<br/>- product_id PK<br/>- product_name<br/>- brand<br/>- category_id FK<br/>- brand_id FK<br/>- product_type<br/>- gender<br/>- material_fabric<br/>- season_collection<br/>- featured_badge<br/>- status"]
        PRODUCT_IMAGES["PRODUCT_IMAGES<br/>- image_id PK<br/>- product_id FK<br/>- image_url<br/>- is_primary<br/>- sort_order"]
    end

    subgraph H2["Taxonomy and Classification"]
        CATEGORIES["CATEGORIES<br/>- category_id PK<br/>- category_name<br/>- department_type<br/>- description"]
        BRANDS["BRANDS<br/>- brand_id PK<br/>- brand_name<br/>- country_of_origin<br/>- description"]
    end

    subgraph H3["Attribute Matrix Definitions"]
        CLOTHING_SIZES["CLOTHING_SIZES<br/>- size_id PK<br/>- size_name<br/>- description"]
        COLORS["COLORS<br/>- color_id PK<br/>- color_name<br/>- description"]
    end

    subgraph H4["Stockable Inventory Units"]
        PRODUCT_VARIANTS["PRODUCT_VARIANTS<br/>- variant_id PK<br/>- product_id FK<br/>- size_id FK<br/>- color_id FK<br/>- sku<br/>- barcode<br/>- cost_price<br/>- sale_price<br/>- wholesale_price<br/>- quantity<br/>- volume_or_weight<br/>- alcohol_by_volume<br/>- download_file_url"]
    end

    subgraph H5["Operational Tracking"]
        STOCK_MOVEMENTS["STOCK_MOVEMENTS<br/>- movement_id PK<br/>- variant_id FK<br/>- movement_type<br/>- quantity"]
        SALE_DETAILS["SALE_DETAILS<br/>- detail_id PK<br/>- variant_id FK<br/>- quantity<br/>- unit_price"]
        PURCHASE_DETAILS["PURCHASE_DETAILS<br/>- detail_id PK<br/>- variant_id FK<br/>- quantity<br/>- cost_price"]
    end

    root --> H1
    root --> H2
    root --> H3
    root --> H4
    root --> H5

    CATEGORIES --> PRODUCTS
    BRANDS --> PRODUCTS
    PRODUCTS --> PRODUCT_VARIANTS
    PRODUCTS --> PRODUCT_IMAGES
    CLOTHING_SIZES --> PRODUCT_VARIANTS
    COLORS --> PRODUCT_VARIANTS

    PRODUCT_VARIANTS --> STOCK_MOVEMENTS
    PRODUCT_VARIANTS --> SALE_DETAILS
    PRODUCT_VARIANTS --> PURCHASE_DETAILS
```

---

## 2. Master Product Directory (10 Products across 5 Brands)

| Product ID | Product Name | Brand Name | Category Name | Product Type | Gender | Material / Fabric | Season / Collection | Featured Badge |
| :---: | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| 29 | Silk Shirt | KhmeRiel Signature | Tops | PHYSICAL_APPAREL | UNISEX | Silk | Autumn/Winter 2026 Signature | LUXURY_HERITAGE |
| 30 | Leather Tote | KhmeRiel Signature | Bags | PHYSICAL_APPAREL | UNISEX | Nappa Leather | Autumn/Winter 2026 Signature | LUXURY_HERITAGE |
| 31 | Classic Polo | Ralph Lauren RLX | Polos | PHYSICAL_APPAREL | MEN | Cotton | Autumn/Winter 2026 Signature | LUXURY_HERITAGE |
| 32 | Golf Polo | Ralph Lauren RLX | Polos | PHYSICAL_APPAREL | MEN | Performance Knit | Autumn/Winter 2026 Signature | LUXURY_HERITAGE |
| 33 | Craft Beer Can | Vattanac Brewery | Beer | PHYSICAL_FMCG | UNISEX | Malt and Hops | Autumn/Winter 2026 Signature | LUXURY_HERITAGE |
| 34 | Craft Beer Bottle | Vattanac Brewery | Beer | PHYSICAL_FMCG | UNISEX | Malt and Hops | Autumn/Winter 2026 Signature | LUXURY_HERITAGE |
| 35 | Cola Can | Coca-Cola Beverages | Beer | PHYSICAL_FMCG | UNISEX | Carbonated | Autumn/Winter 2026 Signature | LUXURY_HERITAGE |
| 36 | Cola Bottle | Coca-Cola Beverages | Beer | PHYSICAL_FMCG | UNISEX | Carbonated | Autumn/Winter 2026 Signature | LUXURY_HERITAGE |
| 37 | Premium Lager Can | Hanuman Premium Beer | Beer | PHYSICAL_FMCG | UNISEX | Malt and Hops | Autumn/Winter 2026 Signature | LUXURY_HERITAGE |
| 38 | Premium Lager Bottle | Hanuman Premium Beer | Beer | PHYSICAL_FMCG | UNISEX | Malt and Hops | Autumn/Winter 2026 Signature | LUXURY_HERITAGE |

---

## 3. Complete Product Variant Directory (All 26 Stock Units)

| Variant ID | Product ID | Product Name | Size | Color | SKU | Barcode | Cost Price (USD) | Sale Price (USD) | Wholesale (USD) | Stock Qty | Weight / Volume | ABV (%) |
| :---: | :---: | :--- | :---: | :---: | :--- | :--- | :---: | :---: | :---: | :---: | :--- | :---: |
| 81 | 29 | Silk Shirt | S | Black | S-S-Black-029 | 885000002901 | 30.00 | 65.00 | 52.00 | 48 | 22 Momme Silk (190g) | 0.00 |
| 82 | 29 | Silk Shirt | M | Black | S-M-Black-029 | 885000002902 | 30.00 | 65.00 | 52.00 | 50 | 22 Momme Silk (190g) | 0.00 |
| 83 | 29 | Silk Shirt | L | Black | S-L-Black-029 | 885000002903 | 30.00 | 65.00 | 52.00 | 50 | 22 Momme Silk (190g) | 0.00 |
| 84 | 29 | Silk Shirt | M | White | S-M-White-029 | 885000002904 | 30.00 | 65.00 | 52.00 | 50 | 22 Momme Silk (190g) | 0.00 |
| 85 | 29 | Silk Shirt | L | White | S-L-White-029 | 885000002905 | 30.00 | 65.00 | 52.00 | 50 | 22 Momme Silk (190g) | 0.00 |
| 86 | 29 | Silk Shirt | M | Gold | S-M-Gold-029 | 885000002906 | 30.00 | 65.00 | 52.00 | 50 | 22 Momme Silk (190g) | 0.00 |
| 87 | 30 | Leather Tote | OS | Black | L-OS-Black-030 | 885000003001 | 60.00 | 130.00 | 104.00 | 49 | Full-Grain Nappa (750g) | 0.00 |
| 88 | 30 | Leather Tote | OS | White | L-OS-White-030 | 885000003002 | 60.00 | 130.00 | 104.00 | 50 | Full-Grain Nappa (750g) | 0.00 |
| 89 | 30 | Leather Tote | OS | Gold | L-OS-Gold-030 | 885000003003 | 60.00 | 130.00 | 104.00 | 50 | Full-Grain Nappa (750g) | 0.00 |
| 90 | 31 | Classic Polo | S | Black | C-S-Black-031 | 885000003101 | 32.00 | 70.00 | 56.00 | 50 | Pima Cotton Pique (210g) | 0.00 |
| 91 | 31 | Classic Polo | M | Black | C-M-Black-031 | 885000003102 | 32.00 | 70.00 | 56.00 | 50 | Pima Cotton Pique (210g) | 0.00 |
| 92 | 31 | Classic Polo | L | Black | C-L-Black-031 | 885000003103 | 32.00 | 70.00 | 56.00 | 50 | Pima Cotton Pique (210g) | 0.00 |
| 93 | 31 | Classic Polo | XL | Black | C-XL-Black-031 | 885000003104 | 32.00 | 70.00 | 56.00 | 50 | Pima Cotton Pique (210g) | 0.00 |
| 94 | 31 | Classic Polo | M | White | C-M-White-031 | 885000003105 | 32.00 | 70.00 | 56.00 | 50 | Pima Cotton Pique (210g) | 0.00 |
| 95 | 31 | Classic Polo | L | White | C-L-White-031 | 885000003106 | 32.00 | 70.00 | 56.00 | 50 | Pima Cotton Pique (210g) | 0.00 |
| 96 | 32 | Golf Polo | S | Black | G-S-Black-032 | 885000003201 | 38.00 | 85.00 | 68.00 | 50 | RLX Performance Knit (185g) | 0.00 |
| 97 | 32 | Golf Polo | M | Black | G-M-Black-032 | 885000003202 | 38.00 | 85.00 | 68.00 | 50 | RLX Performance Knit (185g) | 0.00 |
| 98 | 32 | Golf Polo | L | Black | G-L-Black-032 | 885000003203 | 38.00 | 85.00 | 68.00 | 50 | RLX Performance Knit (185g) | 0.00 |
| 99 | 32 | Golf Polo | M | White | G-M-White-032 | 885000003204 | 38.00 | 85.00 | 68.00 | 50 | RLX Performance Knit (185g) | 0.00 |
| 100 | 32 | Golf Polo | L | White | G-L-White-032 | 885000003205 | 38.00 | 85.00 | 68.00 | 50 | RLX Performance Knit (185g) | 0.00 |
| 101 | 33 | Craft Beer Can | Standard | Standard | C-STD-DEF-033 | 885000003301 | 1.10 | 2.50 | 2.00 | 44 | 330ml Aluminum Can (350g) | 5.00 |
| 102 | 34 | Craft Beer Bottle | Standard | Standard | C-STD-DEF-034 | 885000003401 | 1.30 | 3.00 | 2.40 | 50 | 500ml Glass Bottle (850g) | 5.00 |
| 103 | 35 | Cola Can | Standard | Standard | C-STD-DEF-035 | 885000003501 | 0.50 | 1.25 | 1.00 | 46 | 330ml Aluminum Can (345g) | 0.00 |
| 104 | 36 | Cola Bottle | Standard | Standard | C-STD-DEF-036 | 885000003601 | 0.70 | 1.75 | 1.40 | 50 | 500ml PET Bottle (520g) | 0.00 |
| 105 | 37 | Premium Lager Can | Standard | Standard | P-STD-DEF-037 | 885000003701 | 1.10 | 2.50 | 2.00 | 50 | 330ml Aluminum Can (350g) | 5.00 |
| 106 | 38 | Premium Lager Bottle| Standard | Standard | P-STD-DEF-038 | 885000003801 | 1.30 | 3.00 | 2.40 | 50 | 500ml Glass Bottle (850g) | 5.00 |

---

## 4. Apparel 2D Size by Color Matrix Representation

### 4.1 Product 29: Silk Shirt (KhmeRiel Signature)
- Base Retail Price: 65.00 USD
- Base Cost Price: 30.00 USD

| Size \ Color | Black (#111111) | White (#FFFFFF) | Gold (#D4AF37) |
| :--- | :---: | :---: | :---: |
| Small (S) | SKU: S-S-Black-029 (48 units) | Not Produced | Not Produced |
| Medium (M) | SKU: S-M-Black-029 (50 units) | SKU: S-M-White-029 (50 units) | SKU: S-M-Gold-029 (50 units) |
| Large (L) | SKU: S-L-Black-029 (50 units) | SKU: S-L-White-029 (50 units) | Not Produced |
| Extra Large (XL) | Not Produced | Not Produced | Not Produced |

### 4.2 Product 30: Leather Tote (KhmeRiel Signature)
- Base Retail Price: 130.00 USD
- Base Cost Price: 60.00 USD

| Size \ Color | Black (#111111) | White (#FFFFFF) | Gold (#D4AF37) |
| :--- | :---: | :---: | :---: |
| One Size (OS) | SKU: L-OS-Black-030 (49 units) | SKU: L-OS-White-030 (50 units) | SKU: L-OS-Gold-030 (50 units) |

### 4.3 Product 31: Classic Polo (Ralph Lauren RLX)
- Base Retail Price: 70.00 USD
- Base Cost Price: 32.00 USD

| Size \ Color | Black (#111111) | White (#FFFFFF) | Gold (#D4AF37) |
| :--- | :---: | :---: | :---: |
| Small (S) | SKU: C-S-Black-031 (50 units) | Not Produced | Not Produced |
| Medium (M) | SKU: C-M-Black-031 (50 units) | SKU: C-M-White-031 (50 units) | Not Produced |
| Large (L) | SKU: C-L-Black-031 (50 units) | SKU: C-L-White-031 (50 units) | Not Produced |
| Extra Large (XL) | SKU: C-XL-Black-031 (50 units) | Not Produced | Not Produced |

### 4.4 Product 32: Golf Polo (Ralph Lauren RLX)
- Base Retail Price: 85.00 USD
- Base Cost Price: 38.00 USD

| Size \ Color | Black (#111111) | White (#FFFFFF) | Gold (#D4AF37) |
| :--- | :---: | :---: | :---: |
| Small (S) | SKU: G-S-Black-032 (50 units) | Not Produced | Not Produced |
| Medium (M) | SKU: G-M-Black-032 (50 units) | SKU: G-M-White-032 (50 units) | Not Produced |
| Large (L) | SKU: G-L-Black-032 (50 units) | SKU: G-L-White-032 (50 units) | Not Produced |
| Extra Large (XL) | Not Produced | Not Produced | Not Produced |
