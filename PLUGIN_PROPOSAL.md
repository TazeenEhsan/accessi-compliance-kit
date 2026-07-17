# Plugin Proposal: AccessiWoo — WooCommerce Accessibility Compliance

> A self-contained WordPress plugin that helps WooCommerce stores comply with the EU Accessibility Act (EAA) and WCAG 2.1 AA. Zero external dependencies, zero server costs to the developer, freemium model on WordPress.org.

---

## 1. Executive Summary

**Plugin name (working):** AccessiWoo
**Alternative names to consider:** WooA11y, WCAG Compass, AccessGuard for WooCommerce, ShopAccess
**Category:** WooCommerce → Compliance / Accessibility
**License:** GPLv2 or later (required for WordPress.org)
**Business model:** Freemium — free version on WordPress.org, Pro tier sold via Freemius or self-hosted licensing
**Target launch:** MVP in 6–8 weeks of part-time development

**One-sentence pitch:** Scan any WooCommerce store for accessibility violations, auto-fix common ones on the checkout/product/cart pages, and generate the compliance statement that EU law now requires.

---

## 2. Why This Plugin, Why Now

The European Accessibility Act (EAA) became enforceable across all 27 EU member states on **June 28, 2025**. It requires online shops selling to EU consumers to meet **WCAG 2.1 Level AA**. Micro-enterprises (under 10 employees and under €2M turnover) are exempt, but every store above that threshold selling into the EU must comply.

**Existing gap in the market:**

- Generic accessibility plugins (UserWay, One Click Accessibility, Accessibility Widget) use "overlay" widgets that regulators and disability advocates now consider inadequate, and none are WooCommerce-specific.
- Enterprise tools (accessiBe, AudioEye) cost $500–$1000+/year and are overkill for small merchants.
- WooCommerce's own checkout, cart, and account templates have known accessibility issues (missing labels, poor focus states, low-contrast defaults) that no plugin currently targets specifically.
- Merchants don't know the EAA applies to them until they get a complaint. There's a real search-intent audience with legal urgency.

**Why it fits a solo, bootstrapped developer:**

- 100% runs inside the user's own WordPress install — no server, no API costs, no ongoing infrastructure bill
- WCAG 2.1 AA is a stable spec (unlike AI features that change monthly)
- Legal urgency drives organic search traffic — no paid marketing required
- Natural freemium split (scanning + basic fixes free, full-site audit + PDF report + WooCommerce-specific fixes paid)

---

## 3. Target Users

**Primary:** WooCommerce store owners in the EU with more than 10 employees or €2M+ turnover.
**Secondary:** US-based WooCommerce stores concerned about ADA lawsuits (also a growing market).
**Tertiary:** Freelance WordPress developers who manage multiple client stores and want to add accessibility as a service.

---

## 4. Feature List

### 4.1 Free Version (WordPress.org)

**Scanning**
- One-click accessibility scan of any single page (front-end)
- Uses axe-core (MIT-licensed) to detect WCAG 2.1 A and AA violations
- Results grouped by severity: Critical, Serious, Moderate, Minor
- Each violation shows: rule name, affected element (CSS selector), why it fails, how to fix
- Scan the currently-viewed page, or scan by URL input

**Basic auto-fixes (toggleable per fix in Settings)**
- Add fallback `alt` attributes to product images using the product title
- Add missing `<label>` associations on WooCommerce checkout form fields
- Add visible focus states to all buttons and links (via injected CSS)
- Add `aria-label` to icon-only buttons (cart, search, wishlist icons)
- Ensure product prices are announced correctly by screen readers (add `<span class="screen-reader-text">Price:</span>`)
- Fix empty link anchors (e.g. product image links with no accessible name)

**Compliance statement**
- One-click generator that creates a WordPress page titled "Accessibility Statement" pre-populated with an EAA-compliant template
- Template includes: compliance level claimed, known limitations section, contact for accessibility issues, date of last review
- Merchant edits the page normally afterward

**Admin dashboard**
- Summary widget on WP admin dashboard: last scan date, violation count by severity
- Full plugin admin page under WooCommerce → Accessibility

