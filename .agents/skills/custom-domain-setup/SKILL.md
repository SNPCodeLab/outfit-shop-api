---
name: custom-domain-setup
description: >-
  Domain forwarding, custom domain DNS CNAME/A records setup, Cloudflare Pages/Workers,
  and Vercel hosting configuration workflow.
---

# Custom Domain & Domain Forwarding Setup Skill

This skill provides standard operating procedures for configuring custom domains, subdomains, domain forwarding, and DNS records (CNAME/A) on Vercel.

---

## 1. Domain Forwarding (HTTP Redirect)

Domain forwarding automatically redirects visitors accessing one domain or subdomain to another web destination URL.

### Standard Forwarding Configurations
- **Subdomain Forwarding**:
  - **Source**: `api.kesararamwithdigital.tech`
  - **Destination**: `https://api.kesararamwithdigital.tech/api/v1`.
- **Forwarding Type**:
  - **301 Permanent Redirect**: Recommended for SEO and permanent address updates.
  - **302 Temporary Redirect**: For maintenance or temporary routing.

---

## 2. Direct Custom Domain DNS Records (CNAME & A Records)

For direct hosting on Vercel, configure DNS records to point your custom domain directly to Vercel servers.

### Vercel Hosting DNS
- **Subdomain (`api.kesararamwithdigital.tech` / `app.kesararamwithdigital.tech`)**:
  - Record Type: `CNAME`
  - Name / Host: `api` (or `app`)
  - Target / Value: `cname.vercel-dns.com`
- **Root Domain (`kesararamwithdigital.tech`)**:
  - Record Type: `A`
  - Name / Host: `@`
  - Target / Value: `76.76.21.21`

---

## 3. Verification Checklist

- [ ] Forwarding or CNAME record added in registrar dashboard.
- [ ] Environment variables configured in Vercel project settings.
- [ ] Custom domain linked in Vercel Dashboard ➔ Settings ➔ Domains.
- [ ] Tested HTTP redirect and HTTPS SSL certificate status.
