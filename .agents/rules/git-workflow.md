# Git Workflow Rules

This document defines the mandatory Git workflow for the OutfitShop API project.

## Core Branches
- **`docs`**: The primary production and documentation branch (Default branch).
- **`fix`**: The development and bug-fix staging branch.

## Workflow Rules

1. **Development Always in `fix`**: 
   - All code changes, bug fixes, and documentation updates MUST be committed and pushed to the `fix` branch first.
   - **NEVER** push directly to the `docs` branch.

2. **Merging to `docs`**:
   - Merging from `fix` to `docs` is **NOT** automatic.
   - You must only merge `fix` into `docs` when the user explicitly commands it.
   - The merge command should be: `git checkout docs && git merge fix && git push origin docs`.

3. **Status Checks**:
   - Before merging to `docs`, ensure that the `fix` branch has been tested and is stable.

## Summary Checklist
- [ ] Push changes to `fix`.
- [ ] Wait for user approval/command.
- [ ] Merge `fix` into `docs`.
- [ ] Push `docs` to origin.
