<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="DEVELOPER API Reference. Clean Lineless Bento REST API documentation with Liquid Glass buttons and AI frontend prompt generator.">
<title>DEVELOPER API Reference — REST API Specification</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=JetBrains+Mono:ital,wght@0,400;0,500;0,600;1,400&display=swap" rel="stylesheet">
<style>
/* ==========================================================================
   DEVELOPER API — LINELESS BENTO SPEC WITH LIQUID GLASS BUTTONS
   Design: Lineless Clean White Surfaces | Sharp Neo-Brutalist Block Shapes (4px)
   Buttons: Single-Color Liquid Glass (#635BFF & Glass Translucent)
   Rules: box-shadow: none | no hover styles | no animation | zero emojis
   ========================================================================== */
:root {
  --c-white: #ffffff;
  --c-subtle: #f8fafc;
  --c-ink: #0f172a;
  --c-accent: #635bff;
  --c-accent-hover: #5046e5;
  --c-accent-glass: rgba(99, 91, 255, 0.12);

  --bg-main: #ffffff;
  --bg-subtle: #f8fafc;
  --bg-card: #ffffff;
  --bg-code: #0b1120;
  --bg-code-text: #f1f5f9;
  --sidebar-bg: #fbfcfd;
  --topbar-bg: #ffffff;

  --border-divider: rgba(15, 23, 42, 0.06);

  --text-primary: var(--c-ink);
  --text-secondary: #334155;
  --text-muted: #64748b;
  --text-faint: #94a3b8;

  --color-get: #0284c7;
  --color-get-bg: rgba(2, 132, 199, 0.08);
  --color-post: #059669;
  --color-post-bg: rgba(5, 150, 105, 0.08);
  --color-put: #d97706;
  --color-put-bg: rgba(217, 119, 6, 0.08);
  --color-del: #e11d48;
  --color-del-bg: rgba(225, 29, 72, 0.08);

  --font-sans: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
  --font-mono: 'JetBrains Mono', monospace;
}

/* STRICT RESET */
* {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
  box-shadow: none !important;
  text-shadow: none !important;
}

@keyframes copyPulse {
  0% { transform: scale(1); }
  50% { transform: scale(1.1); }
  100% { transform: scale(1); }
}

.btn-copied, button.btn-copied, .action-link.btn-copied, .copy-code-btn.btn-copied {
  background: #059669 !important;
  color: #ffffff !important;
  border-color: #059669 !important;
  animation: copyPulse 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275) !important;
}

html {
  scroll-behavior: auto;
  font-size: 14px;
  background: var(--bg-main);
  color: var(--text-secondary);
}

body {
  font-family: var(--font-sans);
  background: var(--bg-main);
  color: var(--text-secondary);
  line-height: 1.6;
  -webkit-font-smoothing: antialiased;
}

/* SCROLLBARS */
::-webkit-scrollbar { width: 5px; height: 5px; }
::-webkit-scrollbar-track { background: var(--bg-subtle); }
::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }

/* TYPOGRAPHY & LINKS */
a { color: var(--c-accent); text-decoration: none; font-weight: 600; }
code, pre, kbd { font-family: var(--font-mono); font-size: 12px; }

/* INLINE CODE ONLY */
:not(pre) > code {
  background: #f1f5f9;
  color: var(--c-ink);
  padding: 2px 6px;
  border-radius: 4px;
  font-weight: 600;
}

/* CODE INSIDE PRE (TRANSPARENT, NO WHITE BACKGROUND) */
pre code {
  background: transparent !important;
  border: none !important;
  padding: 0 !important;
  color: inherit !important;
  font-weight: 400 !important;
}

/* ==========================================================================
   TOPBAR
   ========================================================================== */
.topbar {
  position: sticky;
  top: 0;
  z-index: 100;
  background: var(--topbar-bg);
  border-bottom: 1px solid var(--border-divider);
}
.topbar-inner {
  max-width: 1720px;
  margin: 0 auto;
  padding: 0 24px;
  height: 58px;
  display: flex;
  align-items: center;
  gap: 16px;
}
.mobile-menu-btn {
  display: none;
  background: #f1f5f9;
  border: none;
  border-radius: 4px;
  padding: 6px;
  color: var(--c-ink);
  cursor: pointer;
}
.brand {
  display: flex;
  align-items: center;
  gap: 10px;
  font-weight: 900;
  font-size: 15px;
  letter-spacing: 0.04em;
  color: var(--c-ink);
  text-transform: uppercase;
  white-space: nowrap;
}
.brand-mark {
  width: 28px;
  height: 28px;
  background: var(--c-accent);
  border-radius: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--c-white);
  font-weight: 900;
  font-size: 12px;
}
.version-select-box {
  display: flex;
  align-items: center;
  background: #f1f5f9;
  border-radius: 4px;
  padding: 2px 8px;
}
.version-select {
  background: transparent;
  border: none;
  color: var(--c-accent);
  font-family: var(--font-mono);
  font-size: 11px;
  font-weight: 700;
  cursor: pointer;
  outline: none;
}

.search-container {
  flex: 1;
  max-width: 400px;
  margin: 0 auto 0 16px;
  position: relative;
}
.search-input {
  width: 100%;
  background: #f8fafc;
  border: 1px solid transparent;
  border-radius: 4px;
  padding: 8px 34px 8px 14px;
  color: var(--c-ink);
  font-size: 12.5px;
  outline: none;
}
.search-input:focus {
  background: #ffffff;
  border-color: var(--c-accent);
}
.search-input::placeholder { color: var(--text-muted); }
.search-kbd {
  position: absolute;
  right: 8px;
  top: 50%;
  transform: translateY(-50%);
  font-size: 10px;
  font-weight: 700;
  background: #ffffff;
  color: var(--text-muted);
  padding: 2px 6px;
  border-radius: 4px;
}
.top-actions {
  margin-left: auto;
  display: flex;
  align-items: center;
  gap: 10px;
}

/* ==========================================================================
   ONE-COLOR LIQUID GLASS BUTTONS
   ========================================================================== */
.btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: #f1f5f9;
  border: 1px solid rgba(15, 23, 42, 0.08);
  color: var(--c-ink);
  font-size: 12px;
  font-weight: 700;
  padding: 7px 14px;
  border-radius: 4px;
  cursor: pointer;
  white-space: nowrap;
}
.btn-primary {
  background: var(--c-accent);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: var(--c-white);
}
.btn-copied {
  background: #059669 !important;
  color: #ffffff !important;
  border-color: #059669 !important;
  transform: scale(1.04) !important;
}
.live-pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 11px;
  font-weight: 700;
  color: #059669;
  background: rgba(16, 185, 129, 0.08);
  padding: 5px 10px;
  border-radius: 4px;
}
.live-dot {
  width: 6px;
  height: 6px;
  background: #10b981;
  border-radius: 50%;
}

/* ==========================================================================
   LAYOUT STRUCTURE
   ========================================================================== */
.app-container {
  max-width: 1720px;
  margin: 0 auto;
  display: flex;
  min-height: calc(100vh - 58px);
}

/* LEFT SIDEBAR */
.sidebar {
  width: 280px;
  flex: 0 0 280px;
  position: sticky;
  top: 58px;
  height: calc(100vh - 58px);
  overflow-y: auto;
  padding: 24px 16px 48px;
  background: var(--sidebar-bg);
  border-right: 1px solid var(--border-divider);
}
.sidebar-group {
  margin-bottom: 24px;
}
.sidebar-heading {
  font-size: 10.5px;
  font-weight: 800;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: var(--text-faint);
  padding: 6px 10px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.sidebar-heading .badge {
  font-size: 9px;
  background: #e2e8f0;
  color: var(--text-muted);
  padding: 1px 5px;
  border-radius: 4px;
}
.nav-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: 12.5px;
  font-weight: 600;
  color: var(--text-secondary);
  padding: 7px 10px;
  border-radius: 4px;
  margin-bottom: 2px;
  border-left: 3px solid transparent;
}
.nav-item.active {
  background: #f1f5f9;
  color: var(--c-accent);
  border-left-color: var(--c-accent);
  font-weight: 700;
}
.nav-item .endpoint-method {
  font-family: var(--font-mono);
  font-size: 9.5px;
  font-weight: 800;
  padding: 1px 5px;
  border-radius: 4px;
}
.m-get-tag { background: var(--color-get-bg); color: var(--color-get); }
.m-post-tag { background: var(--color-post-bg); color: var(--color-post); }

