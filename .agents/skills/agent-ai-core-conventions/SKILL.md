---
name: agent-ai-core-conventions
description: >
  Primary Authoritative Skill for AI Agents developing the OutfitShop-Backend-API.
  Defines strict coding standards, documentation rules, tool usage protocols,
  mandatory pre-push verification checklist, and the Tri-Branch (docs, main, main-product) workflow.
---

# Agent-AI Core Development Conventions

This skill defines how AI Agents must operate within the API ecosystem to ensure code integrity, documentation consistency, and zero-regression deployments.

## 1. Core Skill Matrix

When working on this project, the agent SHOULD prioritize loading these authoritative skills:

| Skill | Purpose |
| :--- | :--- |
| `checkpoint-push-protocol` | Mandatory Pre-Push Verification Checklist & Tri-Branch Push (`docs`, `main`, `main-product`). |
| `ssmis-api-master-guide` | Infrastructure, endpoints, and transactional logic. |
| `db-entity-audit-protocol` | Database footprint and catalog richness metrics. |
| `static-test-rbac-protocol` | Strict locked static test accounts policy (no ephemeral staff). |
| `product-catalog-asset-curation` | De-duplication, orientation, renaming, and brand taxonomy protocol for product-items. |
| `laravel-api-production-audit` | Enterprise readiness and gap analysis. |
| `postman-collection-protocol` | Postman synchronization and naming conventions. |

## 2. Mandatory Pre-Push Verification Checklist

Before ANY `git push` or deployment operation, the agent MUST execute all 4 verification steps locally in sequence:

1. **Database Integrity**:
   ```bash
   php artisan tinker --execute="echo 'Products: '.App\Models\Product::count().' | Active Employees: '.App\Models\Employee::where('status','ACTIVE')->count();"
   ```
2. **Security & Auth**:
   ```bash
   php artisan tinker --execute="\$a=App\Models\Employee::where('username','admin')->first(); echo Hash::check('Admin#Secure#2026', \$a->password_hash) ? 'AUTH_OK' : 'AUTH_FAIL';"
   ```
3. **Code Compliance & Style**:
   ```bash
   ./vendor/bin/pint --test
   ```
4. **Automated Test Suite Parity**:
   ```bash
   php artisan test
   ```

*Rule: If any check fails, STOP immediately, resolve the issue, and re-run all checks.*

## 3. Tri-Branch Push Protocol (`docs` ➔ `main` ➔ `main-product`)

Whenever the user triggers a push (`push`, `pm`, `deploy and push`, `push to github`, etc.):
- Synchronize all 3 branches in order: `docs` ➔ `main` ➔ `main-product`.
- Fast-forward merge `docs` into `main`, and `main` into `main-product`.
- Push to `origin/docs`, `origin/main`, and `origin/main-product`.
- Always return to `docs` branch.
- Monitor GitHub Actions until all jobs pass green, then output the standardized **Checkpoint & Push Status** report.

## 4. Strict Coding Standards

- **Formatting**: Always run `vendor/bin/pint` before any commit.
- **Typing**: Use strict types (`declare(strict_types=1);`) and full return type hints.
- **Naming**: `camelCase` for variables/methods, `PascalCase` for classes.
- **No Emojis**: Strictly forbidden in code, comments, UI text, documentation, or git commits.

## 5. Strict Documentation Rules

- **No Quick Start / Install**: Never write clone, composer install, artisan, or setup steps in the README.
- **No Credentials**: Never write test accounts, emails, or passwords in public documentation.
- **No Local Host / IP**: Never write `localhost`, `127.0.0.1`, or local addresses in documentation.
- **No External Links**: Never include markdown links or URLs in the README.
- **Conciseness**: Keep all feature descriptions and architectural summaries as short as possible.

## 6. Documentation & Tooling Protocols

- **Postman**: The master source of truth is `postman/OutfitShop_Master_Collection.json`. Sync to all copy targets after every change.
- **API Response**: Always use `App\Http\Response\ApiResponse` for consistency.
- **Tooling**: NEVER use shell commands (`sed`, `awk`, `echo`) to edit files. Use editing tools.

## 7. Strict Database Seeding Rules (No Unauthorized Seeding)

Refer to `.agents/rules/no-unauthorized-seeding.md`:
- **NEVER** run `php artisan db:seed`, SQL insert scripts, or bulk population routines without explicit user request and approval.
- When asked to "prepare a seeder", only create/edit the seeder code and present the plan. Always ask and wait for user confirmation before executing it against the database.
