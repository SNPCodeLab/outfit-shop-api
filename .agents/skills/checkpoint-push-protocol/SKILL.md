---
name: checkpoint-push-protocol
description: STRICT RULE. Protocol for performing mandatory pre-push health checks. Whenever the user says "push", "merge", or "deploy", this protocol MUST execute first to verify database integrity, authentication validity, and code style compliance.
---

# Checkpoint Push Protocol (STRICT RULE)

This protocol must be executed before any `git push` or `git merge` operation to ensure zero-downtime deployment and 100% green CI/CD pipelines.

## 1. Mandatory Pre-Push Sequence
When a push/merge is requested, perform these three checks in order:

### A. Integrity Check (DB)
Verify core entities and user access are active.
```bash
php artisan tinker --execute="echo 'Products: '.App\Models\Product::count().' | Active Employees: '.App\Models\Employee::where('status','ACTIVE')->count();"
```

### B. Security Check (Auth)
Verify that standard account hashes are valid and match the documented unique passwords.
```bash
php artisan tinker --execute="\$a=App\Models\Employee::where('username','admin')->first(); echo Hash::check('Admin#Secure#2026', \$a->password_hash) ? 'AUTH_OK' : 'AUTH_FAIL';"
```

### C. Compliance Check (Lint)
Run Laravel Pint to ensure GitHub Actions will pass.
```bash
./vendor/bin/pint --test
```

## 2. Decision Logic
- **IF ALL PASS**: Proceed with `git add`, `git commit`, and `git push`.
- **IF ANY FAIL**: **STOP.** Report the specific failure to the user and fix it before pushing.

## 3. Automation Alias
Whenever the user says "Push", automatically interpret it as:
1. Run Checkpoints.
2. Fix Style (if needed).
3. Pushing to `docs`, `main`, and `main-product`.
