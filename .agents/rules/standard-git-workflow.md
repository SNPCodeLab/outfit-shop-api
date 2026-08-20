# Rule: Standard Git Workflow (PM/MP Protocol)

This is the mandatory workflow for all code changes in the OutfitShop-Backend-API.

## 1. Branch Hierarchy
- `docs`: The default, development, and staging branch. All work starts and is pushed here first.
- `main`: The production mirror. Only updated after `docs` is verified and pushed.

## 2. Command Trigger: pm / mp
Whenever the user says `pm` or `mp` (Push Merge):

1. **Verify Linting**: Run `vendor/bin/pint --test`. If it fails, fix it and re-run.
2. **Verify Tests**: Run `php artisan test`.
3. **Stage Specifics**: `git add` only the modified files.
4. **Commit**: Use Conventional Commits (e.g., `feat: ...`, `fix: ...`).
5. **Push Docs**: `git push origin docs`.
6. **Sync Main**:
   - `git checkout main`
   - `git merge docs --ff-only`
   - `git push origin main`
   - `git checkout docs`
7. **Cleanup**: Delete any short-lived feature branches.

## 3. Forbidden Actions
- Pushing directly to `main`.
- Pushing without running `pint --test`.
- Using `git add .` to include vendor or temp files.
- Including emojis in commit messages.
