---
name: checkpoint-push-protocol
description: STRICT RULE. Protocol for performing mandatory pre-push health checks and tri-branch sync (docs, main, main-product). Whenever the user says "push", "merge", "deploy", "deploy and push", "pm", or "push to github", this protocol MUST execute first to verify database integrity, authentication validity, code style compliance, and automated test suite parity before synchronizing all 3 branches.
---

# Checkpoint Push Protocol (STRICT RULE)

This protocol must be executed before any `git push` or deployment operation to ensure zero-downtime deployment, 100% green CI/CD pipelines, and parity across all 3 remote branches (`docs`, `main`, `main-product`).

## 1. Mandatory Pre-Push Sequence

When a push or deployment is requested, execute all 4 checks in order:

### A. Integrity Check (DB)
Verify core catalog entities and active personnel:
```bash
php artisan tinker --execute="echo 'Products: '.App\Models\Product::count().' | Active Employees: '.App\Models\Employee::where('status','ACTIVE')->count();"
```

### B. Security Check (Auth)
Verify that standard admin account hashes are valid:
```bash
php artisan tinker --execute="\$a=App\Models\Employee::where('username','admin')->first(); echo Hash::check('Admin#Secure#2026', \$a->password_hash) ? 'AUTH_OK' : 'AUTH_FAIL';"
```

### C. Compliance Check (Lint)
Run Laravel Pint to ensure zero linting errors:
```bash
./vendor/bin/pint --test
```

### D. Test Suite Parity
Run the full PHPUnit automated test suite:
```bash
php artisan test
```

## 2. Decision Logic
- **IF ALL PASS**: Proceed with `git add`, `git commit`, and the Tri-Branch Push sequence.
- **IF ANY FAIL**: **STOP IMMEDIATELY.** Fix the defect and re-verify before pushing.

## 3. Tri-Branch Push & Mirror Sequence
Always synchronize all 3 branches:
```bash
# Push docs branch
git push origin docs

# Fast-forward mirror main and push
git checkout main && git merge docs --ff-only && git push origin main

# Fast-forward mirror main-product and push
git checkout main-product && git merge main --ff-only && git push origin main-product

# Return to default branch
git checkout docs
```

## 4. Post-Push CI/CD Verification
1. Watch/verify the GitHub Actions workflow using `gh run list` or `gh run watch`.
2. Ensure all jobs pass: Lint, Test, Build, Deploy, Smoke Test, Notify.
3. Render the standard **Checkpoint & Push Status** report with all checklist items, branch sync statuses, and run link.
