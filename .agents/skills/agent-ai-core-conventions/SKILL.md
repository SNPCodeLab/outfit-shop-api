---
name: agent-ai-core-conventions
description: >
  Primary Authoritative Skill for AI Agents developing the OutfitShop-Backend-API.
  Defines strict coding standards, documentation rules, tool usage protocols,
  and the "Double-Checkpoint" git workflow.
---

# Agent-AI Core Development Conventions

This skill defines how AI Agents must operate within the API ecosystem to ensure code integrity, documentation consistency, and zero-regression deployments.

## 1. Core Skill Matrix

When working on this project, the agent SHOULD prioritize loading these authoritative skills:

| Skill | Purpose |
| :--- | :--- |
| `ssmis-api-master-guide` | Infrastructure, endpoints, and transactional logic. |
| `checkpoint-push-protocol` | Pre-push integrity and health checks. |
| `db-entity-audit-protocol` | Database footprint and catalog richness metrics. |
| `laravel-api-production-audit` | Enterprise readiness and gap analysis. |
| `postman-collection-protocol` | Postman synchronization and naming conventions. |

## 2. Strict Coding Standards

- **Formatting**: Always run `vendor/bin/pint` before any commit.
- **Typing**: Use strict types (`declare(strict_types=1);`) and full return type hints.
- **Naming**: `camelCase` for variables/methods, `PascalCase` for classes.
- **No Emojis**: Forbidden in code, comments, UI text, documentation, or git commits.

## 3. Strict Documentation Rules

- **No Quick Start / Install**: Never write clone, composer install, artisan, or setup steps in the README.
- **No Credentials**: Never write test accounts, emails, or passwords in public documentation.
- **No Local Host / IP**: Never write `localhost`, `127.0.0.1`, or local addresses in documentation.
- **No External Links**: Never include markdown links or URLs in the README.
- **Conciseness**: Keep all feature descriptions and architectural summaries as short as possible.

## 4. Documentation & Tooling Protocols

- **Postman**: The master source of truth is `postman/OutfitShop_Master_Collection.json`. Sync to all 4 copy targets after every change.
- **API Response**: Always use `App\Http\Response\ApiResponse` for consistency.
- **Tooling**: NEVER use shell commands (`sed`, `awk`, `echo`) to edit files. Use editing tools.

## 5. Master Git Protocols (pm / mp)

Refer to `.agents/rules/standard-git-workflow.md` for shorthand definitions:
- **`pm`**: Push current changes to `docs` and mirror to `main`.
- **`mp`**: Push current branch to origin for a Pull Request to `docs`.