**Notifications**
- Weekly email reminder to run a scan (opt-in)
- Notice on admin bar if the last scan found critical issues

### 4.2 Pro / Extended Version (Paid)

The Pro version turns AccessiWoo from a scanner into a full compliance and remediation system. Every feature listed here still runs 100% inside the merchant's own WordPress install — no external server, no paid API, no ongoing cost to you. Slack/webhook notifications use outgoing HTTP only, WPML/Polylang integrations run locally, and axe-core already ships with WCAG 2.2, Section 508, and EN 301 549 rule sets included.

Features are split across three sub-tiers so a single-store owner isn't paying for agency features they'll never use.

**Tier structure at a glance:**

| Tier | Sites | Ideal for | Suggested price/year |
|---|---|---|---|
| **Pro** | 1 | Single-store merchants | $79 |
| **Business** | 5 | Multi-brand merchants, in-house teams, small agencies | $199 |
| **Agency** | Unlimited + white-label | Agencies, freelancers managing many client stores | $399 |

All Pro features are available in every tier unless marked *(Business+)* or *(Agency only)*.

---

#### A. Advanced Scanning

- **Full-site crawl** — automatically walk the entire store: homepage, all products, categories, cart, checkout, account, custom pages
- **Scheduled scans** — hourly, daily, weekly, or monthly, with configurable off-peak windows
- **Multi-viewport scanning** — run the same scan at mobile (375px), tablet (768px), and desktop (1440px) breakpoints; catches violations that only appear at certain widths
- **Authenticated scanning** — scan pages that require login (My Account, order history, subscription management, downloads)
- **Cart-state scanning** — simulate a filled cart so the checkout page is scanned with real products, not empty
- **Extended rule sets** — WCAG 2.2 AA, Section 508, EN 301 549, and best-practice rules (not just 2.1 AA)
- **Custom rule configuration** — enable/disable individual rules per site with justification notes
- **Violation muting** — mark specific violations as "reviewed, acceptable" with a required justification, so the same false positive doesn't clutter every report
- **Regression detection** — automatic alert when a new scan finds more violations than the previous one (catches content-editors introducing issues)
- **Historical trends** — line chart of violations over time by severity
- **Scan comparison** — diff view between any two scans

#### B. Advanced WooCommerce Auto-Fixes

All 6 free fixes, plus:

- Variable product **variation dropdowns** — announce price and stock changes to screen readers when a user changes size/color
- **Quantity input** accessibility on product and cart pages (proper labels, keyboard controls, live announcements)
- **Mini-cart drawer** — full keyboard navigation, focus trap when open, escape-to-close
- **AJAX live regions** — announce add-to-cart, coupon applied/rejected, quantity updated, and stock warnings to screen readers via `aria-live`
- **Product gallery** — keyboard navigation between images, correct alt text on zoomed images, focus management on lightbox open/close
- **Multi-step checkout indicators** — proper `aria-current` on the current step, announce step changes
- **Payment method radio group** — grouped semantically, keyboard-navigable, clear focus
- **Coupon code errors** — inline error messages announced immediately to screen readers
- **Product review form** — proper label associations, rating input keyboard-accessible
- **Product filter widgets** — accessible attribute/price filters (WooCommerce core + popular filter plugins)
- **Product tabs** (description / additional info / reviews) — proper ARIA tabs pattern, arrow-key navigation
- **Cross-sell / upsell carousels** — pause on focus, keyboard controls, hidden slides properly removed from tab order
- **Skip-to-content link** — auto-injected as the first focusable element
- **ARIA landmarks** — auto-add missing `<main>`, `<nav>`, `<aside>` landmarks where the theme forgot them
- **Empty heading detection & repair** — fix broken heading hierarchy (h1 → h3 skips)
- **Third-party page builder fixes** — targeted fixes for Elementor Pro, Divi, Bricks, Beaver Builder, Oxygen, Breakdance
- **Wishlist / compare plugin fixes** — integrations with YITH Wishlist, TI WooCommerce Wishlist, YITH Compare

#### C. Reporting & Documentation

