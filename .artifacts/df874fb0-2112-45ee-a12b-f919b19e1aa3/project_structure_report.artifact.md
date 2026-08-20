# Project Structure Report: Production vs. Support

This report categorizes the folders and files in the **OutfitShop SS-MIS** project to clarify what is essential for production and what serves as development support.

## 1. Core Production Folders (DO NOT DELETE)
These folders contain the actual application logic, configuration, and entry points. They must be present for the API to function.

| Folder | Purpose | Requirement |
| :--- | :--- | :--- |
| `app/` | Core logic (Controllers, Models, Services, Middleware). | **Critical** |
| `bootstrap/` | Application startup and service container binding. | **Critical** |
| `config/` | System and environment-specific configuration files. | **Critical** |
| `database/` | Migrations (for schema) and Seeders (for initial data). | **Critical** |
| `public/` | Web entry point (`index.php`) and static assets. | **Critical** |
| `resources/` | Lang files and views (stubs for headless). | **Required** |
| `routes/` | API and Web route definitions. | **Critical** |
| `vendor/` | Third-party PHP dependencies (Composer). | **Critical** |
| `artisan` | CLI tool for management and maintenance. | **Required** |
| `composer.json` | Dependency map. | **Required** |
| `.env` | Environment secrets and settings. | **Critical** |

## 2. Documentation & Support (Non-Production)
These folders are for developers, AI agents, and integration support. They are **not** needed by the runtime in production.

| Folder | Purpose | Recommended Action |
| :--- | :--- | :--- |
| `.agents/` | AI Agent instructions and skills. | **Keep in Repo** (Dev use only) |
| `.artifacts/` | Planning and walkthrough documents. | **Keep in Repo** (Audit trail) |
| `docs/` | Human guides and integration references. | **Keep in Repo** (Onboarding) |
| `postman/` | Master Postman collection source. | **Keep in Repo** (Testing) |
| `API-Delivery-Package/` | Frontend team handoff bundle. | **Keep in Repo** (Integration) |
| `tests/` | Automated test suites (PHPUnit). | **Keep in Repo** (CI/CD) |
| `scripts/` | Automation and migration scripts. | **Keep in Repo** (Maintenance) |
| `sync_log.md` | Development activity logs. | **Optional** |
| `*.md` (Root) | README, ARCHITECTURE, etc. | **Keep in Repo** (Documentation) |

## 3. Deployment Recommendation

> [!IMPORTANT]
> **Should I delete support folders?**
> **No.** Do not delete these folders from the source repository. They are vital for development, integration, and security audits.

### How to handle them in Production:
In a modern deployment (like **Vercel**), you use a `.vercelignore` file to exclude these folders from the deployed environment. This keeps the production artifact lean without losing the source material.

### Already Excluded (verified):
- `tests/`
- `.agents/`
- `.git/`
- `.github/`
- `node_modules/` (if present)
- `scripts/`
- `postman/`

---
Copyright 2026 SNPCodeLab. All rights reserved.
