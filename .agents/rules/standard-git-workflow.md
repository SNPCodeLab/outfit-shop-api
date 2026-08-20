# Rule: Standard Git Workflow (PM/MP Master Protocol)

This is the mandatory workflow for all code changes in the OutfitShop-Backend-API. It ensures zero-regression deployments and 100% synchronized branches.

## 1. Branch Hierarchy

| Branch | Role |
| :--- | :--- |
| `docs` | **Default Branch**. All development, staging, and feature merges target here. |
| `main` | **Production Mirror**. Only updated via fast-forward from `docs` after verification. |

## 2. Command Trigger: pm / mp

The agent MUST ONLY perform a multi-branch sync and push when the user explicitly says:
`pm`, `mp`, `push merge`, or `merge push`.

### The PM/MP Sequence:
1.  **Compliance Check**: Run `vendor/bin/pint --test`. If it fails, run `vendor/bin/pint` and retry.
2.  **Logic Check**: Run `php artisan test`. All tests must pass (100% Green).
3.  **Stage Specifics**: `git add <files>`. Never use `git add .`.
4.  **Commit**: Use Conventional Commits (`feat:`, `fix:`, `docs:`, `refactor:`).
5.  **Push Docs**: `git push origin docs`.
6.  **Mirror Main**:
    - `git checkout main`
    - `git merge docs --ff-only`
    - `git push origin main`
    - `git checkout docs`
7.  **Finalize**: Delete any temporary feature branches.

## 3. The Double-Checkpoint Rule

Every merge or push must pass two checkpoints:
- **Checkpoint 1 (Local)**: `pint --test` and `php artisan test` must PASS.
- **Checkpoint 2 (Remote)**: Verify GitHub Actions status (if active). If CI fails, the task is NOT done.

## 4. Forbidden Actions

- **Direct Push to `main`**: All changes must go through `docs` first.
- **Auto-Merge**: Never merge without the `pm/mp` command triggers.
- **Emoji Usage**: No emojis in commit messages or code.
- **Shell Edits**: No `sed`/`echo` for file modifications. Use provided IDE tools.