- **Legal-audit PDF report** — full-site, timestamped, cryptographically signed hash (for legal defensibility), includes screenshots of failing elements, remediation status per violation
- **Developer report** — same data but formatted with code snippets and specific selectors, exportable for handing to a dev team
- **Executive summary PDF** — one-pager for stakeholders showing compliance score, trend, top issues
- **CSV / JSON export** — full violation data for import into ticketing systems (Jira, Linear, Asana)
- **Timestamped audit history** — immutable log of every scan, every remediation, every muted violation with reason (defensible if you ever face a legal complaint)
- **Compliance score card** — 0–100 score based on WCAG level, weighted by severity, trended over time
- **Custom-branded reports** *(Agency only)* — agency logo, colors, and contact info on all PDFs

#### D. Compliance Templates & Statements

- **Multi-jurisdiction statement templates:**
  - EU — European Accessibility Act
  - US — ADA / Section 508
  - Canada — AODA
  - UK — Equality Act 2010
  - Australia — Disability Discrimination Act
- **8+ language translations** of every statement template — EN, DE, FR, ES, IT, NL, PT, PL (community translations for more)
- **PDF statement export** matching each jurisdiction's expected format
- **Statement version history** — every publish is snapshotted so you can prove what was on the site on any given date

#### E. Team & Workflow

- **Violation assignment** — assign specific violations to team members (uses WordPress users)
- **Comments & annotations** on individual violations
- **Remediation status tracking** — Open, In Progress, Fixed, Won't Fix (with required reason)
- **Activity log** — who scanned, who fixed, who muted, when
- **Weekly digest email** — summary of new violations, resolved violations, current score
- **Slack notifications** via outgoing webhook (no Slack API key needed — merchant pastes their webhook URL)
- **Custom webhook** — POST violation data to any URL for integration with Discord, Teams, Zapier self-hosted, n8n, etc.
- **Custom user role** — "Accessibility Auditor" role with read-only access to scans and reports

#### F. Integrations (all local, no external services)

- **WPML** full multilingual support — scan runs across every translated variant of a page
- **Polylang** full support
- **WooCommerce Subscriptions** — accessibility fixes for the subscription management UI
- **WooCommerce Bookings** — accessibility fixes for booking forms and calendar widgets
- **WooCommerce Memberships** — accessibility fixes for member-only content gates
- **WP-CLI commands** — `wp accessiwoo scan`, `wp accessiwoo report` — enables CI/CD integration (staging site scans on every deploy)
- **REST API endpoints** — read scans, trigger scans, fetch reports for external tooling
- **Multisite network support** *(Business+)* — network admin dashboard showing all sub-sites' compliance scores

#### G. Gutenberg / Block Editor Integration

- **Block-level warnings** — sidebar panel in the block editor showing accessibility issues for the current block (missing alt text on Image block, empty heading, low-contrast text, etc.)
- **Pre-publish check** — optional gate that blocks the "Publish" button if critical violations are present (fully overridable)
- **Contextual guidance** — inline tips in the block inspector for accessibility best practices per block type

#### H. Priority Theme Support

Tuned auto-fixes and selector overrides for the themes most WooCommerce merchants actually use:

- Storefront (official WooCommerce theme)
- Astra
- Kadence
- Blocksy
- GeneratePress
- Flatsome
- Woodmart
- Neve
- Botiga
- Divi (Elegant Themes)

Updated within 30 days when any of these ship a major release.

#### I. Automation & Monitoring

- **Scheduled scans** — daily / weekly / monthly, with results emailed to specified addresses
- **Auto-scan on publish** — trigger a scan of any page/product when it's published or updated
- **Regression alerts** — email/Slack when violation count increases week-over-week
- **Critical-violation alerts** — instant notification when a new "critical" severity issue is detected
- **CI/CD ready** — WP-CLI + REST API means merchants can run scans as part of their deploy pipeline and fail builds on regressions

#### J. Agency Features *(Agency tier only)*