/* MAIN CONTENT */
.main-wrapper {
  flex: 1;
  min-width: 0;
  padding: 40px 48px 96px;
}

/* BENTO HERO (LINELESS SPEC) */
.bento-hero {
  margin-bottom: 48px;
}
.eyebrow {
  font-size: 11px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  color: var(--c-accent);
  margin-bottom: 8px;
}
.bento-header h1 {
  font-size: 34px;
  font-weight: 900;
  color: var(--c-ink);
  letter-spacing: -0.02em;
  line-height: 1.15;
  margin-bottom: 12px;
}
.bento-lead {
  font-size: 14.5px;
  line-height: 1.7;
  color: var(--text-secondary);
  max-width: 940px;
}

/* BENTO GRID (LINELESS SHAPES) */
.bento-grid {
  display: grid;
  grid-template-columns: repeat(12, 1fr);
  gap: 16px;
  margin-top: 24px;
}
.bento-card {
  background: #f8fafc;
  border-radius: 4px;
  padding: 20px;
  position: relative;
}
.bento-col-8 { grid-column: span 8; }
.bento-col-4 { grid-column: span 4; }
.bento-col-12 { grid-column: span 12; }

.bento-title {
  font-size: 11px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: var(--text-muted);
  margin-bottom: 8px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.bento-val {
  font-size: 16px;
  font-weight: 800;
  color: var(--c-ink);
}
.bento-url-box {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: #ffffff;
  border-radius: 4px;
  padding: 8px 12px;
  font-family: var(--font-mono);
  font-size: 12.5px;
  color: var(--c-accent);
  font-weight: 700;
  margin-top: 8px;
}

.client-libraries-bar {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-top: 14px;
  padding-top: 12px;
  border-top: 1px solid rgba(15, 23, 42, 0.06);
  flex-wrap: wrap;
}
.lib-chip {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 11px;
  font-weight: 700;
  color: var(--text-secondary);
  background: #ffffff;
  padding: 3px 8px;
  border-radius: 4px;
}

/* ==========================================================================
   STRIPE 2-COLUMN SPLIT SECTION (LINELESS & BORDERLESS TEXT BLOCKS)
   ========================================================================== */
.api-section {
  padding: 44px 0;
  border-top: 1px solid var(--border-divider);
  scroll-margin-top: 70px;
}
.section-split {
  display: grid;
  grid-template-columns: 1.15fr 1fr;
  gap: 40px;
  align-items: start;
}
.doc-col { min-width: 0; }
.code-col { position: sticky; top: 76px; min-width: 0; }

.section-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 12px;
}
.section-title {
  font-size: 21px;
  font-weight: 900;
  color: var(--c-ink);
  letter-spacing: -0.01em;
}
.action-link {
  font-size: 11px;
  font-weight: 700;
  color: var(--c-accent);
  background: var(--c-accent-glass);
  border: 1px solid rgba(99, 91, 255, 0.2);
  padding: 5px 12px;
  border-radius: 4px;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  white-space: nowrap;
}
.endpoint-bar {
  display: flex;
  align-items: center;
  gap: 10px;
  margin: 12px 0 14px;
  flex-wrap: wrap;
}
.http-pill {
  font-family: var(--font-mono);
  font-size: 11px;
  font-weight: 900;
  padding: 3px 8px;
  border-radius: 4px;
  letter-spacing: 0.05em;
}
.http-get { background: var(--color-get-bg); color: var(--color-get); }
.http-post { background: var(--color-post-bg); color: var(--color-post); }

.endpoint-uri {
  font-family: var(--font-mono);
  font-size: 13.5px;
  font-weight: 800;
  color: var(--c-ink);
}
.tier-tag {
  font-size: 10.5px;
  font-weight: 800;
  padding: 3px 8px;
  border-radius: 4px;
  text-transform: uppercase;
  background: #f1f5f9;
  color: var(--c-ink);
}

.doc-desc {
  font-size: 13.5px;
  line-height: 1.65;
  color: var(--text-secondary);
  margin-bottom: 18px;
}
.doc-desc p { margin-bottom: 8px; }

/* PARAMETERS TABLE (LINELESS MINIMALIST) */
.param-section-title {
  font-size: 11px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: var(--text-faint);
  margin: 20px 0 8px;
}
.param-table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 18px;
  background: #ffffff;
}
.param-table th {
  text-align: left;
  font-size: 10.5px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: var(--text-muted);
  background: #f8fafc;
  padding: 8px 12px;
  border-bottom: 1px solid var(--border-divider);
}
.param-table td {
  padding: 10px 12px;
  border-bottom: 1px solid var(--border-divider);
  font-size: 12px;
  vertical-align: top;
  color: var(--text-secondary);
}
.param-table tr:last-child td { border-bottom: none; }
.param-name {
  font-family: var(--font-mono);
  font-weight: 700;
  color: var(--c-ink);
}
.param-type {
  font-family: var(--font-mono);
  font-size: 11px;
  color: var(--c-accent);
  margin-left: 6px;
  font-weight: 700;
}
.param-req {
  font-size: 9.5px;
  font-weight: 800;
  color: var(--color-del);
  text-transform: uppercase;
  margin-left: 6px;
}
.param-opt {
  font-size: 9.5px;
  font-weight: 700;
  color: var(--text-muted);
  text-transform: uppercase;
  margin-left: 6px;
}

/* ==========================================================================
   CODE CARD & RESPONSE PANEL (CLEAN DARK SYNTAX)
   ========================================================================== */
.code-card {
  background: var(--bg-code);
  border-radius: 4px;
  margin-bottom: 14px;
  overflow: hidden;
}
.code-card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: rgba(255, 255, 255, 0.04);
  border-bottom: 1px solid rgba(255, 255, 255, 0.06);
  padding: 7px 14px;
}
.copy-code-btn {
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.12);
  color: #cbd5e1;
  font-size: 11px;
  font-weight: 700;
  padding: 4px 10px;
  border-radius: 4px;
  cursor: pointer;
}
.code-body {
  padding: 14px;
  overflow-x: auto;
  font-family: var(--font-mono);
  font-size: 11.5px;
  line-height: 1.6;
  color: var(--bg-code-text);
}
.code-body pre { margin: 0; background: transparent !important; }

