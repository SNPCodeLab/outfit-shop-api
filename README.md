# OutfitShop MIS and POS API

**Version:** 1.2.0  
**Status:** Operational  
**Organization:** SNPCodeLab  
**Gateway Domain**: https://api.kesararamwithdigital.tech/api/v1

---

## 1. Project Overview
OutfitShop API is an enterprise-grade backend infrastructure designed for fashion retail management. Built on the Laravel 12 framework, it provides a unified platform for multi-channel product cataloging, persistent shopping cart management, and point-of-sale (POS) operations. The system is specifically engineered for the Cambodian market, implementing high-precision financial auditing and localized tax compliance.

---

## 2. Core Capabilities

### 2.1 Access Control and Security
The system implements a four-tier Role-Based Access Control (RBAC) architecture to ensure data integrity and operational security. Access levels are categorized into Public/Guest, Cashier/Staff, Manager, and Administrator. Authentication is managed through cryptographically hashed tokens and integrated rate limiting to prevent unauthorized access and brute-force attacks.

### 2.2 Transactional Integrity
The POS engine utilizes idempotent request handling and pessimistic row-level locking to manage high-concurrency checkouts. This ensures that inventory levels remain accurate across multiple terminals and prevents duplicate transactions during network instability.

### 2.3 Inventory and Product Logic
A dynamic variant matrix tracks stock across multiple dimensions including size and color. Every stock mutation is recorded in an immutable audit ledger, providing a complete historical trail of stock movements, adjustments, and sales. The system supports real-time SKU tracking and instant barcode resolution.

### 2.4 Financial Auditing
The financial engine performs real-time asset valuation based on cost versus resale metrics. It handles multi-currency transactions (USD and KHR) and implements a specialized 10% tax-exclusive calculation formula. Cash register operations are managed through structured shifts with automated Z-Report generation for daily reconciliation.

---

## 3. Technical Architecture

### 3.1 Technology Stack
- Framework: Laravel 12
- Database: PostgreSQL 17
- Media Management: Cloudinary Edge Service
- API Standard: RESTful with standardized JSON envelopes

### 3.2 Serverless Optimization
The application is hardened for serverless environments. It features a custom bootstrap sequence that handles read-only filesystems by redirecting cache and session storage to database-driven layers and writable temporary paths.

---

## 4. Operational Protocols

### 4.1 Development Workflow
To maintain production stability, work is performed in isolated feature branches. Updates are merged into the staging branch for validation before being synchronized with the production environment.

### 4.2 Deployment Requirements
Proper execution in a production environment requires the configuration of the following parameters:
- Application encryption keys for data protection.
- Secure PostgreSQL connection strings with forced SSL mode.
- Database-backed session and cache drivers for serverless persistence.

---

## 5. System Resources
Comprehensive API specifications are maintained in OpenAPI 3.0 format. A master Postman collection is provided within the repository to facilitate endpoint testing and integration. Entity relationships and master data matrices are defined in the accompanying product documentation files.

---
Copyright 2024–2026 SNPCodeLab. All rights reserved.
