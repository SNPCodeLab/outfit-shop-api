# Security Policy

## Supported Versions

We provide security updates for the following versions:

| Version | Supported |
| --- | --- |
| 1.2.x | ✅ Yes |
| 1.1.x | ❌ No |
| < 1.0 | ❌ No |

---

## Reporting a Vulnerability

If you discover a security vulnerability within the OutfitShop-Backend-API, please send an email to the security team at `security@kesararamwithdigital.tech`. 

**Please do not open a public GitHub issue for security vulnerabilities.**

### Our Process
1. We will acknowledge receipt of your report within 48 hours.
2. We will provide an estimated timeline for a fix.
3. Once the vulnerability is patched, we will issue a new release and give credit to the reporter (if desired).

---

## Security Standards

- **Encryption**: All sensitive data is encrypted at rest using Laravel's encryption services (AES-256-CBC).
- **Authentication**: Managed via cryptographically hashed tokens (Laravel Sanctum).
- **Passwords**: Hashed using Argon2 or Bcrypt with a high cost factor (12+).
- **Rate Limiting**: Enforced on all authentication and write endpoints to prevent brute-force and DoS attacks.
- **Auditing**: Every security-sensitive action (Login, Password Change, Stock Adjustment) is recorded in an immutable audit ledger.

---
Copyright 2024–2026 SNPCodeLab. All rights reserved.
