# Agent-AI Blueprint & Core Conventions

This document outlines the authoritative standards and automated protocols established for the **OutfitShop-Backend-API**. It serves as the primary reference for both human developers and AI Agents.

---

## 1. System Identity

- **Primary Name**: `OutfitShop-Backend-API`
- **Classification**: Monolithic Headless REST API (TPS/OLTP + MIS).
- **Core Stack**: Laravel 12 + PostgreSQL 17 + Cloudinary Edge.

---

## 2. Core Agent Skills

AI Agents interacting with this repository are equipped with specialized "Skills" located in `.agents/skills/`. The most critical ones include:

| Skill | Description |
| :--- | :--- |
| `ssmis-api-master-guide` | Master architectural reference and production endpoints. |
| `agent-ai-core-conventions` | Strict coding standards and tool usage protocols. |
| `checkpoint-push-protocol` | Mandatory pre-push health and integrity checks. |
| `db-entity-audit-protocol` | System-wide database discovery and catalog richness metrics. |
| `postman-collection-protocol` | Synchronization rules for the Master Postman Collection. |

---

## 3. Mandatory Development Rules

All modifications must adhere to these strict rules found in `.agents/rules/`:

1.  **No Emojis**: Strictly no emojis in code, UI, or commit messages.
2.  **Standard Git Workflow**: Mandatory use of the `pm` / `mp` (Push Merge) protocol.
3.  **Unified Response Envelope**: Every API must return the `ApiResponse` JSON structure.
4.  **Postman Synchronization**: Any endpoint change must be reflected across all 5 collection copies.

---

## 4. The Standard Git Workflow (PM/MP)

To ensure production stability, the following shorthands are used:
- **`pm` (Push Merge)**: Automates lint check, test pass, push to `docs`, and mirror to `main`.
- **`mp` (Make PR)**: Automates lint/test and pushes current branch to origin for PR review.

---

## 5. Directory Responsibilities

| Directory | Responsibility |
| :--- | :--- |
| `app/Services/` | All business logic, transactions, and "Brains". |
| `app/Http/Response/` | The centralized `ApiResponse` factory. |
| `docs/` | Unified documentation and reference guides. |
| `.agents/` | Automated rules and skills for AI-driven maintenance. |

---
Copyright 2026 SNPCodeLab. All rights reserved.
