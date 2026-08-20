# Git Workflow Rules

## Branch Structure (Two Branches Only)

| Branch | Role |
|--------|------|
| `docs` | Default branch. Production-ready code. All merges and PRs target here. Triggers production deploy. |
| `main` | Mirror of `docs`. Synced after every merge to `docs`. |

No other long-lived branches are permitted. Short-lived working branches are created for features/fixes, then deleted after merging.

---

## Command Shorthand — Primary Reference

These are the canonical commands you use. The agent follows them exactly.

| You say | What the agent does |
|---------|---------------------|
| `merge` | Merge current branch into `docs`, sync `main`, push both, run checkpoint pipeline |
| `push` | Open a PR targeting `docs` (no merge yet) |
| `merge push` | Merge into `docs` + sync `main` + push both + trigger production deploy via checkpoint pipeline |

### `merge` — Default Merge to `docs`

When you say **merge**, the agent will:
1. Run the pre-push checkpoint pipeline (lint, stage check)
2. Merge the current branch into `docs` (no-ff commit)
3. Fast-forward sync `main` to match `docs`
4. Push `docs` and `main` to origin
5. Delete the working branch (local + remote) if applicable

```bash
git checkout docs
git merge <branch> --no-ff -m "merge: <branch> -> docs — <description>"
git push origin docs
git checkout main
git merge origin/docs --ff-only
git push origin main
```

### `push` — Open PR to `docs`

When you say **push** (without merge), the agent will:
1. Push the current working branch to origin
2. Open a Pull Request targeting `docs`
3. No merge is performed — waits for review

```bash
git push -u origin <branch>
# then open PR to docs
```

### `merge push` — Full Release to Production

When you say **merge push**, the agent will:
1. Run the full pre-push checkpoint pipeline
2. Merge current branch into `docs`
3. Sync `main` to `docs` (ff-only)
4. Push `docs` to origin — this triggers the GitHub Actions deploy pipeline
5. Push `main` to origin
6. Delete the working branch (local + remote)
7. Confirm the GitHub Actions deploy pipeline fired (smoke test passes)

This is the production release command. Every `merge push` must pass the checkpoint pipeline before touching `docs`.

---

## Pre-Push Checkpoint Pipeline

Run before every merge or push. No skipping.

```bash
# 1. Confirm branch and clean working tree
git status

# 2. Stage only relevant files (never git add .)
git add <specific files>

# 3. Lint check
vendor/bin/pint --test

# 4. Review staged diff
git diff --staged --stat

# 5. Commit with conventional message
git commit -m "type: short description"
```

Commit message types: `feat`, `fix`, `refactor`, `docs`, `chore`, `test`

---

## GitHub Actions Deploy Pipeline

Every push to `docs` or `main` triggers the CI/CD pipeline in `.github/workflows/deploy.yml`:

| Stage | What runs |
|-------|-----------|
| 1. Lint | Laravel Pint code style check |
| 2. Test | PHPUnit / Pest test suite against PostgreSQL |
| 3. Build | Production artifact, config + route cache |
| 4. Deploy | Webhook trigger to production server |
| 5. Smoke Test | Live health check on `https://api.kesararamwithdigital.tech/api/v1/health` |
| 6. Notify | Slack / Teams status alert |

After every `merge` or `merge push`, verify the pipeline passed before calling the task done.

---

## Branch Flow

```
working branch  -->  docs (merge/merge push)  -->  main (ff-only sync)
                          |
                          +---> GitHub Actions --> Production Deploy
```

Default PR target: `docs`
Production deploy branch: `docs`
Mirror branch: `main`

---

## Forbidden Actions

- Direct push to `docs` or `main` without going through the checkpoint pipeline
- Force push (`--force`) on `docs` or `main`
- Merging `main` into `docs` (direction is always: working branch -> `docs` -> `main`)
- Leaving stale working branches alive after merge
- Using `git add .` — always stage specific files
- Skipping lint before merge
