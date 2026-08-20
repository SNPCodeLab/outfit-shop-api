# OutfitShop-Backend-API

![Version](https://img.shields.io/badge/version-1.2.0-blue)
![Status](https://img.shields.io/badge/status-operational-success)
![Framework](https://img.shields.io/badge/framework-Laravel_12-red)
![License](https://img.shields.io/badge/license-MIT-green)

OutfitShop-Backend-API is an enterprise-grade backend infrastructure designed for fashion retail management. Built on the **Laravel 12** framework, it provides a unified platform for multi-channel product cataloging, persistent shopping cart management, and point-of-sale (POS) operations.

---

## 1. System Classification

The system is architected as a **Monolithic, Headless REST API Backend** implementing a:
- **TPS / OLTP**: Real-time Transaction Processing System for POS checkouts and inventory mutations.
- **MIS Reporting**: Management Information System for dashboard analytics and audit ledgers.

---

## 2. Core Capabilities

- **Access Control (RBAC)**: 4-tier Role-Based Access Control (Public, Staff, Manager, Admin) via Laravel Sanctum and Spatie Permissions.
- **Transactional Integrity**: Idempotent request handling and pessimistic row-level locking for high-concurrency checkouts.
- **Inventory Matrix**: Dynamic variants tracking stock across dimensions (Size $\times$ Color) with immutable audit ledgers.
- **Financial Auditing**: Real-time asset valuation, multi-currency support (USD/KHR), and automated Z-Report generation.
- **Omnichannel Logistics**: Bakong KHQR integration, thermal receipt engine, and sales velocity forecasting.

---

## 3. Technical Architecture

- **Framework**: Laravel 12 (Hardened for Serverless/Vercel)
- **Primary Database**: PostgreSQL 17 (Neon Cloud Managed)
- **Alternative Database**: Oracle SQL (Enterprise on-premise)
- **Media Management**: Cloudinary Edge CDN
- **API Standard**: RESTful JSON with standardized `ApiResponse` envelopes

Detailed documentation is available in the `docs/` folder:
- [Architecture & Responsibilities](docs/ARCHITECTURE.md)
- [API Conventions & Endpoints](docs/API.md)
- [Agent-AI Blueprint & Standards](docs/AGENT_BLUEPRINT.md)
- [Frontend Integration Guide](docs/frontend_integration_guide.md)

---

## 5. AI Agent & Developer Protocol

This repository is hardened for **AI-Driven Development**. Any AI model interacting with this codebase is bound by the rules defined in the `.agents/` directory.

- **Mandatory First Step**: Load the `agent-ai-core-conventions` skill.
- **Strict Rule**: No emojis in any project asset.
- **Workflow**: All changes must follow the **Double-Checkpoint PM/MP Protocol**.

---

## 4. Quick Start

### 4.1 Requirements
- PHP 8.2+ (8.3+ recommended)
- Composer 2
- PostgreSQL 16+ (or SQLite for local testing)

### 4.2 Installation

```bash
# 1. Clone the repository
git clone https://github.com/SNPCodeLab/outfit-shop-api.git
cd "OutfitShop MIS and POS API"

# 2. Install dependencies
composer install

# 3. Setup environment
cp .env.example .env
php artisan key:generate
```

### 4.3 Database Setup

```bash
php artisan migrate
php artisan db:seed              # Seed roles + demo catalog
php artisan serve
```

API root: `http://127.0.0.1:8000/api/v1`

---

## 5. Environment Configuration

| Variable | Purpose | Default / Example |
|---|---|---|
| `APP_KEY` | Encryption key (`php artisan key:generate`) | `base64:...` |
| `DB_CONNECTION` | Database driver | `pgsql` (Primary) or `oracle` |
| `DATABASE_URL` | Neon Database connection string | `postgresql://...` |
| `CACHE_STORE` | Cache driver (use `database` for serverless) | `database` |
| `SESSION_DRIVER` | Session persistence | `database` |
| `CLOUDINARY_URL` | Media CDN credentials | `cloudinary://...` |

---

## 6. Public API Usage

The API is versioned under `/api/v1`. Public endpoints for storefront integration do not require authentication.

### Core Public Endpoints
- `GET /api/v1/health` — System health check.
- `GET /api/v1/products` — Browse product catalog.
- `GET /api/v1/categories` — List categories.
- `GET /api/v1/cart` — Manage session-based shopping cart.

```bash
# Example: Fetch Products
curl -s -X GET https://api.kesararamwithdigital.tech/api/v1/products \
  -H "Accept: application/json"
```

---

## 7. Testing & Quality

We use PHPUnit for feature and unit testing.

```bash
# Run tests
php artisan test

# Run linting (Laravel Pint)
vendor/bin/pint --test
```

---
Copyright 2024–2026 SNPCodeLab. All rights reserved.









