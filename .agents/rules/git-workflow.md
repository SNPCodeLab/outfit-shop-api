# Git Workflow Rules

## Branch Structure (Two Branches Only)

| Branch | Role |
|--------|------|
| `docs` | Default branch. Production-ready code. All merges and PRs target here. Triggers production deploy. |
| `main` | Mirror of `docs`. Synced after every merge to `docs`. |

No other long-lived branches are permitted. Short-lived working branches are created for features/fixes, then deleted after merging.

---

## Command Shorthand — Primary Reference

| You say | What the agent does |
|---------|---------------------|
| `merge` | Merge current branch into `docs`, sync `main`, push both — double checkpoint required |
| `push` | Open a PR targeting `docs` (no merge yet) |
| `merge push` | Merge + sync + push both + production deploy fires — double checkpoint required |

---

## Double Checkpoint — Mandatory Before Every Push to `docs` or `main`

Every merge or push goes through TWO checkpoints. If either fails, **do not push**. Fix the issue first.

### Checkpoint 1 — Local (Pre-Push)

Run locally before staging the commit:

```bash
# 1. Confirm branch and clean working tree
git status

# 2. Stage only specific relevant files (never git add .)
git add <specific files>

# 3. Run Pint lint — MUST PASS before continuing
vendor/bin/pint --test

# If lint fails: fix with vendor/bin/pint <file>, then re-run --test
# Only proceed when output shows: PASS — X files

# 4. Review staged diff
git diff --staged --stat

# 5. Commit with conventional message
git commit -m "type: short description"
```

**If `vendor/bin/pint --test` fails → run `vendor/bin/pint` to auto-fix → re-run `--test` → must show PASS before any push.**

### Checkpoint 2 — GitHub Actions (Post-Push)

After pushing to `docs` or `main`, verify the CI/CD pipeline passes on GitHub:

| Stage | Check |
|-------|-------|
| Lint (Pint & Code Style) | Must be green |
| Test (PHPUnit / Pest) | Must be green |
| Build (Production Artifact) | Must be green |
| Deploy (Webhook trigger) | Must be green |
| Smoke Test (Live health check) | Must be green |

**If any GitHub Actions stage fails → do not declare the task done. Investigate, fix, re-run the full double checkpoint, and push again.**

The pipeline runs on every push to `docs` and `main` via `.github/workflows/deploy.yml`.

---

## Merge Flow

```
working branch
     |
     v
[ Checkpoint 1: vendor/bin/pint --test → PASS ]
     |
     v
git merge -> docs (--no-ff)
git push origin docs
     |
     v
[ Checkpoint 2: GitHub Actions pipeline → ALL GREEN ]
     |
     v
git checkout main
git merge origin/docs --ff-only
git push origin main
     |
     v
DONE
```

---

## Full Command Sequences

### `merge`

```bash
vendor/bin/pint --test                          # Checkpoint 1 — must PASS
git checkout docs
git merge <branch> --no-ff -m "merge: <branch> -> docs — <description>"
git push origin docs                            # triggers GitHub Actions
# wait for Checkpoint 2 — all stages must be green
git checkout main
git merge origin/docs --ff-only
git push origin main
git checkout docs
```

### `push` (PR only)

```bash
vendor/bin/pint --test                          # Checkpoint 1 — must PASS
git push -u origin <branch>
# open PR targeting docs
```

### `merge push` (full release)

```bash
vendor/bin/pint --test                          # Checkpoint 1 — must PASS
git checkout docs
git merge <branch> --no-ff -m "merge: <branch> -> docs — <description>"
git push origin docs                            # triggers GitHub Actions deploy
# wait for Checkpoint 2 — all stages must be green
git checkout main
git merge origin/docs --ff-only
git push origin main
git checkout docs
# delete working branch (local + remote)
```

---

## Forbidden Actions

- Pushing to `docs` or `main` when Checkpoint 1 (Pint) is failing
- Declaring a task done when Checkpoint 2 (GitHub Actions) is failing
- Direct push to `docs` or `main` that bypasses the checkpoint pipeline
- Force push (`--force`) on `docs` or `main`
- Merging `main` into `docs` (direction is always: working branch -> `docs` -> `main`)
- Using `git add .` — always stage specific files
- Leaving stale working branches alive after merge
