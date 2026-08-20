---
name: agent-ai-core-conventions
description: >
  Primary Authoritative Skill for AI Agents developing the OutfitShop-Backend-API.
  Defines strict coding standards, documentation rules, tool usage protocols,
  and the "Double-Checkpoint" git workflow.
---

# 🤖 Agent-AI Core Development Conventions

This skill defines how AI Agents must operate within the **OutfitShop-Backend-API** ecosystem to ensure code integrity, documentation consistency, and zero-regression deployments.

## 1. Strict Coding Standards

- **Formatting**: Always run `vendor/bin/pint` before any commit. If a single file is modified, run `vendor/bin/pint <file>`.
- **Typing**: Use strict types (`declare(strict_types=1);`) in all new PHP files. Include return type hints and property types.
- **Naming**: Use camelCase for methods and variables, PascalCase for classes, and SCREAMING_SNAKE_CASE for machine-readable error codes.
- **No Emojis**: Strictly forbidden in code, comments, UI text, or git commits.

## 2. Documentation Rules

- **Envelopes**: Every API response must use the `App\Http\Response\ApiResponse` factory.
- **Postman**: The master source of truth is `postman/OutfitShop_Master_Collection.json`. Always sync this file to:
    - `API-Delivery-Package/postman_collection.json`
    - `docs/postman_collection.json`
    - `public/SS_MIS.postman_collection.json`
- **MD Formatting**: Use professional, concise Markdown. Prefer tables for reference data.

## 3. Tool Usage Protocol

- **Discovery Phase**: Always bundle `find_files`, `grep`, and `read_file` to understand context before editing.
- **No Shell Edits**: NEVER use `sed`, `awk`, or `echo` to modify files. Use `replace_file_content` or `write_file`.
- **Tinker Verification**: Use `php artisan tinker --execute="..."` to verify database state or logic during the audit phase.

## 4. The Standard Git Workflow (Master Protocol)

Every change must follow the **Double-Checkpoint** sequence:

### Checkpoint 1: Pre-Push (Local)
1. `git status` — Verify a clean working tree.
2. `vendor/bin/pint --test` — Must show **PASS**.
3. `php artisan test` — Ensure core logic is green.
4. `git add <specific files>` — Never use `git add .`.

### Checkpoint 2: The Sync (PM/MP Flow)
When the user says `pm` (Push Merge) or `mp`:
1. Commit: `git commit -m "type: description"`
2. Push to `docs` (Default Branch).
3. Merge `docs` into `main` (Production Mirror).
4. Push `main`.
5. Sync all 5 Postman collection copies.

## 5. Enterprise Identity
The project name is strictly **OutfitShop-Backend-API**. Do not use "Laravel", "Ecommerce API", or "SS-MIS" in public-facing envelopes or primary documentation unless referring to the internal architecture classification.
