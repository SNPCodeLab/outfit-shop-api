# Rule: Standard Git Workflow (PM/MP Master Protocol)

This is the mandatory workflow for all code changes in the OutfitShop-Backend-API. It ensures zero-regression deployments and 100% synchronized branches.

## 1. Branch Hierarchy

| Branch | Role |
| :--- | :--- |
| `docs` | **Default Branch**. All development, staging, and feature merges target here. |
| `main` | **Production Mirror**. Only updated via fast-forward from `docs` after verification. |

## 2. Command Shorthand Definitions

The agent MUST ONLY perform Git push operations when the user explicitly uses these shorthand triggers:

| Command | Action Name | Workflow Description |
| :--- | :--- | :--- |
| **`pm`** | **Push Merge** | Full 3-way synchronization: `Local` ──► `origin/docs` ──► `origin/main`. |
| **`mp`** | **Make PR** | Push current branch to `origin` and prepare for a **Pull Request** to `docs`. |

### The `pm` (Push Merge) Sequence:
1.  **Compliance Check**: Run `vendor/bin/pint --test`.
2.  **Logic Check**: Run `php artisan test`.
3.  **Stage & Commit**: `git add <files>` and `git commit -m "..."`.
4.  **Push Docs**: `git push origin docs`.
5.  **Mirror Main**: `git checkout main` ──► `git merge docs --ff-only` ──► `git push origin main`.
6.  **Return**: `git checkout docs`.

### The `mp` (Make PR) Sequence:
1.  **Compliance Check**: Run `vendor/bin/pint --test`.
2.  **Logic Check**: Run `php artisan test`.
3.  **Stage & Commit**: `git add <files>` and `git commit -m "..."`.
4.  **Push Branch**: `git push origin <current-branch>`.
5.  **Notification**: Inform the user that the branch is ready for a Pull Request to `docs`.

## 3. The Double-Checkpoint Rule

Every merge or push must pass two checkpoints:
- **Checkpoint 1 (Local)**: `pint --test` and `php artisan test` must PASS.
- **Checkpoint 2 (Remote)**: Verify GitHub Actions status (if active). If CI fails, the task is NOT done.

## 4. Forbidden Actions

- **Direct Push to `main`**: All changes must go through `docs` first.
- **Auto-Merge**: Never merge without the `pm/mp` command triggers.
- **Emoji Usage**: No emojis in commit messages or code.
- **Shell Edits**: No `sed`/`echo` for file modifications. Use provided IDE tools.
