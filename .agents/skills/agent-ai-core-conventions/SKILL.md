---
name: agent-ai-core-conventions
description: >
  Primary Authoritative Skill for AI Agents developing the OutfitShop-Backend-API.
  Defines strict coding standards, documentation rules, tool usage protocols,
  and the "Double-Checkpoint" git workflow.
---

# 🤖 Agent-AI Core Development Conventions

This skill defines how AI Agents must operate within the **OutfitShop-Backend-API** ecosystem to ensure code integrity, documentation consistency, and zero-regression deployments.

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
- **No Emojis**: Forbidden in code, comments, UI text, or git commits.

## 3. Documentation & Tooling Protocols

- **Postman**: The master source of truth is `postman/OutfitShop_Master_Collection.json`. Sync to all 4 copy targets after every change.
- **API Response**: Always use `App\Http\Response\ApiResponse` for consistency.
- **Tooling**: NEVER use shell commands (`sed`, `awk`, `echo`) to edit files. Use `replace_file_content`.

## 4. Master Git Protocol (PM/MP)

Refer to `.agents/rules/standard-git-workflow.md` for the mandatory multi-branch synchronization process triggered by `pm` or `mp`.

## 5. Enterprise Identity
The project name is strictly **OutfitShop-Backend-API**. Refer to the system as a **Monolithic Headless TPS/OLTP REST API**.
