# KhmeRiel MIS & POS — Postman Quickstart Guide

This directory contains the ready-to-import Postman files for the **KhmeRiel MIS & POS REST API**:

1. **[`khmeriel_ssmis_postman_collection.json`](file:///Users/Apple16/Desktop/SS_MIS/postman/khmeriel_ssmis_postman_collection.json)**
   - Contains all 4 RBAC levels (Level 1: Public/Guest, Level 2: Cashier/Staff, Level 3: Manager, Level 4: Admin).
   - Pre-configured headers, query parameters, 10% tax calculations, and request bodies.

2. **[`khmeriel_ssmis_postman_environment.json`](file:///Users/Apple16/Desktop/SS_MIS/postman/khmeriel_ssmis_postman_environment.json)**
   - Pre-configured variables: `base_url`, `admin_token`, `manager_token`, `cashier_token`, `staff_token`.

---

## How to Import & Use

1. Open **Postman**.
2. Click **Import** in the top left.
3. Drag & drop both files from [`postman/`](file:///Users/Apple16/Desktop/SS_MIS/postman) into Postman.
4. Select the **KhmeRiel MIS & POS — Environment** in the environment dropdown.
5. Run `Level 1: Public & Guest -> Staff / Admin Login` to automatically receive your Bearer Token.
6. Paste the token into the corresponding token variable (`admin_token`, `manager_token`, etc.) to test all protected endpoints!