.response-card {
  background: var(--bg-code);
  border-radius: 4px;
  overflow: hidden;
}
.response-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: rgba(255, 255, 255, 0.04);
  border-bottom: 1px solid rgba(255, 255, 255, 0.06);
  padding: 7px 14px;
}
.status-indicator {
  display: flex;
  align-items: center;
  gap: 6px;
  font-family: var(--font-mono);
  font-size: 11px;
  font-weight: 800;
}
.status-200, .status-201 { color: #34d399; }
.status-422 { color: #f87171; }

/* SYNTAX HIGHLIGHTING */
.c-kw { color: #c084fc; font-weight: 700; }
.c-fn { color: #60a5fa; }
.c-str { color: #34d399; }
.c-num { color: #f472b6; }
.c-bool { color: #38bdf8; }
.c-key { color: #93c5fd; }

/* STATUS CODES GRID */
.status-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
  gap: 12px;
  margin: 16px 0 20px;
}
.status-item {
  background: #f8fafc;
  border-radius: 4px;
  padding: 12px 14px;
}
.status-item-code {
  font-family: var(--font-mono);
  font-weight: 800;
  font-size: 12.5px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 4px;
}
.status-item-desc {
  font-size: 11.5px;
  color: var(--text-muted);
}

/* BENTO MODAL */
.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.5);
  z-index: 200;
  display: none;
  align-items: center;
  justify-content: center;
  padding: 20px;
}
.modal-content {
  background: var(--c-white);
  border-radius: 4px;
  width: 100%;
  max-width: 820px;
  max-height: 85vh;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}
.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 20px;
  border-bottom: 1px solid var(--border-divider);
  background: var(--c-white);
}
.modal-title {
  font-size: 14px;
  font-weight: 800;
  color: var(--c-ink);
}
.modal-body {
  padding: 20px;
  overflow-y: auto;
  font-family: var(--font-mono);
  font-size: 11.5px;
  line-height: 1.6;
  background: var(--bg-code);
  color: var(--bg-code-text);
  white-space: pre-wrap;
}
.modal-footer {
  padding: 14px 20px;
  border-top: 1px solid var(--border-divider);
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 10px;
  background: var(--c-white);
}

/* BENTO TOAST */
.toast-container {
  position: fixed;
  bottom: 24px;
  right: 24px;
  z-index: 300;
  display: flex;
  flex-direction: column;
  gap: 10px;
  pointer-events: none;
}
.toast-item {
  pointer-events: auto;
  min-width: 280px;
  max-width: 380px;
  background: var(--c-white);
  border: 1px solid rgba(15, 23, 42, 0.08);
  border-radius: 4px;
  padding: 12px 16px;
  display: flex;
  align-items: center;
  gap: 10px;
}
.toast-icon {
  width: 22px;
  height: 22px;
  flex: 0 0 22px;
  background: rgba(16, 185, 129, 0.1);
  color: #059669;
  border-radius: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.toast-content { flex: 1; }
.toast-title {
  font-size: 12px;
  font-weight: 800;
  color: var(--c-ink);
}
.toast-desc {
  font-size: 11px;
  color: var(--text-muted);
  margin-top: 1px;
}

/* RESPONSIVE */
@media (max-width: 1200px) {
  .section-split { grid-template-columns: 1fr; gap: 24px; }
  .code-col { position: static; }
}
@media (max-width: 860px) {
  .mobile-menu-btn { display: inline-flex; }
  .sidebar {
    display: none;
    position: fixed;
    top: 58px;
    left: 0;
    bottom: 0;
    z-index: 90;
    width: 280px;
    background: var(--c-white);
  }
  .sidebar.open { display: block; }
  .bento-col-8, .bento-col-4 { grid-column: span 12; }
  .main-wrapper { padding: 24px 16px 64px; }
  .search-container { display: none; }
}
</style>
</head>
<body>

<!-- TOPBAR -->
<header class="topbar">
  <div class="topbar-inner">
    <button class="mobile-menu-btn" onclick="toggleMobileSidebar()" aria-label="Toggle navigation">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
    </button>

    <a href="/developers" class="brand">
      <div class="brand-mark">API</div>
      DEVELOPER API
    </a>

    <div class="version-select-box">
      <select class="version-select" onchange="switchApiVersion(this.value)">
        <option value="v1">v1.2 (Active)</option>
        <option value="v1.0">v1.0 (LTS)</option>
        <option value="v2">v2.0 (Preview)</option>
      </select>
    </div>
    
    <div class="search-container">
      <input type="text" class="search-input" id="quickSearch" placeholder="Search 47 categories, 207 requests...">
      <span class="search-kbd">&#8984;K</span>
    </div>
    
    <div class="top-actions">
      <div class="live-pill">
        <span class="live-dot"></span> 99.9% Uptime
      </div>
      <button class="btn btn-primary" onclick="openLlmModal()">
        AI System Prompt
      </button>
      <a href="/api/v1/postman.json" class="btn" target="_blank" title="Requires Manager/Admin token">
        Postman Collection
      </a>
    </div>
  </div>
</header>

<!-- APPLICATION WRAPPER -->
<div class="app-container">

  <!-- LEFT STICKY SIDEBAR -->
  <aside class="sidebar" id="sidebarNav">
    
    <!-- GROUP 1: GETTING STARTED -->
    <div class="sidebar-group">
      <div class="sidebar-heading">
        <span>Getting Started</span>
        <span class="badge">Core</span>
      </div>
      <a href="#overview" class="nav-item active">Overview</a>
      <a href="#authentication" class="nav-item">Authentication</a>
      <a href="#response-envelope" class="nav-item">Response Envelope</a>
      <a href="#http-status-codes" class="nav-item">Status Codes Summary</a>
      <a href="#rbac-tiers" class="nav-item">RBAC 4-Tier Matrix</a>
      <a href="#health-status" class="nav-item">
        <span>Health &amp; Status</span>
        <span class="endpoint-method m-get-tag">GET</span>
      </a>
      <a href="#operations-guide" class="nav-item">
        <span>Operations Guide</span>
        <span class="endpoint-method m-get-tag">GET</span>
      </a>
      <a href="#postman-spec" class="nav-item">Postman Collection</a>
      <a href="#llm-integration" class="nav-item">AI Integration Guide</a>
    </div>

    <!-- GROUP 2: PUBLIC CATALOG -->
    <div class="sidebar-group">
      <div class="sidebar-heading">
        <span>Public Catalog</span>
        <span class="badge">Tier 1</span>
      </div>
      <a href="#products-catalog" class="nav-item">
        <span>Products Catalog</span>
        <span class="endpoint-method m-get-tag">GET</span>
      </a>
      <a href="#product-matrix" class="nav-item">
        <span>2D Matrix (Size x Color)</span>
        <span class="endpoint-method m-get-tag">GET</span>
      </a>
      <a href="#cart-endpoints" class="nav-item">
        <span>Shopping Cart</span>
        <span class="endpoint-method m-post-tag">CRUD</span>
      </a>
    </div>

    <!-- GROUP 3: POS & ORDERS -->
    <div class="sidebar-group">
      <div class="sidebar-heading">
        <span>POS &amp; Orders</span>
        <span class="badge">Tier 2</span>
      </div>
      <a href="#pos-checkout" class="nav-item">
        <span>Transactional Checkout</span>
        <span class="endpoint-method m-post-tag">POST</span>
      </a>
      <a href="#khqr-bakong" class="nav-item">
        <span>KHQR &amp; Bakong Payments</span>
        <span class="endpoint-method m-post-tag">POST</span>
      </a>
    </div>

    <!-- GROUP 4: MANAGER OPERATIONS -->
    <div class="sidebar-group">
      <div class="sidebar-heading">
        <span>Manager Operations</span>
        <span class="badge">Tier 3</span>
      </div>
      <a href="#inventory-valuation" class="nav-item">
        <span>Inventory Valuation</span>
        <span class="endpoint-method m-get-tag">GET</span>
      </a>
      <a href="#stock-movements" class="nav-item">
        <span>Stock Movements &amp; Opname</span>
        <span class="endpoint-method m-post-tag">POST</span>
      </a>
    </div>

    <!-- GROUP 5: ADMIN SECURITY -->
    <div class="sidebar-group">
      <div class="sidebar-heading">
        <span>Admin &amp; Security</span>
        <span class="badge">Tier 4</span>
      </div>
      <a href="#admin-master-pulse" class="nav-item">
        <span>Master Pulse &amp; Analytics</span>
        <span class="endpoint-method m-get-tag">GET</span>
      </a>
    </div>

  </aside>

  <!-- MAIN DOCUMENTATION WORKSPACE -->
  <main class="main-wrapper">

    <!-- BENTO HERO -->
    <section id="overview" class="bento-hero">
      <div class="bento-header">
        <div class="eyebrow">REST Architecture Specification</div>
        <h1>DEVELOPER API</h1>
        <p class="bento-lead">
          The DEVELOPER API is organized around REST. Our API has predictable resource-oriented URLs, accepts JSON-encoded request bodies, returns JSON-encoded responses, and uses standard HTTP response codes, authentication, and verbs. You can use the DEVELOPER API in sandboxes without affecting your live data. The API key that you use to authenticate the request determines whether the request runs in live mode or in a sandbox.
        </p>
      </div>

      <!-- BENTO GRID (LINELESS) -->
      <div class="bento-grid">
        <div class="bento-card bento-col-8">
          <div class="bento-title">
            <span>Primary Gateway Endpoint</span>
            <span class="live-pill"><span class="live-dot"></span> Active</span>
          </div>
          <div class="bento-val">JSON HTTPS Gateway v1</div>
          <div class="bento-url-box">
            <span id="baseUrlText">https://api.kesararamwithdigital.tech/api/v1</span>
            <button class="btn" style="padding: 3px 10px; font-size: 11px;" onclick="copyText('https://api.kesararamwithdigital.tech/api/v1', this)">Copy</button>
          </div>
          
          <div class="client-libraries-bar">
            <span style="font-size: 11px; font-weight: 800; color: var(--text-muted); text-transform: uppercase;">Environments:</span>
            <span class="lib-chip">cURL</span>
            <span class="lib-chip">TypeScript</span>
            <span class="lib-chip">Python</span>
            <span class="lib-chip">PHP</span>
            <span class="lib-chip">Node.js</span>
            <span class="lib-chip">Go</span>
          </div>
        </div>

        <div class="bento-card bento-col-4">
          <div class="bento-title">
            <span>Postman Suite</span>
            <span class="badge">200 OK</span>
          </div>
          <div class="bento-val">47 Folders / 207 Requests</div>
          <p style="font-size: 11.5px; color: var(--text-muted); margin-top: 8px;">
            Served via <code>/api/v1/postman.json</code> with Manager/Admin token.
          </p>
        </div>

        <div class="bento-card bento-col-4">
          <div class="bento-title"><span>Payload Protocol</span></div>
          <div class="bento-val">application/json</div>
          <p style="font-size: 11.5px; color: var(--text-muted); margin-top: 6px;">UTF-8 JSON encoding strictly enforced.</p>
        </div>

        <div class="bento-card bento-col-4">
          <div class="bento-title"><span>Security Model</span></div>
          <div class="bento-val">4-Tier Strict RBAC</div>
          <p style="font-size: 11.5px; color: var(--text-muted); margin-top: 6px;">GUEST &lt; CASHIER &lt; MANAGER &lt; ADMIN.</p>
        </div>

        <div class="bento-card bento-col-4">
          <div class="bento-title"><span>Idempotency Key</span></div>
          <div class="bento-val">X-Idempotency-Key</div>
          <p style="font-size: 11.5px; color: var(--text-muted); margin-top: 6px;">Prevents duplicate mutations.</p>
        </div>
      </div>
    </section>

    <!-- SECTION: AUTHENTICATION -->
    <section id="authentication" class="api-section">
      <div class="section-split">
        <div class="doc-col">
          <div class="section-head">
            <h2 class="section-title">Authentication</h2>
            <div class="section-actions">
              <button class="action-link" onclick="copySectionLlm('authentication', this)">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                Copy AI Prompt
              </button>
            </div>
          </div>
          
          <div class="endpoint-bar">
            <span class="http-pill http-post">POST</span>
            <span class="endpoint-uri">/auth/login</span>
            <span class="tier-tag">Public</span>
          </div>

          <div class="doc-desc">
            <p>
              Exchanges employee credentials for a Sanctum Bearer token with full permission capabilities. Attach token as <code>Authorization: Bearer &lt;access_token&gt;</code> on all protected calls.
            </p>
          </div>

          <div class="param-section-title">Parameters</div>
          <table class="param-table">
            <thead><tr><th>Field</th><th>Type</th><th>Description</th></tr></thead>
            <tbody>
              <tr><td><span class="param-name">email</span> <span class="param-opt">opt</span></td><td><span class="param-type">string</span></td><td>User registered email address.</td></tr>
              <tr><td><span class="param-name">password</span> <span class="param-req">req</span></td><td><span class="param-type">string</span></td><td>Account password.</td></tr>
              <tr><td><span class="param-name">device_name</span> <span class="param-opt">opt</span></td><td><span class="param-type">string</span></td><td>Client identifier (e.g. "POS-01").</td></tr>
            </tbody>
          </table>
        </div>

        <div class="code-col">
          <div class="code-card">
            <div class="code-card-header">
              <span style="font-size:11px; font-weight:800; color:#94a3b8;">REQUEST</span>
              <button class="copy-code-btn" onclick="copyCodeBlock(this)">Copy</button>
            </div>
            <div class="code-body">
              <pre><code><span class="c-kw">curl</span> -X POST https://api.kesararamwithdigital.tech/api/v1/auth/login \
  -H <span class="c-str">"Content-Type: application/json"</span> \
  -d <span class="c-str">'{
    "email": "manager@store.com",
    "password": "SecretPassword123"
  }'</span></code></pre>
            </div>
          </div>

          <div class="response-card">
            <div class="response-header">
              <div class="status-indicator status-200"><span class="live-dot"></span> 200 OK</div>
            </div>
            <div class="code-body">
              <pre><code>{
  <span class="c-key">"success"</span>: <span class="c-bool">true</span>,
  <span class="c-key">"status_code"</span>: <span class="c-num">200</span>,
  <span class="c-key">"data"</span>: {
    <span class="c-key">"access_token"</span>: <span class="c-str">"14|9kL7aBcD8...v1N"</span>,
    <span class="c-key">"token_type"</span>: <span class="c-str">"Bearer"</span>,
    <span class="c-key">"role"</span>: <span class="c-str">"MANAGER"</span>
  }
}</code></pre>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- SECTION: RESPONSE ENVELOPE -->
    <section id="response-envelope" class="api-section">
      <div class="section-split">
        <div class="doc-col">
          <div class="section-head">
            <h2 class="section-title">Standard Response Envelope</h2>
            <div class="section-actions">
              <button class="action-link" onclick="copySectionLlm('response-envelope', this)">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                Copy AI Prompt
              </button>
            </div>
          </div>

          <div class="doc-desc">
            <p>
              Every response returns a deterministic JSON envelope with <code>success</code>, <code>status_code</code>, <code>request_id</code> (distributed tracing), and payload.
            </p>
          </div>
        </div>

        <div class="code-col">
          <div class="response-card">
            <div class="response-header">
              <div class="status-indicator status-422"><span class="live-dot" style="background:#f87171;"></span> 422 Validation Envelope</div>
            </div>
            <div class="code-body">
              <pre><code>{
  <span class="c-key">"success"</span>: <span class="c-bool">false</span>,
  <span class="c-key">"status_code"</span>: <span class="c-num">422</span>,
  <span class="c-key">"request_id"</span>: <span class="c-str">"req_e72b4f10-91a3-49d8"</span>,
  <span class="c-key">"error"</span>: {
    <span class="c-key">"code"</span>: <span class="c-str">"VALIDATION_ERROR"</span>,
    <span class="c-key">"detail"</span>: {
      <span class="c-key">"fields"</span>: [{ <span class="c-key">"field"</span>: <span class="c-str">"quantity"</span>, <span class="c-key">"message"</span>: <span class="c-str">"Must be >= 1"</span> }]
    }
  }
}</code></pre>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- SECTION: STATUS CODES SUMMARY -->
    <section id="http-status-codes" class="api-section">
      <div class="section-split">
        <div class="doc-col" style="grid-column: span 2;">
          <div class="section-head">
            <h2 class="section-title">HTTP Status Code Summary &amp; Issue Diagnostics</h2>
            <div class="section-actions">
              <button class="action-link" onclick="copySectionLlm('http-status-codes', this)">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                Copy AI Prompt
              </button>
            </div>
          </div>

          <div class="doc-desc">
            <p>
              Standardized HTTP response codes categorized by responsibility (Frontend Client Issue vs. Backend Server Issue) with actionable recovery paths.
            </p>
          </div>

          <div class="status-grid">
            <!-- 200 OK -->
            <div class="status-item">
              <div class="status-item-code status-200">
                <span>200 OK</span>
                <span class="badge" style="background:#e0f2fe; color:#0369a1; font-weight:800;">SUCCESS</span>
              </div>
              <div class="status-item-desc">
                <strong>Lifecycle:</strong> Successful GET, PUT, or DELETE. Payload returned inside <code>data</code>.
              </div>
            </div>

            <!-- 201 Created -->
            <div class="status-item">
              <div class="status-item-code status-201">
                <span>201 Created</span>
                <span class="badge" style="background:#dcfce7; color:#15803d; font-weight:800;">SUCCESS</span>
              </div>
              <div class="status-item-desc">
                <strong>Lifecycle:</strong> Successful POST resource creation (Order, Customer, Product).
              </div>
            </div>

            <!-- 400 Bad Request -->
            <div class="status-item">
              <div class="status-item-code status-422">
                <span>400 Bad Request</span>
                <span class="badge" style="background:#fee2e2; color:#b91c1c; font-weight:800;">FRONTEND ISSUE</span>
              </div>
              <div class="status-item-desc">
                <strong>Root Cause:</strong> Malformed JSON syntax or invalid query params.<br>
                <strong>Fix:</strong> Validate JSON body format and Content-Type header before dispatch.
              </div>
            </div>

            <!-- 401 Unauthorized -->
            <div class="status-item">
              <div class="status-item-code status-422">
                <span>401 Unauthorized</span>
                <span class="badge" style="background:#fee2e2; color:#b91c1c; font-weight:800;">FRONTEND ISSUE</span>
              </div>
              <div class="status-item-desc">
                <strong>Root Cause:</strong> Missing, invalid, or expired Sanctum Bearer token.<br>
                <strong>Fix:</strong> Clear local storage, prompt user re-login, and re-fetch token via <code>/auth/login</code>.
              </div>
            </div>

            <!-- 403 Forbidden -->
            <div class="status-item">
              <div class="status-item-code status-422">
                <span>403 Forbidden</span>
                <span class="badge" style="background:#fee2e2; color:#b91c1c; font-weight:800;">FRONTEND ISSUE</span>
              </div>
              <div class="status-item-desc">
                <strong>Root Cause:</strong> Authenticated role lacks permission tier (e.g. Cashier calling Manager route).<br>
                <strong>Fix:</strong> Hide UI controls for unauthorized tiers based on user's active role.
              </div>
            </div>

            <!-- 404 Not Found -->
            <div class="status-item">
              <div class="status-item-code status-422">
                <span>404 Not Found</span>
                <span class="badge" style="background:#fee2e2; color:#b91c1c; font-weight:800;">FRONTEND ISSUE</span>
              </div>
              <div class="status-item-desc">
                <strong>Root Cause:</strong> Target resource ID does not exist or URL endpoint is mistyped.<br>
                <strong>Fix:</strong> Verify database entity ID or check route spelling.
              </div>
            </div>

            <!-- 409 Conflict -->
            <div class="status-item">
              <div class="status-item-code status-422">
                <span>409 Conflict</span>
                <span class="badge" style="background:#fee2e2; color:#b91c1c; font-weight:800;">FRONTEND ISSUE</span>
              </div>
              <div class="status-item-desc">
                <strong>Root Cause:</strong> Unique constraint violation (e.g. barcode/email already exists) or concurrent edit.<br>
                <strong>Fix:</strong> Prompt user to use unique barcode/email or refresh latest record state.
              </div>
            </div>

            <!-- 422 Unprocessable Entity -->
            <div class="status-item">
              <div class="status-item-code status-422">
                <span>422 Unprocessable</span>
                <span class="badge" style="background:#fee2e2; color:#b91c1c; font-weight:800;">FRONTEND ISSUE</span>
              </div>
              <div class="status-item-desc">
                <strong>Root Cause:</strong> Form field validation error (e.g. price &lt; 0, quantity &lt; 1).<br>
                <strong>Fix:</strong> Parse <code>error.detail.fields</code> array and bind error messages to inputs.
              </div>
            </div>

            <!-- 423 Locked -->
            <div class="status-item">
              <div class="status-item-code status-422">
                <span>423 Locked</span>
                <span class="badge" style="background:#fee2e2; color:#b91c1c; font-weight:800;">SECURITY ISSUE</span>
              </div>
              <div class="status-item-desc">
                <strong>Root Cause:</strong> Account locked after 10 consecutive failed login attempts.<br>
                <strong>Fix:</strong> Wait for lockout window (15 mins) or have Admin reset password.
              </div>
            </div>

            <!-- 429 Too Many Requests -->
            <div class="status-item">
              <div class="status-item-code status-422">
                <span>429 Rate Limited</span>
                <span class="badge" style="background:#fee2e2; color:#b91c1c; font-weight:800;">FRONTEND ISSUE</span>
              </div>
              <div class="status-item-desc">
                <strong>Root Cause:</strong> Request threshold exceeded for current tier quota.<br>
                <strong>Fix:</strong> Implement exponential backoff using <code>Retry-After</code> header duration.
              </div>
            </div>

            <!-- 500 Internal Server Error -->
            <div class="status-item">
              <div class="status-item-code status-422">
                <span>500 Server Error</span>
                <span class="badge" style="background:#fef3c7; color:#b45309; font-weight:800;">BACKEND ISSUE</span>
              </div>
              <div class="status-item-desc">
                <strong>Root Cause:</strong> Unhandled backend exception or database query failure.<br>
                <strong>Fix:</strong> Check server logs using <code>request_id</code> for stack trace root cause.
              </div>
            </div>

            <!-- 503 Service Unavailable -->
            <div class="status-item">
              <div class="status-item-code status-422">
                <span>503 Unavailable</span>
                <span class="badge" style="background:#fef3c7; color:#b45309; font-weight:800;">BACKEND ISSUE</span>
              </div>
              <div class="status-item-desc">
                <strong>Root Cause:</strong> Database connectivity lost or serverless cold pool recovery.<br>
                <strong>Fix:</strong> Automatic retry after 2-5 seconds or check Neon DB status.
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- SECTION: RBAC 4-TIER MATRIX -->
    <section id="rbac-tiers" class="api-section">
      <div class="section-split">
        <div class="doc-col" style="grid-column: span 2;">
          <div class="section-head">
            <h2 class="section-title">RBAC 4-Tier Security Matrix</h2>
            <div class="section-actions">
              <button class="action-link" onclick="copySectionLlm('rbac-tiers', this)">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                Copy AI Prompt
              </button>
            </div>
          </div>

          <table class="param-table">
            <thead><tr><th>Tier Level</th><th>Required Role</th><th>Rate Limit</th><th>Permitted Resource Scope</th></tr></thead>
            <tbody>
              <tr><td><span class="tier-tag">Tier 1: Public</span></td><td>Guest</td><td><code>30 req / min</code></td><td>Catalog reads, matrix, cart, wishlist, currencies, banners.</td></tr>
              <tr><td><span class="tier-tag">Tier 2: Cashier</span></td><td>CASHIER, STAFF, MANAGER, ADMIN</td><td><code>100 req / min</code></td><td>POS checkout, orders, cash shifts, KHQR, customers, receipts.</td></tr>
              <tr><td><span class="tier-tag">Tier 3: Manager</span></td><td>MANAGER, ADMIN</td><td><code>200 req / min</code></td><td>Catalog writes, stock transfers, purchases, valuation, reports.</td></tr>
              <tr><td><span class="tier-tag">Tier 4: Admin</span></td><td>ADMIN</td><td><code>300 req / min</code></td><td>Employees CRUD, master pulse, system security analytics.</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </section>

    <!-- SECTION: HEALTH & STATUS -->
    <section id="health-status" class="api-section">
      <div class="section-split">
        <div class="doc-col">
          <div class="section-head">
            <h2 class="section-title">Health Check &amp; System Status</h2>
            <div class="section-actions">
              <button class="action-link" onclick="copySectionLlm('health-status', this)">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                Copy AI Prompt
              </button>
            </div>
          </div>
          <div class="endpoint-bar">
            <span class="http-pill http-get">GET</span>
            <span class="endpoint-uri">/health</span>
            <span class="tier-tag">Public</span>
          </div>
          <div class="doc-desc">
            <p>Real-time database connectivity and live inventory counts for uptime monitoring.</p>
          </div>
        </div>
        <div class="code-col">
          <div class="response-card">
            <div class="response-header"><div class="status-indicator status-200"><span class="live-dot"></span> 200 OK</div></div>
            <div class="code-body"><pre><code>{ <span class="c-key">"success"</span>: <span class="c-bool">true</span>, <span class="c-key">"data"</span>: { <span class="c-key">"database"</span>: <span class="c-str">"Connected"</span>, <span class="c-key">"live_products_count"</span>: <span class="c-num">174</span> } }</code></pre></div>
          </div>
        </div>
      </div>
    </section>

    <!-- SECTION: OPERATIONS GUIDE -->
    <section id="operations-guide" class="api-section">
      <div class="section-split">
        <div class="doc-col">
          <div class="section-head">
            <h2 class="section-title">Operations &amp; POS Quickstart Guide</h2>
            <div class="section-actions">
              <button class="action-link" onclick="copySectionLlm('operations-guide', this)">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                Copy AI Prompt
              </button>
            </div>
          </div>
          <div class="endpoint-bar">
            <span class="http-pill http-get">GET</span>
            <span class="endpoint-uri">/guide</span>
            <span class="tier-tag">Public</span>
          </div>
          <div class="doc-desc">
            <p>Complete operational guide data for POS cashiers, 4-tier quantity lifecycle, and VAT calculations.</p>
          </div>
        </div>
        <div class="code-col">
          <div class="code-card">
            <div class="code-card-header"><span style="font-size:11px; font-weight:800; color:#94a3b8;">REQUEST</span><button class="copy-code-btn" onclick="copyCodeBlock(this)">Copy</button></div>
            <div class="code-body"><pre><code><span class="c-kw">curl</span> -X GET https://api.kesararamwithdigital.tech/api/v1/guide</code></pre></div>
          </div>
        </div>
      </div>
    </section>

    <!-- SECTION: POSTMAN SPEC -->
    <section id="postman-spec" class="api-section">
      <div class="section-split">
        <div class="doc-col">
          <div class="section-head">
            <h2 class="section-title">Postman Master Collection</h2>
            <div class="section-actions">
              <button class="action-link" onclick="copySectionLlm('postman-spec', this)">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                Copy AI Prompt
              </button>
            </div>
          </div>
          <div class="endpoint-bar">
            <span class="http-pill http-get">GET</span>
            <span class="endpoint-uri">/postman.json</span>
            <span class="tier-tag">Manager+</span>
          </div>
          <div class="doc-desc">
            <p>Serves the full 47-folder / 207-request Postman test suite with preloaded production IDs.</p>
          </div>
        </div>
        <div class="code-col">
          <div class="code-card">
            <div class="code-card-header"><span style="font-size:11px; font-weight:800; color:#94a3b8;">REQUEST</span><button class="copy-code-btn" onclick="copyCodeBlock(this)">Copy</button></div>
            <div class="code-body"><pre><code><span class="c-kw">curl</span> -X GET https://api.kesararamwithdigital.tech/api/v1/postman.json \
  -H <span class="c-str">"Authorization: Bearer &lt;manager_token&gt;"</span> -o collection.json</code></pre></div>
          </div>
        </div>
      </div>
    </section>

    <!-- SECTION: PRODUCTS CATALOG -->
    <section id="products-catalog" class="api-section">
      <div class="section-split">
        <div class="doc-col">
          <div class="section-head">
            <h2 class="section-title">Products Catalog</h2>
            <div class="section-actions">
              <button class="action-link" onclick="copySectionLlm('products-catalog', this)">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                Copy AI Prompt
              </button>
            </div>
          </div>
          <div class="endpoint-bar">
            <span class="http-pill http-get">GET</span>
            <span class="endpoint-uri">/products</span>
            <span class="tier-tag">Public</span>
          </div>
          <div class="doc-desc"><p>Paginated catalog products with pricing, category, and primary image assets.</p></div>
        </div>
        <div class="code-col">
          <div class="response-card">
            <div class="response-header"><div class="status-indicator status-200"><span class="live-dot"></span> 200 OK</div></div>
            <div class="code-body"><pre><code>{
  <span class="c-key">"success"</span>: <span class="c-bool">true</span>,
  <span class="c-key">"data"</span>: [{ <span class="c-key">"id"</span>: <span class="c-num">182</span>, <span class="c-key">"name"</span>: <span class="c-str">"Silk Monogram Blazer"</span>, <span class="c-key">"base_price"</span>: <span class="c-num">240.00</span> }]
}</code></pre></div>
          </div>
        </div>
      </div>
    </section>

    <!-- SECTION: 2D MATRIX -->
    <section id="product-matrix" class="api-section">
      <div class="section-split">
        <div class="doc-col">
          <div class="section-head">
            <h2 class="section-title">Product 2D Matrix (Size x Color)</h2>
            <div class="section-actions">
              <button class="action-link" onclick="copySectionLlm('product-matrix', this)">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                Copy AI Prompt
              </button>
            </div>
          </div>
          <div class="endpoint-bar">
            <span class="http-pill http-get">GET</span>
            <span class="endpoint-uri">/products/{id}/matrix</span>
            <span class="tier-tag">Public</span>
          </div>
          <div class="doc-desc"><p>2D grid matrix of SKU stock across clothing sizes (columns) and colors (rows).</p></div>
        </div>
        <div class="code-col">
          <div class="code-card">
            <div class="code-card-header"><span style="font-size:11px; font-weight:800; color:#94a3b8;">REQUEST</span><button class="copy-code-btn" onclick="copyCodeBlock(this)">Copy</button></div>
            <div class="code-body"><pre><code><span class="c-kw">curl</span> -X GET https://api.kesararamwithdigital.tech/api/v1/products/182/matrix</code></pre></div>
          </div>
        </div>
      </div>
    </section>

    <!-- SECTION: SHOPPING CART -->
    <section id="cart-endpoints" class="api-section">
      <div class="section-split">
        <div class="doc-col">
          <div class="section-head">
            <h2 class="section-title">Shopping Cart</h2>
            <div class="section-actions">
              <button class="action-link" onclick="copySectionLlm('cart-endpoints', this)">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                Copy AI Prompt
              </button>
            </div>
          </div>
          <div class="endpoint-bar">
            <span class="http-pill http-post">POST</span>
            <span class="endpoint-uri">/cart</span>
            <span class="tier-tag">Public</span>
          </div>
          <div class="doc-desc"><p>Adds items to the active cart session before checkout.</p></div>
        </div>
        <div class="code-col">
          <div class="code-card">
            <div class="code-card-header"><span style="font-size:11px; font-weight:800; color:#94a3b8;">REQUEST</span><button class="copy-code-btn" onclick="copyCodeBlock(this)">Copy</button></div>
            <div class="code-body"><pre><code><span class="c-kw">curl</span> -X POST https://api.kesararamwithdigital.tech/api/v1/cart \
  -H <span class="c-str">"Content-Type: application/json"</span> \
  -d <span class="c-str">'{ "variant_id": 180, "quantity": 1 }'</span></code></pre></div>
          </div>
        </div>
      </div>
    </section>

    <!-- SECTION: POS CHECKOUT -->
    <section id="pos-checkout" class="api-section">
      <div class="section-split">
        <div class="doc-col">
          <div class="section-head">
            <h2 class="section-title">Transactional POS Checkout</h2>
            <div class="section-actions">
              <button class="action-link" onclick="copySectionLlm('pos-checkout', this)">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                Copy AI Prompt
              </button>
            </div>
          </div>
          <div class="endpoint-bar">
            <span class="http-pill http-post">POST</span>
            <span class="endpoint-uri">/orders/checkout</span>
            <span class="tier-tag">Cashier+</span>
          </div>
          <div class="doc-desc">
            <p>Atomic checkout with pessimistic row locks on inventory, discount calculations, and receipt generation.</p>
          </div>
        </div>
        <div class="code-col">
          <div class="code-card">
            <div class="code-card-header"><span style="font-size:11px; font-weight:800; color:#94a3b8;">REQUEST</span><button class="copy-code-btn" onclick="copyCodeBlock(this)">Copy</button></div>
            <div class="code-body"><pre><code><span class="c-kw">curl</span> -X POST https://api.kesararamwithdigital.tech/api/v1/orders/checkout \
  -H <span class="c-str">"Authorization: Bearer &lt;token&gt;"</span> \
  -H <span class="c-str">"X-Idempotency-Key: idemp_pos_8821a"</span> \
  -d <span class="c-str">'{ "items": [{ "variant_id": 180, "quantity": 2 }], "payment_method": "CASH" }'</span></code></pre></div>
          </div>
          <div class="response-card">
            <div class="response-header"><div class="status-indicator status-201"><span class="live-dot"></span> 201 Created</div></div>
            <div class="code-body"><pre><code>{ <span class="c-key">"success"</span>: <span class="c-bool">true</span>, <span class="c-key">"data"</span>: { <span class="c-key">"order_id"</span>: <span class="c-num">25</span>, <span class="c-key">"total_amount"</span>: <span class="c-num">88.00</span> } }</code></pre></div>
          </div>
        </div>
      </div>
    </section>

    <!-- SECTION: KHQR PAYMENTS -->
    <section id="khqr-bakong" class="api-section">
      <div class="section-split">
        <div class="doc-col">
          <div class="section-head">
            <h2 class="section-title">KHQR &amp; Bakong Payments</h2>
            <div class="section-actions">
              <button class="action-link" onclick="copySectionLlm('khqr-bakong', this)">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                Copy AI Prompt
              </button>
            </div>
          </div>
          <div class="endpoint-bar">
            <span class="http-pill http-post">POST</span>
            <span class="endpoint-uri">/payments/khqr</span>
            <span class="tier-tag">Cashier+</span>
          </div>
          <div class="doc-desc"><p>Generates dynamic EMVCo KHQR payloads for Bakong &amp; Cambodian banking apps.</p></div>
        </div>
        <div class="code-col">
          <div class="code-card">
            <div class="code-card-header"><span style="font-size:11px; font-weight:800; color:#94a3b8;">REQUEST</span><button class="copy-code-btn" onclick="copyCodeBlock(this)">Copy</button></div>
            <div class="code-body"><pre><code><span class="c-kw">curl</span> -X POST https://api.kesararamwithdigital.tech/api/v1/payments/khqr \
  -H <span class="c-str">"Authorization: Bearer &lt;token&gt;"</span> \
  -d <span class="c-str">'{ "order_id": 25, "amount": 88.00, "currency": "USD" }'</span></code></pre></div>
          </div>
        </div>
      </div>
    </section>

    <!-- SECTION: INVENTORY VALUATION -->
    <section id="inventory-valuation" class="api-section">
      <div class="section-split">
        <div class="doc-col">
          <div class="section-head">
            <h2 class="section-title">Inventory Valuation &amp; Asset Margin</h2>
            <div class="section-actions">
              <button class="action-link" onclick="copySectionLlm('inventory-valuation', this)">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                Copy AI Prompt
              </button>
            </div>
          </div>
          <div class="endpoint-bar">
            <span class="http-pill http-get">GET</span>
            <span class="endpoint-uri">/reports/inventory-valuation</span>
            <span class="tier-tag">Manager+</span>
          </div>
          <div class="doc-desc"><p>Calculates purchased cost value, retail resale asset value, and gross profit margins.</p></div>
        </div>
        <div class="code-col">
          <div class="code-card">
            <div class="code-card-header"><span style="font-size:11px; font-weight:800; color:#94a3b8;">REQUEST</span><button class="copy-code-btn" onclick="copyCodeBlock(this)">Copy</button></div>
            <div class="code-body"><pre><code><span class="c-kw">curl</span> -X GET https://api.kesararamwithdigital.tech/api/v1/reports/inventory-valuation \
  -H <span class="c-str">"Authorization: Bearer &lt;manager_token&gt;"</span></code></pre></div>
          </div>
        </div>
      </div>
    </section>

    <!-- SECTION: STOCK MOVEMENTS -->
    <section id="stock-movements" class="api-section">
      <div class="section-split">
        <div class="doc-col">
          <div class="section-head">
            <h2 class="section-title">Stock Movements &amp; Opname</h2>
            <div class="section-actions">
              <button class="action-link" onclick="copySectionLlm('stock-movements', this)">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                Copy AI Prompt
              </button>
            </div>
          </div>
          <div class="endpoint-bar">
            <span class="http-pill http-post">POST</span>
            <span class="endpoint-uri">/stock-movements/adjust</span>
            <span class="tier-tag">Manager+</span>
          </div>
          <div class="doc-desc"><p>Adjusts stock levels with immutable before/after audit ledger tracking.</p></div>
        </div>
        <div class="code-col">
          <div class="code-card">
            <div class="code-card-header"><span style="font-size:11px; font-weight:800; color:#94a3b8;">REQUEST</span><button class="copy-code-btn" onclick="copyCodeBlock(this)">Copy</button></div>
            <div class="code-body"><pre><code><span class="c-kw">curl</span> -X POST https://api.kesararamwithdigital.tech/api/v1/stock-movements/adjust \
  -H <span class="c-str">"Authorization: Bearer &lt;manager_token&gt;"</span> \
  -d <span class="c-str">'{ "variant_id": 180, "adjustment_type": "IN", "quantity": 10, "reason": "Restock" }'</span></code></pre></div>
          </div>
        </div>
      </div>
    </section>

    <!-- SECTION: ADMIN MASTER PULSE -->
    <section id="admin-master-pulse" class="api-section">
      <div class="section-split">
        <div class="doc-col">
          <div class="section-head">
            <h2 class="section-title">Admin Master Pulse &amp; APM</h2>
            <div class="section-actions">
              <button class="action-link" onclick="copySectionLlm('admin-master-pulse', this)">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                Copy AI Prompt
              </button>
            </div>
          </div>
          <div class="endpoint-bar">
            <span class="http-pill http-get">GET</span>
            <span class="endpoint-uri">/admin/master-pulse</span>
            <span class="tier-tag">Admin</span>
          </div>
          <div class="doc-desc"><p>System-wide health telemetry, active database connections, latency metrics, and error rates.</p></div>
        </div>
        <div class="code-col">
          <div class="code-card">
            <div class="code-card-header"><span style="font-size:11px; font-weight:800; color:#94a3b8;">REQUEST</span><button class="copy-code-btn" onclick="copyCodeBlock(this)">Copy</button></div>
            <div class="code-body"><pre><code><span class="c-kw">curl</span> -X GET https://api.kesararamwithdigital.tech/api/v1/admin/master-pulse \
  -H <span class="c-str">"Authorization: Bearer &lt;admin_token&gt;"</span></code></pre></div>
          </div>
        </div>
      </div>
    </section>

    <!-- SECTION: AI INTEGRATION CONTRACT -->
    <section id="llm-integration" class="api-section">
      <div class="section-split">
        <div class="doc-col" style="grid-column: span 2;">
          <div class="section-head">
            <h2 class="section-title">AI Agent Integration Contract</h2>
            <div class="section-actions">
              <button class="btn btn-primary" onclick="openLlmModal()">View System Prompt Modal</button>
            </div>
          </div>
          <div class="doc-desc"><p>Optimized prompt instructions for AI coding assistants (ChatGPT, Claude, Cursor, Copilot, DeepSeek).</p></div>
          <div class="code-card">
            <div class="code-card-header">
              <span style="font-size:11px; font-weight:800; color:#94a3b8;">AI SYSTEM PROMPT</span>
              <button class="copy-code-btn" onclick="copyLlmPrompt(this)">Copy AI Contract</button>
            </div>
            <div class="code-body">
              <pre id="llmPromptBlock"><code># SYSTEM INSTRUCTION: DEVELOPER API FRONTEND INTEGRATION
Base URL: https://api.kesararamwithdigital.tech/api/v1
Auth: Authorization: Bearer &lt;token&gt;

1. Always check `response.data.success === true` before reading `response.data.data`.
2. Extract validation errors from `error.detail.fields` to display inline field errors.
3. Attach `X-Idempotency-Key` (UUIDv4) header on all POST /orders/checkout operations.</code></pre>
            </div>
          </div>
        </div>
      </div>
    </section>

  </main>
</div>

<!-- BENTO MODAL -->
<div class="modal-backdrop" id="llmModal" onclick="closeLlmModal(event)">
  <div class="modal-content" onclick="event.stopPropagation()">
    <div class="modal-header">
      <div class="modal-title">AI Frontend Integration System Prompt</div>
      <button class="btn" onclick="closeLlmModal()">Close</button>
    </div>
    <div class="modal-body" id="fullLlmText"># DEVELOPER REST API INTEGRATION SPECIFICATION
Gateway Base URL: https://api.kesararamwithdigital.tech/api/v1
Authentication: Bearer &lt;token&gt; via 'Authorization: Bearer ...'

## RESPONSE ENVELOPE RULE
{
  "success": true | false,
  "status_code": 200 | 201 | 400 | 401 | 403 | 422 | 500,
  "request_id": "req_uuid",
  "data": { ... } | null,
  "error": { "code": "...", "detail": { "fields": [...] } }
}

## FRONTEND RULES
1. Always parse `success` boolean before reading `data`.
2. Extract errors from `error.detail.fields` array to bind inline form input validation messages.
3. Pass `X-Idempotency-Key` (UUIDv4) header on all POST /orders/checkout transactions.</div>
    <div class="modal-footer">
      <button class="btn btn-primary" onclick="copyFullLlmPrompt()">Copy Prompt to Clipboard</button>
    </div>
  </div>
</div>

<!-- TOAST CONTAINER -->
<div class="toast-container" id="toastContainer"></div>

<script>
/* ==========================================================================
   DEVELOPER DOCS JAVASCRIPT ENGINE (SCROLLSPY, SEARCH, TOASTS)
   ========================================================================== */

// 1. Toast Notification Manager
function showToast(title, description = '') {
  const container = document.getElementById('toastContainer');
  if (!container) return;

  const toast = document.createElement('div');
  toast.className = 'toast-item';
  toast.innerHTML = `
    <div class="toast-icon">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
    </div>
    <div class="toast-content">
      <div class="toast-title">${title}</div>
      ${description ? `<div class="toast-desc">${description}</div>` : ''}
    </div>
  `;

  container.appendChild(toast);
  setTimeout(() => {
    toast.remove();
  }, 2200);
}

// 2. Mobile Sidebar Drawer
function toggleMobileSidebar() {
  document.getElementById('sidebarNav')?.classList.toggle('open');
}

// 3. API Version Selector
function switchApiVersion(v) {
  showToast('API Version Changed', `Switched active reference to ${v}`);
}

// 4. Quick Search Filter
document.getElementById('quickSearch')?.addEventListener('input', function(e) {
  const query = e.target.value.toLowerCase().trim();
  const navItems = document.querySelectorAll('.nav-item');
  navItems.forEach(item => {
    const text = item.textContent.toLowerCase();
    item.style.display = (!query || text.includes(query)) ? 'flex' : 'none';
  });
});

// Shortcut ⌘K / Ctrl+K
document.addEventListener('keydown', function(e) {
  if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
    e.preventDefault();
    document.getElementById('quickSearch')?.focus();
  }
});

// 5. Robust Clipboard Engine with Instant Fallback & Button Animation
function fallbackCopyText(text) {
  return new Promise((resolve, reject) => {
    try {
      const textArea = document.createElement('textarea');
      textArea.value = text;
      textArea.setAttribute('readonly', '');
      textArea.style.position = 'fixed';
      textArea.style.top = '0';
      textArea.style.left = '0';
      textArea.style.width = '2em';
      textArea.style.height = '2em';
      textArea.style.padding = '0';
      textArea.style.border = 'none';
      textArea.style.outline = 'none';
      textArea.style.boxShadow = 'none';
      textArea.style.background = 'transparent';
      document.body.appendChild(textArea);
      textArea.focus();
      textArea.select();
      textArea.setSelectionRange(0, 99999);
      const successful = document.execCommand('copy');
      document.body.removeChild(textArea);
      successful ? resolve() : reject(new Error('execCommand failed'));
    } catch (err) {
      reject(err);
    }
  });
}

function copyToClipboard(text) {
  if (navigator.clipboard && window.isSecureContext) {
    return navigator.clipboard.writeText(text).catch(() => fallbackCopyText(text));
  } else {
    return fallbackCopyText(text);
  }
}

function animateButtonSuccess(btn, originalHtml, successText = 'Copied') {
  if (!btn) return;
  btn.classList.add('btn-copied');
  btn.innerHTML = `<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" style="vertical-align:middle; margin-right:4px;"><polyline points="20 6 9 17 4 12"></polyline></svg>${successText}`;
  setTimeout(() => {
    btn.classList.remove('btn-copied');
    btn.innerHTML = originalHtml;
  }, 1800);
}

function copyText(text, btn = null) {
  const originalHtml = btn ? btn.innerHTML : '';
  copyToClipboard(text).then(() => {
    if (btn) animateButtonSuccess(btn, originalHtml, 'Copied');
    showToast('Copied to Clipboard', text.length > 45 ? text.substring(0, 45) + '...' : text);
  }).catch(() => {
    fallbackCopyText(text);
    if (btn) animateButtonSuccess(btn, originalHtml, 'Copied');
    showToast('Copied to Clipboard', 'Text copied successfully');
  });
}

function copyCodeBlock(btn) {
  const container = btn.closest('.code-card, .response-card');
  const codeEl = container ? container.querySelector('pre') : null;
  const code = codeEl ? (codeEl.innerText || codeEl.textContent) : '';
  copyText(code.trim(), btn);
}

// AI Integration Prompt Generator for Specific Section
function copySectionLlm(sectionId, btn = null) {
  const section = document.getElementById(sectionId);
  const title = section.querySelector('.section-title')?.textContent || sectionId;
  const desc = section.querySelector('.doc-desc')?.textContent.trim() || '';
  const uri = section.querySelector('.endpoint-uri')?.textContent || '';
  const method = section.querySelector('.http-pill')?.textContent || '';
  
  const reqCode = section.querySelector('.code-card pre')?.innerText || section.querySelector('.code-card pre')?.textContent || '';
  const resCode = section.querySelector('.response-card pre')?.innerText || section.querySelector('.response-card pre')?.textContent || '';

  const prompt = `# AI INTEGRATION PROMPT: ${title}
You are an expert Frontend AI Assistant. Write clean client-side integration code (TypeScript / React / Vue / Axios) for this endpoint:

## API ENDPOINT SPECIFICATION
- Method: ${method}
- URI: https://api.kesararamwithdigital.tech/api/v1${uri}
- Description: ${desc}
- Auth: Attach 'Authorization: Bearer <token>' header if protected.

## REQUEST EXAMPLE
\`\`\`bash
${reqCode.trim()}
\`\`\`

## EXPECTED RESPONSE ENVELOPE
\`\`\`json
${resCode.trim()}
\`\`\`

## INTEGRATION RULES
1. Check \`response.data.success === true\` before rendering data.
2. If HTTP 422, extract \`error.detail.fields\` array to bind inline form errors.`;

  const originalHtml = btn ? btn.innerHTML : '';
  copyToClipboard(prompt).then(() => {
    if (btn) animateButtonSuccess(btn, originalHtml, 'AI Prompt Copied');
    showToast('AI Prompt Copied', `Prompt for "${title}" copied. Ready for ChatGPT / Claude.`);
  }).catch(() => {
    fallbackCopyText(prompt);
    if (btn) animateButtonSuccess(btn, originalHtml, 'AI Prompt Copied');
    showToast('AI Prompt Copied', `Prompt for "${title}" copied.`);
  });
}

function copyLlmPrompt(btn = null) {
  const codeEl = document.getElementById('llmPromptBlock');
  const prompt = codeEl ? (codeEl.innerText || codeEl.textContent) : '';
  const originalHtml = btn ? btn.innerHTML : '';
  copyToClipboard(prompt).then(() => {
    if (btn) animateButtonSuccess(btn, originalHtml, 'Contract Copied');
    showToast('AI Contract Copied', 'Paste into your AI coding assistant prompt.');
  }).catch(() => {
    fallbackCopyText(prompt);
    if (btn) animateButtonSuccess(btn, originalHtml, 'Contract Copied');
    showToast('AI Contract Copied', 'Paste into your AI coding assistant prompt.');
  });
}

function openLlmModal() { document.getElementById('llmModal').style.display = 'flex'; }
function closeLlmModal() { document.getElementById('llmModal').style.display = 'none'; }

function copyFullLlmPrompt(btn = null) {
  const codeEl = document.getElementById('fullLlmText');
  const text = codeEl ? (codeEl.innerText || codeEl.textContent) : '';
  const originalHtml = btn ? btn.innerHTML : '';
  copyToClipboard(text).then(() => {
    if (btn) animateButtonSuccess(btn, originalHtml, 'Prompt Copied');
    showToast('System Prompt Copied', 'Full AI specification copied to clipboard.');
    closeLlmModal();
  }).catch(() => {
    fallbackCopyText(text);
    if (btn) animateButtonSuccess(btn, originalHtml, 'Prompt Copied');
    showToast('System Prompt Copied', 'Full AI specification copied to clipboard.');
    closeLlmModal();
  });
}

// 6. Scrollspy Active Navigation
let isThrottled = false;
window.addEventListener('scroll', () => {
  if (isThrottled) return;
  isThrottled = true;
  setTimeout(() => { isThrottled = false; }, 50);

  const sections = document.querySelectorAll('.api-section, .bento-hero');
  const scrollPos = window.scrollY + 90;
  let currentActiveId = null;

  sections.forEach(section => {
    const top = section.offsetTop;
    const height = section.offsetHeight;
    if (scrollPos >= top && scrollPos < top + height) {
      currentActiveId = section.getAttribute('id');
    }
  });

  if (currentActiveId) {
    const navItems = document.querySelectorAll('.nav-item');
    navItems.forEach(item => {
      if (item.getAttribute('href') === `#${currentActiveId}`) {
        if (!item.classList.contains('active')) {
          navItems.forEach(i => i.classList.remove('active'));
          item.classList.add('active');
          item.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
        }
      }
    });
  }
});
</script>

</body>
</html>
