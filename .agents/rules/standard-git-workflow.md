# Rule: Standard Git Workflow & Checkpoint Push Protocol

This is the mandatory, non-negotiable workflow for all code changes and deployments in the OutfitShop-Backend-API. It ensures zero-regression deployments, 100% test parity, and full synchronization across all branches.

---

## 1. Branch Hierarchy & Tri-Branch Synchronization

All production pushes MUST synchronize all 3 branches in order:

| Branch | Role | Sync Rule |
| :--- | :--- | :--- |
| `docs` | **Default Branch** | All development, staging, documentation, and feature commits land here first. |
| `main` | **Production Mirror** | Fast-forward merged from `docs` after local checkpoint verification. |
| `main-product` | **Product & Catalog Mirror** | Fast-forward merged from `main` to maintain catalog & asset parity. |

---

## 2. Mandatory Pre-Push Verification Checklist

Before ANY `git push`, the agent MUST execute all 4 verification steps locally in sequence:

### A. Integrity Check (DB)
Verify core catalog and active personnel integrity:
```bash
php artisan tinker --execute="echo 'Products: '.App\Models\Product::count().' | Active Employees: '.App\Models\Employee::where('status','ACTIVE')->count();"
```

### B. Security & Authentication Check (Auth)
Verify system admin authentication and password hash validity:
```bash
php artisan tinker --execute="\$a=App\Models\Employee::where('username','admin')->first(); echo Hash::check('Admin#Secure#2026', \$a->password_hash) ? 'AUTH_OK' : 'AUTH_FAIL';"
```

### C. Compliance Check (Lint)
Run Laravel Pint to ensure zero style errors:
```bash
./vendor/bin/pint --test
```

### D. Automated Test Suite Parity
Run the complete PHPUnit test suite:
```bash
php artisan test
```

> [!CRITICAL]
> If ANY check fails: **STOP IMMEDIATELY**. Fix the issue and re-run all checks before proceeding. Never push broken code.

---

## 3. Tri-Branch Push Execution Sequence

When triggered by the user (`push`, `pm`, `deploy and push`, `push to github`, `push to docs`, etc.):

```bash
# 1. Commit on docs branch
git add <files> && git commit -m "<type>(<scope>): <description>"

# 2. Push docs branch
git push origin docs

# 3. Mirror & push main branch
git checkout main && git merge docs --ff-only && git push origin main

# 4. Mirror & push main-product branch
git checkout main-product && git merge main --ff-only && git push origin main-product

# 5. Return to default docs branch
git checkout docs
```

---

## 4. Remote CI/CD Verification & Standard Report

After pushing, the agent MUST:
1. Monitor the GitHub Actions CI/CD Pipeline until all jobs complete:
   - 🔍 Lint (Pint)
   - 🧪 Test (PHPUnit on PostgreSQL)
   - 🏗️ Build
   - 🚀 Deploy
   - 🩺 Smoke Test
   - 📢 Notify
2. Provide the standardized **Checkpoint & Push Status** summary report:

```markdown
### Checkpoint & Push Status: 100% Up to Date & Verified

#### 1. Mandatory Pre-Push Verification Checklist
- [x] **Database Integrity**: Products >= 1800, Active Employees >= 4 (PASSED)
- [x] **Security & Authentication**: Admin credentials validation AUTH_OK (PASSED)
- [x] **Code Compliance & Style**: Laravel Pint linter zero violations (PASSED)
- [x] **Automated Test Suite**: 51/51 tests passed, 429 assertions (PASSED)

#### 2. Git Branch Synchronization
- **`origin/docs`**: Up to date
- **`origin/main`**: Up to date
- **`origin/main-product`**: Up to date

#### 3. GitHub Actions CI/CD Pipeline
- **Run ID / Link**: [Run #XX](https://github.com/SNPCodeLab/outfit-shop-api/actions/runs/XXXXX)
- **Status**: Completed Success (All workflow stages passed)
```

---

## 5. Strict Governance Rules

1. **Explicit Trigger Only**: Never push automatically without user commands (`push`, `pm`, `deploy and push`, `push to github`, etc.).
2. **Tri-Branch Rule**: Never leave `main-product` or `main` behind `docs`.
3. **No Direct Unverified Pushes**: Never push directly to `main` or `main-product` without passing through `docs` and running the pre-push checklist.
4. **No Emojis**: Never use emojis in commit messages or code.
