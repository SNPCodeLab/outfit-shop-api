# OutfitShop API — Complete Master Postman Collection

This directory contains the **complete, zero-endpoint-skipped** Postman workspace for the **OutfitShop Ecommerce Clothing & POS REST API** with full support for both **Local Development** and **Real Product (Production)** testing.

---

## 🎨 Official Brand Assets

| Asset Type | Resource URL |
| :--- | :--- |
| **Primary Logo** | `https://res.cloudinary.com/od8t271n/image/upload/v1787064621/bleu-SNPCodeLab.png` |
| **Animated Cycle** | `https://res.cloudinary.com/od8t271n/image/upload/v1787062663/default-cycle-SNPCodeLab.gif` |
| **Secondary Logo** | `https://res.cloudinary.com/od8t271n/image/upload/v1787062664/bleu-SNPCodeLab.gif` |
| **Vector Icon** | `https://res.cloudinary.com/od8t271n/image/upload/v1787062662/anime-SNPCodeLab.svg` |
| **Brand Video** | `https://res.cloudinary.com/od8t271n/video/upload/v1787062665/default-cycle-SNPCodeLab.mp4` |

---

## 📁 Files Included

1. **[`production.json`](file:///Users/Apple16/Desktop/SS_MIS/postman/production.json)** *(⭐ Real Product / Production Collection)*
   - Target URL: `https://api.kesararamwithdigital.tech/api/v1`
   - Pre-configured directly for live production testing. 1-click import and test without having to set up environment variables!
   - Contains all endpoints across categories (including Orders, Cart, Wishlist, Invoices, Estimates & Inventory Valuation).

2. **[`local.json`](file:///Users/Apple16/Desktop/SS_MIS/postman/local.json)** *(💻 Local Development Collection)*
   - Target URL: `http://127.0.0.1:8000/api/v1`
   - Pre-configured for local server testing (`php artisan serve`).

3. **[`khmeriel_ssmis_postman_environment_production.json`](file:///Users/Apple16/Desktop/SS_MIS/postman/khmeriel_ssmis_postman_environment_production.json)**
   - Production environment variables.

4. **[`khmeriel_ssmis_postman_environment_local.json`](file:///Users/Apple16/Desktop/SS_MIS/postman/khmeriel_ssmis_postman_environment_local.json)**
   - Local environment variables.

---

## 🚀 How to Import into Postman

1. Open **Postman**.
2. Click **Import** (top left).
3. Drag & drop the JSON collections into Postman.

---

## 🔐 1-Click Authentication Workflow

1. Open folder **`01. System & Authentication`**.
2. Click **`Auth - Login (Superadmin / Admin)`** → Click **Send**.
3. Postman's built-in test script will automatically extract the Bearer token and set:
   - `{{token}}`
   - `{{admin_token}}`
4. Now, every single protected endpoint in all folders will work immediately without manual authorization steps!