- **Full white-label** — replace "AccessiWoo" branding with agency name/logo throughout admin UI
- **Central agency dashboard** (separate hosted-anywhere plugin add-on for the agency's own WordPress install) showing compliance scores across every client site
- **Bulk actions** across managed sites (trigger scans, apply setting changes, push fix updates)
- **Client-facing reports** with agency branding, no mention of AccessiWoo
- **Auditor role for clients** — give clients read-only view of scans/reports without exposing the plugin's admin
- **Reseller license option** — resell white-labeled licenses to your own clients

#### K. Priority Support

| Tier | Channel | Response SLA |
|---|---|---|
| Pro | Email | 48 hours (business days) |
| Business | Email | 24 hours (business days) |
| Agency | Email + private Slack | 12 hours (business days) + monthly roadmap call |

---

**A note on scope:** everything in section 4.2 is technically buildable but you should not build it all before launch. See section 12 (Post-Launch Roadmap) — ship Pro with categories A + B + C + D (scanning, WC fixes, reports, statements) first. Everything else is a Pro *expansion* released over months 6–18 to drive renewals and word-of-mouth.

### 4.3 Explicitly Out of Scope (Do Not Build)

- Overlay widgets ("accessibility toolbar" that lets users toggle high-contrast mode etc.) — these are widely criticized and don't achieve legal compliance on their own
- Live remediation via JavaScript that hides violations rather than fixing them
- Third-party AI content generation (needs API keys, out of our zero-cost constraint)
- Automated legal-risk scoring — creates liability

---

## 5. Technical Architecture

### 5.1 Tech Stack (all free, all bundled)

| Layer | Technology | License | Notes |
|---|---|---|---|
| Core plugin | PHP 7.4+ (targeting 8.0+) | GPLv2 | Standard WordPress plugin |
| Scanner | axe-core (JavaScript) | MIT | Bundled, no CDN dependency |
| Admin UI | React (`@wordpress/element`) + WP components | GPL | Ships with WordPress core |
| PDF generation | DomPDF | LGPL 2.1 | Bundled via Composer, runs locally |
| Database | WordPress `$wpdb` + custom tables | — | For scan history |
| Licensing (Pro) | Freemius SDK or EDD Software Licensing | Various | Freemius = no upfront cost |
| Build tools | `@wordpress/scripts` | GPL | Official WP build tooling |
| Testing | PHPUnit + Jest | Various | Optional but recommended |

**Nothing calls an external server. Nothing costs you money.**

### 5.2 File & Folder Structure

```
accessiwoo/
├── accessiwoo.php                 # Main plugin file (headers, bootstrap)
├── uninstall.php                  # Cleanup on plugin deletion
├── composer.json                  # DomPDF and dev dependencies
├── package.json                   # JS build config
├── webpack.config.js              # Extends @wordpress/scripts
├── readme.txt                     # WordPress.org readme format
├── LICENSE.txt                    # GPLv2
│
├── src/
│   ├── Plugin.php                 # Main plugin class (singleton)
│   ├── Activator.php              # Runs on activation (create tables)
│   ├── Deactivator.php            # Runs on deactivation
│   │
│   ├── Admin/
│   │   ├── AdminMenu.php          # Registers admin pages
│   │   ├── DashboardWidget.php    # WP dashboard summary widget
│   │   ├── SettingsPage.php       # Settings page controller
│   │   └── ScanPage.php           # Scan results page controller
│   │
│   ├── Scanner/
│   │   ├── ScanController.php     # AJAX handlers for scans
│   │   ├── ScanStorage.php        # Save/retrieve scan results from DB
│   │   ├── ScanCrawler.php        # (Pro) Full-site crawler
│   │   └── ViolationParser.php    # Normalize axe-core output
│   │
│   ├── Fixes/
│   │   ├── FixManager.php         # Registers active fixes
│   │   ├── AbstractFix.php        # Base class each fix extends
│   │   ├── ProductImageAltFix.php
│   │   ├── CheckoutLabelsFix.php
│   │   ├── FocusStatesFix.php
│   │   ├── IconButtonAriaFix.php
│   │   ├── PriceScreenReaderFix.php
│   │   └── EmptyLinkAnchorFix.php
│   │
│   ├── Statement/
│   │   ├── StatementGenerator.php # Creates the WP page
│   │   ├── templates/
│   │   │   ├── en.php
│   │   │   ├── de.php
│   │   │   └── ...
│   │
│   ├── Pro/                       # Loaded only if Pro license active
│   │   ├── CrawlerScheduler.php
│   │   ├── PdfReportGenerator.php
│   │   ├── AdvancedFixes/
│   │   │   ├── VariationDropdownFix.php
│   │   │   ├── QuantityInputFix.php
│   │   │   ├── MiniCartFix.php
│   │   │   └── ...
│   │
│   └── Utils/
│       ├── Logger.php
│       ├── Options.php            # Wrapper around get_option/update_option
│       └── Capabilities.php       # Who can access what
│
├── assets/
│   ├── js/
│   │   ├── admin.js               # Compiled admin React app
│   │   ├── scanner.js             # Front-end scan runner (bundles axe-core)
│   │   └── src/                   # React source
│   │       ├── admin/
│   │       │   ├── App.jsx
│   │       │   ├── ScanResults.jsx
│   │       │   ├── Settings.jsx
│   │       │   └── Dashboard.jsx
│   │       └── scanner/
│   │           └── runScan.js
│   ├── css/
│   │   ├── admin.css
│   │   └── frontend-fixes.css     # Injected fixes (focus states etc.)
│   └── images/
│
├── vendor/                        # Composer (DomPDF etc.)
├── node_modules/                  # (gitignored)
├── build/                         # (gitignored) compiled JS/CSS output
│
├── languages/                     # .pot files for i18n
│
└── tests/
    ├── phpunit/
    └── js/
```

### 5.3 Database Schema

**Custom table: `wp_accessiwoo_scans`**

| Column | Type | Notes |
|---|---|---|
| `id` | BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY | |
| `scan_type` | VARCHAR(20) | 'single', 'crawl' |
| `url` | TEXT | Page scanned |
| `started_at` | DATETIME | |
| `completed_at` | DATETIME NULL | |
| `status` | VARCHAR(20) | 'running', 'complete', 'failed' |
| `violations_json` | LONGTEXT | Normalized axe-core output |
| `summary_json` | TEXT | Counts by severity for quick display |
| `triggered_by` | BIGINT UNSIGNED | WP user ID |

**Options stored via `wp_options`:**

- `accessiwoo_settings` — plugin configuration array
- `accessiwoo_active_fixes` — which fixes are enabled
- `accessiwoo_license` — Pro license data (via Freemius or custom)
- `accessiwoo_last_scan_id` — pointer for dashboard widget

### 5.4 WordPress & WooCommerce Hooks You'll Use

**Actions:**
- `plugins_loaded` — bootstrap
- `init` — register post types, taxonomies (none needed initially)
- `admin_init` — settings API registration
- `admin_menu` — add plugin admin pages
- `admin_enqueue_scripts` — load admin JS/CSS
- `wp_enqueue_scripts` — load frontend fix CSS
- `wp_ajax_accessiwoo_run_scan` — AJAX endpoint for scans
- `wp_ajax_accessiwoo_save_settings` — settings save
- `woocommerce_before_checkout_form` — hook point for checkout fixes
- `woocommerce_after_add_to_cart_button` — hook point for product fixes

**Filters:**
- `wp_get_attachment_image_attributes` — inject alt fallbacks
- `woocommerce_form_field_args` — fix form labels
- `woocommerce_locate_template` — potentially override problem templates (use sparingly)
- `the_content` — inject accessibility statement content
- `body_class` — add class when fixes are active

**REST API (optional, for future SPA admin):**
- `register_rest_route( 'accessiwoo/v1', '/scans', ... )`

### 5.5 How Scanning Works (Data Flow)

1. Admin clicks "Scan this page" in the admin bar or plugin page
2. Plugin opens a hidden iframe pointing at the target URL with a query string flag `?accessiwoo_scan=1`
3. Frontend scanner script (loaded only when that flag is present AND user is admin) runs axe-core on the loaded page
4. Scanner posts results back to the parent window via `postMessage`
5. Parent window sends results to `admin-ajax.php` action `accessiwoo_run_scan`
6. PHP saves to the `wp_accessiwoo_scans` table
7. Admin UI polls or receives result and renders it

**No data ever leaves the merchant's server.**

### 5.6 How Auto-Fixes Work

Each fix is a class extending `AbstractFix` with:
- `is_enabled()` — checks settings
- `applies_to()` — returns array of contexts ('product', 'checkout', 'cart', 'global')
- `register()` — hooks into the appropriate WP/WC filters/actions

The `FixManager` loops on `init`, checks each fix's enabled state, and calls `register()`. This keeps fixes independent and toggleable.

### 5.7 Licensing & Pro Loading

- Use Freemius SDK (free to integrate, they take a revenue share only on paid sales — matches your zero-upfront-cost constraint)
- The `src/Pro/` folder is only autoloaded when a valid license is detected
- All Pro classes must be optional — the free plugin works fully without them

---

## 6. Development Phases

### Phase 0 — Setup (Week 1)

- Set up plugin skeleton with `@wordpress/scripts`
- Add Composer, install DomPDF
- Create GitHub repo (private initially)
- Set up PSR-4 autoloading
- Write main plugin file with headers, activation/deactivation hooks
- Create the scans database table on activation

### Phase 1 — Single-Page Scanner (Weeks 2–3)

- Integrate axe-core in the scanner JS bundle
- Build the AJAX scan endpoint
- Build a basic admin page that shows scan results in a table
- Store results in DB
- Ship it internally, test on 5–10 real WooCommerce sites

### Phase 2 — Auto-Fixes (Weeks 3–5)

- Build the `FixManager` and `AbstractFix` base class
- Implement all 6 free-tier fixes
- Add settings page with toggles
- Test each fix doesn't break common themes (Storefront, Astra, Kadence)

### Phase 3 — Accessibility Statement (Week 6)

- Build the statement generator
- Create the EN template (add more languages later)
- Wire up the "Create statement page" button

### Phase 4 — Polish & Free Version Launch (Weeks 7–8)

- Write `readme.txt` in the WordPress.org format
- Take screenshots and record a demo video
- Set up plugin support forum readiness (docs page, FAQ)
- Submit to WordPress.org — review takes 1–14 days typically
- Announce on WooCommerce subreddits, WP Tavern, Twitter/X, LinkedIn

### Phase 5 — Pro Development (Post-launch, when you have ~500+ active installs)

- Integrate Freemius
- Build the full-site crawler
- Build the PDF report generator
- Build advanced WooCommerce fixes
- Launch Pro tier

---

## 7. Monetization Plan

**Freemius revenue share:** 30% of first year, 20% ongoing (worth it for zero upfront cost and no need to run your own licensing server).

**Pricing (recommended, aligned with market and matches section 4.2 tiers):**
- **Pro** (single site): $79/year
- **Business** (5 sites): $199/year
- **Agency** (unlimited sites + white-label + reseller): $399/year
- Lifetime deals: skip for the first year, use only if growth stalls

**Conservative revenue projection:**
- 10,000 active free installs in year 1 is realistic for a compliance-driven plugin with SEO focus
- 1–2% free-to-paid conversion is typical = 100–200 paying customers
- Average $80/customer/year (mix of tiers) = $8,000–$16,000 year 1
- Year 2 doubles or triples as compounding kicks in

**Note:** These are order-of-magnitude estimates from public data on comparable freemium WP plugins (e.g. WPForms, WP Rocket at earlier stages). Your actual numbers depend heavily on execution and SEO.

---

## 8. Go-To-Market

**Content strategy (do this yourself, no ad spend):**
- Blog on your own domain (or dev.to / Medium initially):
  - "Is your WooCommerce store compliant with the EU Accessibility Act?"
  - "WCAG 2.1 AA for WooCommerce: A complete checklist"
  - "How to write an accessibility statement for your online store"
  - "The 10 most common WooCommerce accessibility violations"
- Target long-tail SEO around "WooCommerce accessibility", "EAA compliance WooCommerce", "WCAG WooCommerce", "ADA WooCommerce"
- Cross-post to WPTavern, WP Mainline (they take submissions)

**Distribution:**
- WordPress.org plugin directory (primary — free traffic)
- Post launch on: r/woocommerce, r/wordpress, WordPress Facebook groups, LinkedIn (accessibility community is active there)
- Reach out to accessibility consultants — offer them the agency tier license for testimonials

**Reviews (critical for WP.org ranking):**
- After 30 days of usage, prompt users politely for a review
- Never buy reviews or fake them

---

## 9. Risks & Mitigations

| Risk | Mitigation |
|---|---|
| Automattic ships a competing feature natively into WooCommerce | Unlikely for compliance-specific tooling in the near term; even if so, first-mover advantage + specialization holds |
| Legal changes to WCAG or EAA scope | Track W3C and European Commission updates; WCAG spec changes are slow (WCAG 2.2 already stable, 3.0 years out) |
| WordPress.org review rejection | Follow their plugin guidelines strictly, no obfuscated code, no external calls in free version, no sponsored content |
| Users blame the plugin for breaking their site's layout | Every fix must be individually toggleable; ship with all off by default until user opts in per fix |
| Support burden as free installs grow | Detailed docs + community forum + template responses; no email support on free tier |
| False positives from axe-core | axe-core has excellent accuracy but always show violations as "detected issues" not "confirmed violations" — leave interpretation to the merchant |

---

## 10. How to Use This Document with Claude Code

This document is designed to be the source of truth you feed to Claude Code in VS Code. Suggested workflow:

1. Create a new directory, drop this file in as `PROPOSAL.md`
2. In VS Code, open Claude Code and give it a starting prompt like:
   > "Read PROPOSAL.md. Set up the plugin skeleton described in section 5.2. Start with the main plugin file, composer.json, package.json, and the activation logic that creates the database table from section 5.3."
3. Work section by section. After the skeleton, ask Claude Code to build Phase 1 (scanner), then Phase 2 (fixes), etc.
4. For each fix in section 4.1, give Claude Code the specific fix name and the hook from section 5.4, and ask it to implement one at a time
5. Keep this document updated as you make decisions — it becomes your spec, your onboarding doc, and (later) your investor/partner explainer

**Good prompts to use with Claude Code:**

- "Implement the ProductImageAltFix class per PROPOSAL.md section 4.1. It should extend AbstractFix, hook into `wp_get_attachment_image_attributes`, and use the product title as fallback when alt is empty. Only apply on product pages."
- "Set up the admin page under WooCommerce → Accessibility with a React shell. Follow the file structure in section 5.2."
- "Write the axe-core integration in assets/js/src/scanner/runScan.js. It should run axe.run() on document, format results, and postMessage to the parent window."
- "Write a phpunit test for FixManager that verifies only enabled fixes are registered."

---

## 11. Success Criteria for the MVP

Before you launch, the plugin should:

- [ ] Install and activate on a fresh WordPress 6.5+ / WooCommerce 8.0+ install without errors
- [ ] Scan any front-end page and return a list of violations grouped by severity
- [ ] Save scan results to a database table and show history in the admin
- [ ] Have all 6 free-tier fixes working, individually toggleable, defaulting to OFF
- [ ] Generate an accessibility statement page with one click
- [ ] Show a dashboard widget with the last scan summary
- [ ] Pass WordPress.org plugin review guidelines (no external calls, sanitized inputs, escaped outputs, GPL-compatible dependencies)
- [ ] Not break the front-end on Storefront, Astra, or Kadence themes with all fixes enabled
- [ ] Have a `readme.txt` that renders correctly in WordPress.org's preview tool

---

## 12. Post-Launch Roadmap (First 12 Months)

- **Month 1–3:** Bug fixes, support, respond to every review, iterate on onboarding based on user confusion points
- **Month 3–6:** Launch Pro tier once you hit ~500 active installs. Start with full-site crawler and PDF report — the two features users will explicitly ask for.
- **Month 6–9:** Add advanced WooCommerce fixes (variations, mini-cart, checkout steps). These are your Pro differentiators.
- **Month 9–12:** Multi-language, agency white-label, WPML/Polylang integration. By this point you have data on which Pro features drive the most upgrades.

**Second plugin timing:** Don't start plugin #2 until AccessiWoo is generating steady revenue and the support load is under control. When you do, the natural adjacent plugin from your audience is a **WooCommerce Returns/RMA portal** — same buyer, same "compliance/professionalism" pitch, and you can cross-sell to your existing user base.

---

*End of proposal. Update this document as decisions change — treat it as living spec.*
