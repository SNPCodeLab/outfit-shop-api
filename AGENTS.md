# AGENTS.md - Authoritative Rules for AI Agents

This file establishes the foundational instructions and operational constraints for any AI Agent working in the **OutfitShop-Backend-API** repository.

---

## 1. Mandatory Pre-Push Verification Checklist

Before running `git push` or merging branches, the agent MUST run and verify all 4 checks locally:

```bash
# 1. Database Catalog & Active Personnel Integrity
php artisan tinker --execute="echo 'Products: '.App\Models\Product::count().' | Active Employees: '.App\Models\Employee::where('status','ACTIVE')->count();"

# 2. Security & Admin Authentication Hash Validation
php artisan tinker --execute="\$a=App\Models\Employee::where('username','admin')->first(); echo Hash::check('Admin#Secure#2026', \$a->password_hash) ? 'AUTH_OK' : 'AUTH_FAIL';"

# 3. Code Style & Lint Compliance
./vendor/bin/pint --test

# 4. Automated PHPUnit Test Parity
php artisan test
```

> **Strict Failure Rule**: If ANY check fails, STOP immediately. Never push failing code.

---

## 2. Tri-Branch Push & Deployment Synchronization

Whenever the user triggers a deployment or push (`push`, `deploy and push`, `pm`, `push to github`, etc.):

1. **Commit changes** to the default branch `docs`.
2. **Push** to `origin/docs`.
3. **Fast-forward merge & push** to `origin/main`.
4. **Fast-forward merge & push** to `origin/main-product`.
5. **Return** to default working branch `docs`.
6. **Verify Remote CI/CD**: Monitor GitHub Actions (`gh run list` / `gh run watch`) until all stages pass.
7. **Report**: Deliver the standardized **Checkpoint & Push Status** summary table.

---

## 3. Strict Coding & Documentation Standards

- **Strict Types**: `declare(strict_types=1);` in every PHP file.
- **No Emojis**: Strictly prohibited in code, comments, UI, documentation, or commit messages.
- **No Direct Shell Edits**: Do not use `sed`/`awk`/`echo` for source code modifications.
- **No Unauthorized Seeding**: Never run `php artisan db:seed` without explicit user permission.
