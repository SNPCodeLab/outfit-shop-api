---
name: custom-domain-setup
description: >-
  Domain forwarding, custom domain DNS CNAME/A records setup, Cloudflare Pages/Workers,
  Vercel hosting, and Firebase Authorized Domains configuration workflow.
---

# Custom Domain & Domain Forwarding Setup Skill

This skill provides standard operating procedures for configuring custom domains, subdomains, domain forwarding, DNS records (CNAME/A), and Firebase Authentication authorized domains.

---

## 1. Domain Forwarding (HTTP Redirect)

Domain forwarding automatically redirects visitors accessing one domain or subdomain to another web destination URL.

### Standard Forwarding Configurations
- **Subdomain Forwarding**:
  - **Source**: `api.kesararamwithdigital.tech`
  - **Destination**: `https://kesararamwithdigital.tech/api/v1` (or your deployed Vercel / Cloudflare URL).
- **Forwarding Type**:
  - **301 Permanent Redirect**: Recommended for SEO and permanent address updates.
  - **302 Temporary Redirect**: For maintenance or temporary routing.

### Step-by-Step Registrar Setup (Get.tech / Namecheap / GoDaddy)
1. Log into your domain registrar dashboard.
2. Navigate to **Domain Management** ➔ **Add Domain Forwarding**.
3. Select **Subdomain** (e.g. `api`) or **Domain** (e.g. `@`).
4. Enter your **Destination web address** (e.g., `https://kesararamwithdigital.tech/api/v1` or `https://ss-mis.vercel.app`).
5. Click **Done** / **Save**. *(DNS forwarding propagation takes up to 24 hours).*

---

## 2. Direct Custom Domain DNS Records (CNAME & A Records)

For direct hosting without HTTP redirect, configure DNS records to point your custom domain directly to your host provider.

### Vercel Hosting DNS
- **Subdomain (`api.domain.com` / `app.domain.com`)**:
  - Record Type: `CNAME`
  - Name / Host: `api` (or `app`)
  - Target / Value: `cname.vercel-dns.com`
- **Root Domain (`domain.com`)**:
  - Record Type: `A`
  - Name / Host: `@`
  - Target / Value: `76.76.21.21`

### Cloudflare Pages / Workers DNS
- **Custom Subdomain**:
  - Record Type: `CNAME`
  - Name / Host: `api`
  - Target / Value: `ss-mis.pages.dev` (or your Cloudflare project output).

---

## 3. Firebase Authentication Authorized Domains

> [!IMPORTANT]
> Any new domain or subdomain running your frontend MUST be authorized in Firebase Console, otherwise Firebase Auth & Google Sign-In will block login requests.

### Authorizing Domains Step-by-Step
1. Log into **Firebase Console** with your project owner account (`kesararamwithdigital@gmail.com`).
2. Go to **Authentication** ➔ **Settings** ➔ **Authorized Domains**.
3. Click **Add Domain**.
4. Add your domain entries:
   - `kesararamwithdigital.tech`
   - `api.kesararamwithdigital.tech`
   - `ssmis-ea5df.vercel.app` / `ss-mis.pages.dev`
5. Click **Save**.

---

## 4. Verification Checklist

- [ ] Forwarding or CNAME record added in registrar dashboard.
- [ ] Environment variables (`VITE_FIREBASE_*`) configured in host provider.
- [ ] Domain listed under Firebase Auth Authorized Domains.
- [ ] Tested HTTP redirect and HTTPS SSL certificate status.
