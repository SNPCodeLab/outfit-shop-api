# Contributing Guidelines

Thank you for your interest in contributing to the OutfitShop-Backend-API project. To maintain high code quality and consistency, please follow these guidelines.

---

## 1. Development Workflow

### Branching Strategy
We use a strict **Branch Isolation** strategy:
- `dev`: The primary development branch. All feature branches and bug fixes must target `dev`.
- `main`: The production-ready branch. Merges from `dev` to `main` are restricted and occurring only during release cycles.

**Rule**: NEVER push directly to `main`. Work exclusively on `dev` or feature-specific branches.

### Linting & Formatting
We use **Laravel Pint** for code style enforcement.
- Before committing, run: `composer run lint` or `vendor/bin/pint --test`.
- Commits that fail the linting check will be rejected by CI.

---

## 2. Commit Message Conventions

We follow a simplified conventional commit format:
- `feat: ...` for new features.
- `fix: ...` for bug fixes.
- `docs: ...` for documentation changes.
- `refactor: ...` for code refactoring.
- `test: ...` for adding or updating tests.

---

## 3. Pull Request Process

1. Fork the repository and create your branch from `dev`.
2. Ensure your code passes all tests: `php artisan test`.
3. Ensure your code is linted: `vendor/bin/pint`.
4. Submit a Pull Request targeting the `dev` branch.
5. Provide a clear description of the changes and any relevant issue numbers.

---

## 4. Code Standards

- **No Emojis**: Do not use emojis in code comments, UI text, or commit messages.
- **RESTful**: Follow the established REST conventions in `docs/API.md`.
- **Type Hinting**: Always use strict typing (`declare(strict_types=1);`) and return type hints in new classes.
- **Service Pattern**: Move complex business logic into Services rather than bloating Controllers.

---
Copyright 2024–2026 SNPCodeLab. All rights reserved.
