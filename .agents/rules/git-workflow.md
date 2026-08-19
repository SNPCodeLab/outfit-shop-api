# Git Workflow Rules

## Branch Structure (Two Branches Only)

| Branch | Role |
|--------|------|
| `docs` | Default branch. Production-ready code. All PRs target here. |
| `main` | Mirror of `docs`. Kept in sync after every merge to `docs`. |

No other branches are permitted. If a feature or fix needs a working branch, create it, use it, then delete it after merging to `docs`.

---

## Workflow: Every Push

Before pushing any code, run this checklist in order:

### 1. Pre-Push Checkpoint Pipeline

```bash
# 1. Ensure you are on the correct branch (never push directly to docs)
git status

# 2. Stage only the files relevant to the change
git add <specific files>

# 3. Run lint check (Pint)
vendor/bin/pint --test

# 4. Confirm no unintended files are staged
git diff --staged --stat

# 5. Commit with a clear conventional message
git commit -m "type: short description"
```

Commit message types: `feat`, `fix`, `refactor`, `docs`, `chore`, `test`

### 2. Push to a Short-Lived Branch

```bash
git push -u origin <branch-name>
```

Never push directly to `docs` or `main`.

### 3. Open a Pull Request to `docs`

All code must enter `docs` via a PR, not a direct push.

PR title: under 70 characters.
PR description must include:
- What changed
- What was tested
- Any known risks

### 4. After PR is Merged to `docs` — Sync `main`

After every merge to `docs`, immediately sync `main`:

```bash
git checkout main
git merge origin/docs --ff-only
git push origin main
```

---

## Shorthand Commands

| You say | What it means |
|---------|---------------|
| `pm` | Push + Merge: push current branch, open PR, merge to `docs`, sync `main` |
| `mp` | Same as `pm` — merge + push to docs and sync main |

When you say `pm` or `mp`, the agent will:
1. Push the current working branch to origin
2. Merge it into `docs` via PR (or direct merge if no CI is blocking)
3. Delete the working branch
4. Sync `main` to match `docs`
5. Delete any leftover local and remote working branches

---

## Forbidden Actions

- Direct push to `docs` or `main`
- Leaving stale branches alive after merge
- Force push (`--force`) on `docs` or `main`
- Merging `main` into `docs` (the direction is always: working branch -> `docs` -> `main`)
- Creating branches named `fix`, `main-product`, `dev`, or other permanent parallel branches

---

## Quick Reference

```
Working Branch  -->  PR  -->  docs  -->  main (ff-only sync)
```

Default branch for all PRs: `docs`
Production deploy branch: `docs`
Mirror branch: `main`
