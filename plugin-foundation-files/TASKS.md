# TASKS.md — Accessi Compliance Kit Implementation Tasks

Source of truth: `PLUGIN_PROPOSAL.md`. Rules: `AI_RULES.md`. Design detail: `docs/`.
Each task is sized for roughly 10–20 minutes. Work top to bottom within a phase; phases follow proposal §6. Check a box only when the task is done **and verified** (file exists, code runs, test passes — whichever applies).

Legend: tasks marked **(Pro)** are Phase 5+ and must not be started before the free version launches (proposal §6, Phase 5 gate: ~500+ active installs).

---

## Phase 0 — Setup (proposal §6, Week 1)

### 0.1 Repository & Tooling

- [x] Create the plugin folder skeleton: empty `src/`, `assets/js/src/`, `assets/css/`, `assets/images/`, `languages/`, `tests/phpunit/`, `tests/js/` directories per proposal §5.2
- [x] Write `.gitignore` covering `node_modules/`, `build/`, `vendor/`, OS/editor files
- [x] Write `LICENSE.txt` (GPLv2 full text)
- [x] Write `composer.json`: project metadata, PHP 7.4 platform requirement, PSR-4 autoload `AccessiComplianceKit\` → `src/`, require `dompdf/dompdf`, require-dev PHPUnit
- [x] Run `composer install`; verify `vendor/autoload.php` exists and autoloads a dummy class from `src/`
- [x] Write `package.json` with `@wordpress/scripts` dev dependency and `build`/`start` scripts for two entry points (admin app, scanner)
- [x] Write `webpack.config.js` extending `@wordpress/scripts` default config with entries `assets/js/src/admin/index.js` and `assets/js/src/scanner/index.js` outputting to `build/`
- [x] Run `npm install` and a first `npm run build` with placeholder entry files; verify `build/` output
- [x] Add `axe-core` as an npm dependency (bundled, no CDN — AI_RULES §2.1); verify it appears in the scanner bundle

### 0.2 Plugin Bootstrap

- [x] Write `accessi-compliance-kit.php` main file: plugin headers (name, description, version, requires WP 6.5, requires PHP 7.4, license GPLv2, text domain `accessi-compliance-kit`), `ABSPATH` guard, define constants (`ACCESSI_COMPLIANCE_KIT_VERSION`, `ACCESSI_COMPLIANCE_KIT_FILE`, `ACCESSI_COMPLIANCE_KIT_PATH`, `ACCESSI_COMPLIANCE_KIT_URL`), require Composer autoloader
- [x] Write `src/Plugin.php` singleton: `instance()`, `boot()` hooked on `plugins_loaded`, empty service-registration method stubs (admin, scanner, fixes, statement)
- [x] Add a WooCommerce-active check in `Plugin.php` with an admin notice when WooCommerce is missing (plugin targets WC 8.0+, proposal §11)
- [x] Write `src/Activator.php` stub + `src/Deactivator.php` stub; register `register_activation_hook` / `register_deactivation_hook` in `accessi-compliance-kit.php`
- [x] Implement table creation in `Activator.php`: `wp_accessi_compliance_kit_scans` via `dbDelta()` exactly per proposal §5.3 schema (see docs/database.md), store `accessi_compliance_kit_db_version` option
- [x] Implement default options seeding in `Activator.php`: `accessi_compliance_kit_settings` and `accessi_compliance_kit_active_fixes` (all fixes OFF per AI_RULES §2.3)
- [x] Write `uninstall.php`: drop the scans table and delete all `accessi_compliance_kit_*` options (guarded by `WP_UNINSTALL_PLUGIN`)
- [x] Write `src/Utils/Options.php`: typed getters/setters wrapping `get_option`/`update_option` for the four option keys in proposal §5.3
- [x] Write `src/Utils/Capabilities.php`: capability constants and `can_scan()` / `can_manage_settings()` helpers (default `manage_options`)
- [x] Write `src/Utils/Logger.php`: thin wrapper around `error_log` gated by `WP_DEBUG`, with a `accessi_compliance_kit_` prefix
- [x] Activate the plugin on the local dev site; verify: no errors, table created with correct columns, default options present
- [x] Set up `tests/phpunit/` bootstrap (WP test suite or Brain Monkey — pick per docs/coding-guidelines.md) and one smoke test asserting `Plugin::instance()` returns a singleton

## Phase 1 — Single-Page Scanner (proposal §6, Weeks 2–3)

### 1.1 Front-End Scanner Runner

- [x] Write `assets/js/src/scanner/runScan.js`: import axe-core, run `axe.run(document)`, format results (rule id, impact, description, help text, nodes with CSS selectors + HTML snippets), `postMessage` payload to `window.parent` (proposal §5.5 step 4, §10 prompt 3)
- [x] Write the scanner entry `assets/js/src/scanner/index.js`: run on DOM ready, include a origin/handshake token read from a localized variable so the parent can verify messages (docs/security.md §5)
- [x] Add conditional enqueue in a new `src/Scanner/ScannerAssets.php` (registered from `Plugin.php`): load scanner bundle on front-end **only** when `?accessi_compliance_kit_scan=1` AND `Capabilities::can_scan()` (proposal §5.5 step 3)
- [x] Localize scanner script with the handshake token + admin origin; verify manually that the script loads only for admins with the flag

### 1.2 Scan Persistence (PHP)

- [x] Write `src/Scanner/ViolationParser.php`: normalize raw axe-core JSON into the stored shape (violations array + summary counts by severity: critical/serious/moderate/minor per proposal §4.1)
- [x] Unit-test `ViolationParser` with a fixture of real axe-core output (empty results, mixed severities)
- [x] Write `src/Scanner/ScanStorage.php` part 1: `create_scan( $url, $type, $user_id )` inserting a `running` row, `complete_scan( $id, $violations, $summary )`, `fail_scan( $id )` — all via `$wpdb->prepare` (docs/database.md)
- [x] Write `src/Scanner/ScanStorage.php` part 2: `get_scan( $id )`, `get_recent_scans( $limit, $offset )`, `get_last_scan()` (updates/reads `accessi_compliance_kit_last_scan_id`), JSON decode helpers
- [x] Unit-test `ScanStorage` round-trip (insert → complete → fetch)

### 1.3 AJAX Endpoints

- [ ] Write `src/Scanner/ScanController.php`: register `wp_ajax_accessi_compliance_kit_run_scan`; handler validates nonce + capability, sanitizes URL and payload, runs `ViolationParser`, saves via `ScanStorage`, returns JSON summary (proposal §5.4, §5.5 steps 5–6)
- [ ] Add a second handler `wp_ajax_accessi_compliance_kit_get_scan` (fetch one scan's stored results for the results UI) with nonce + capability checks
- [ ] Add URL validation for scan targets: same-site origin only (`esc_url_raw` + host check against `home_url()`) — prevents scanning arbitrary external URLs (docs/security.md §6)
- [ ] Test the endpoints with a REST client / curl: valid nonce succeeds, missing nonce and non-admin fail with proper error codes

### 1.4 Admin Scan UI (React)

- [ ] Write `src/Admin/AdminMenu.php`: register submenu page under WooCommerce → "Accessibility" (proposal §4.1), page callback renders a root `<div id="accessi-compliance-kit-admin">`
- [ ] Write `src/Admin/ScanPage.php`: enqueue admin bundle + `admin.css` on the plugin page only (`admin_enqueue_scripts`), `wp_localize_script` with ajaxurl, nonces, current-site URL, severity labels
- [ ] Write React shell `assets/js/src/admin/App.jsx` + entry `index.js`: tab layout (Scan | History | Settings) using `@wordpress/components`
- [ ] Write `assets/js/src/admin/ScanRunner.jsx` (iframe orchestration): URL input (default: home page), "Scan this page" button, create hidden iframe with `?accessi_compliance_kit_scan=1`, listen for `postMessage`, verify handshake token, show progress state (proposal §5.5 steps 1–4)
- [ ] Wire ScanRunner results to the `accessi_compliance_kit_run_scan` AJAX action; handle success/error/timeout (e.g. 60s no-message timeout → failed state)
- [ ] Write `assets/js/src/admin/ScanResults.jsx`: violations grouped by severity (Critical, Serious, Moderate, Minor), each row shows rule name, affected element selector, why it fails, how to fix (proposal §4.1) — worded as "detected issues" (AI_RULES §2.4)
- [ ] Add expandable violation detail (HTML snippet, help URL from axe-core data) to `ScanResults.jsx`
- [ ] Write scan History tab: table of past scans from a new `wp_ajax_accessi_compliance_kit_get_scans` list endpoint (date, URL, status, severity counts), click-through to results view
- [ ] Implement the `accessi_compliance_kit_get_scans` list AJAX handler in `ScanController.php` (nonce + capability + pagination)
- [ ] Write `assets/css/admin.css`: base layout/severity color coding consistent with WP admin styles
- [ ] End-to-end test on the dev site: scan the shop page, see grouped violations, row saved in DB, history lists it

### 1.5 Scan Entry Points

- [ ] Add "Scan this page" admin-bar node on front-end pages for users with scan capability, linking to the admin page with the current URL pre-filled (proposal §5.5 step 1)
- [ ] Verify the URL-input scan path: paste any same-site URL into the Scan tab and run it (proposal §4.1 "scan by URL input")

## Phase 2 — Auto-Fixes (proposal §6, Weeks 3–5)

### 2.1 Fix Framework

- [ ] Write `src/Fixes/AbstractFix.php`: abstract base with `id()`, `label()`, `description()`, `is_enabled()` (reads `accessi_compliance_kit_active_fixes` via Options), `applies_to()` returning contexts ('product', 'checkout', 'cart', 'global'), abstract `register()` (proposal §5.6)
- [ ] Write `src/Fixes/FixManager.php`: hard-coded registry of the 6 free fix classes, loops on `init`, instantiates each, calls `register()` only when `is_enabled()` (proposal §5.6)
- [ ] Add `body_class` filter in `FixManager`: append `accessi-compliance-kit-fixes-active` (plus per-fix classes) when any fix is enabled (proposal §5.4)
- [ ] PHPUnit test: `FixManager` registers only enabled fixes (proposal §10 prompt 4)

### 2.2 The Six Free Fixes (one task each — see docs/frontend.md for specs)

- [ ] `ProductImageAltFix.php` — filter `wp_get_attachment_image_attributes`; when alt is empty on product images, fall back to the product title; only on product contexts (proposal §4.1, §10 prompt 1)
- [ ] Unit-test `ProductImageAltFix` (empty alt gets title, existing alt untouched)
- [ ] `CheckoutLabelsFix.php` — filter `woocommerce_form_field_args` to ensure checkout fields have proper `<label>` associations (proposal §4.1, §5.4)
- [ ] `FocusStatesFix.php` — enqueue `assets/css/frontend-fixes.css` (via `wp_enqueue_scripts`) adding visible focus states to all buttons and links; write the CSS with `:focus-visible` and a high-contrast outline
- [ ] `IconButtonAriaFix.php` — add `aria-label` to icon-only buttons (cart, search, wishlist icons); implement via targeted front-end JS/output buffering approach per docs/frontend.md decision
- [ ] `PriceScreenReaderFix.php` — prepend `<span class="screen-reader-text">Price:</span>` to WooCommerce price output so screen readers announce prices correctly (proposal §4.1)
- [ ] `EmptyLinkAnchorFix.php` — give accessible names to empty link anchors (e.g. product image links), e.g. inject screen-reader text with the product title
- [ ] Unit-test the remaining five fixes (one focused test file each; can batch 1 test per task run)

### 2.3 Settings

- [ ] Register settings via `admin_init` in `src/Admin/SettingsPage.php`: sanitization callback for `accessi_compliance_kit_settings` and `accessi_compliance_kit_active_fixes` (whitelist known fix IDs, booleans only)
- [ ] Add `wp_ajax_accessi_compliance_kit_save_settings` handler (nonce + capability + sanitize) per proposal §5.4
- [ ] Write `assets/js/src/admin/Settings.jsx`: one ToggleControl per fix (label + description + context badge), all default OFF, save via AJAX with success/error notice (proposal §9: individually toggleable, off by default)
- [ ] Add email-reminder opt-in toggle to Settings UI (stores in `accessi_compliance_kit_settings`; used in Phase 4 notifications)
- [ ] Manual test matrix: enable each fix one at a time on Storefront; confirm the fix applies and nothing visually breaks (proposal §6 Phase 2)
- [ ] Repeat quick fix smoke-test on Astra and Kadence (proposal §6 Phase 2; full matrix again in Phase 4)

## Phase 3 — Accessibility Statement (proposal §6, Week 6)

- [ ] Write `src/Statement/templates/en.php`: EAA-compliant statement template with placeholders — compliance level claimed, known limitations section, contact for accessibility issues, date of last review (proposal §4.1)
- [ ] Write `src/Statement/StatementGenerator.php`: `generate()` creates a WordPress page titled "Accessibility Statement" populated from the template with site name/contact/date substitutions (`wp_insert_post`, draft-or-publish per settings decision in docs/admin.md)
- [ ] Handle re-generation: if a statement page already exists (store page ID in `accessi_compliance_kit_settings`), warn instead of duplicating; offer "create new" explicitly
- [ ] Add `wp_ajax_accessi_compliance_kit_generate_statement` handler (nonce + capability) returning the created page's edit link
- [ ] Add "Create statement page" button + status card (exists / not created, link to edit) to the admin React app (proposal §6 Phase 3)
- [ ] Unit-test `StatementGenerator` (page created with expected title/content, no duplicate on second call)

## Phase 4 — Polish & Free Launch (proposal §6, Weeks 7–8)

### 4.1 Dashboard Widget & Notifications

- [ ] Write `src/Admin/DashboardWidget.php`: WP dashboard summary widget — last scan date, violation count by severity, link to full page (proposal §4.1, §11)
- [ ] Add admin-bar notice when the last scan found critical issues (proposal §4.1 "Notifications")
- [ ] Implement weekly email reminder: WP-Cron event scheduled only when the opt-in setting is on, unscheduled on deactivation/opt-out; email links to the scan page (proposal §4.1)
- [ ] Test cron scheduling/unscheduling (toggle opt-in, deactivate plugin, check `wp cron event list`)

### 4.2 i18n & Hardening

- [ ] Sweep all PHP/JS for untranslated strings; load text domain; generate `languages/accessi-compliance-kit.pot` (`wp i18n make-pot`)
- [ ] Security pass 1: verify every AJAX handler has nonce + capability + sanitization + escaped output (checklist in docs/security.md §9)
- [ ] Security pass 2: verify all `$wpdb` calls use `prepare()`, all templates escape output, every PHP file has the ABSPATH guard
- [ ] Run WPCS (`phpcs` with WordPress ruleset) across `src/`; fix violations
- [ ] Run Plugin Check (WordPress.org's plugin-check tool) and fix everything it flags

### 4.3 Release Assets & Submission

- [ ] Write `readme.txt` in WordPress.org format: short/long description, installation, FAQ, screenshots section, changelog; validate in the WP.org readme preview tool (proposal §11)
- [ ] Fresh-install test: new WP 6.5+ / WC 8.0+ site, install + activate without errors, run through every MVP success criterion in proposal §11 and record results
- [ ] Full theme regression: all 6 fixes enabled simultaneously on Storefront, Astra, Kadence — front-end must not break (proposal §11)
- [ ] Take WP.org screenshots (scan results, settings, statement button, dashboard widget) and store in the assets-for-wp.org folder
- [ ] Record the demo video (script: scan → review violations → enable a fix → generate statement) (proposal §6 Phase 4)
- [ ] Prepare docs page + FAQ content for support-forum readiness (proposal §6 Phase 4)
- [ ] Build the distributable zip (build JS, `composer install --no-dev`, exclude dev files); test-install the zip on a clean site
- [ ] Submit to WordPress.org; log submission date (review takes 1–14 days, proposal §6 Phase 4)
- [ ] Draft launch announcements for r/woocommerce, r/wordpress, WP Tavern, Twitter/X, LinkedIn (proposal §6 Phase 4, §8)

---

## Phase 5 — Pro (Post-launch gate: ~500+ active installs — proposal §6 Phase 5, §12)

Do not start these before the gate. Ship Pro with categories A–D first (proposal §4.2 scope note); remaining categories are months 6–18 expansions.

### 5.1 Licensing & Pro Loading

- [ ] **(Pro)** Integrate Freemius SDK; wrap activation in the standard Freemius init (proposal §5.7, §7)
- [ ] **(Pro)** Implement guarded Pro autoloading in `Plugin.php`: `src/Pro/` classes load only with a valid license; free paths never reference Pro classes directly (AI_RULES §8)
- [ ] **(Pro)** Configure the three tiers in Freemius: Pro $79 / Business $199 / Agency $399 per year (proposal §4.2, §7)

### 5.2 Category A — Advanced Scanning (proposal §4.2.A)

- [ ] **(Pro)** Design crawl queue schema + `ScanCrawler.php` URL discovery (homepage, products, categories, cart, checkout, account, custom pages)
- [ ] **(Pro)** Implement crawl execution loop with batching + resumability
- [ ] **(Pro)** `CrawlerScheduler.php`: hourly/daily/weekly/monthly schedules with off-peak windows
- [ ] **(Pro)** Multi-viewport scanning (375px / 768px / 1440px)
- [ ] **(Pro)** Authenticated scanning (logged-in pages: My Account, orders, subscriptions, downloads)
- [ ] **(Pro)** Cart-state scanning (simulate filled cart before checkout scan)
- [ ] **(Pro)** Extended rule sets: WCAG 2.2 AA, Section 508, EN 301 549, best-practice
- [ ] **(Pro)** Custom rule enable/disable per site with justification notes
- [ ] **(Pro)** Violation muting with required justification
- [ ] **(Pro)** Regression detection alert (new scan worse than previous)
- [ ] **(Pro)** Historical trends chart (violations over time by severity)
- [ ] **(Pro)** Scan comparison diff view

### 5.3 Category B — Advanced WooCommerce Fixes (proposal §4.2.B)

- [ ] **(Pro)** Variation dropdown announcements (price/stock changes via screen reader)
- [ ] **(Pro)** Quantity input accessibility (labels, keyboard, live announcements)
- [ ] **(Pro)** Mini-cart drawer (keyboard nav, focus trap, escape-to-close)
- [ ] **(Pro)** AJAX live regions (add-to-cart, coupon, quantity, stock via `aria-live`)
- [ ] **(Pro)** Product gallery keyboard nav + lightbox focus management
- [ ] **(Pro)** Multi-step checkout indicators (`aria-current`, step announcements)
- [ ] **(Pro)** Payment method radio group semantics
- [ ] **(Pro)** Coupon error announcements
- [ ] **(Pro)** Product review form accessibility
- [ ] **(Pro)** Product filter widget fixes
- [ ] **(Pro)** Product tabs ARIA pattern + arrow keys
- [ ] **(Pro)** Cross-sell/upsell carousel fixes
- [ ] **(Pro)** Skip-to-content link injection
- [ ] **(Pro)** ARIA landmark auto-add
- [ ] **(Pro)** Heading hierarchy detection & repair
- [ ] **(Pro)** Page-builder fix packs (Elementor Pro, Divi, Bricks, Beaver Builder, Oxygen, Breakdance)
- [ ] **(Pro)** Wishlist/compare plugin fixes (YITH Wishlist, TI Wishlist, YITH Compare)

### 5.4 Category C — Reporting (proposal §4.2.C)

- [ ] **(Pro)** `PdfReportGenerator.php` foundation on DomPDF (local generation)
- [ ] **(Pro)** Legal-audit PDF (timestamped, signed hash, screenshots, remediation status)
- [ ] **(Pro)** Developer report (code snippets + selectors, exportable)
- [ ] **(Pro)** Executive summary PDF (score, trend, top issues)
- [ ] **(Pro)** CSV/JSON export
- [ ] **(Pro)** Immutable timestamped audit history
- [ ] **(Pro)** Compliance score card (0–100, severity-weighted, trended)

### 5.5 Category D — Statements (proposal §4.2.D)

- [ ] **(Pro)** Multi-jurisdiction templates (EU EAA, US ADA/508, Canada AODA, UK Equality Act, Australia DDA)
- [ ] **(Pro)** Statement translations (DE, FR, ES, IT, NL, PT, PL beyond EN)
- [ ] **(Pro)** PDF statement export per jurisdiction
- [ ] **(Pro)** Statement version history snapshots

### 5.6 Later Expansions (months 6–18 — categories E–J, proposal §4.2 & §12)

- [ ] **(Pro)** Team & workflow: assignment, comments, remediation status, activity log, digest email, Slack/webhooks, Auditor role (§4.2.E)
- [ ] **(Pro)** Integrations: WPML, Polylang, WC Subscriptions/Bookings/Memberships, WP-CLI commands, REST API endpoints, multisite (§4.2.F) — REST design pre-specified in docs/rest-api.md
- [ ] **(Pro)** Gutenberg integration: block-level warnings, pre-publish check, contextual guidance (§4.2.G)
- [ ] **(Pro)** Priority theme support packs for the 10 listed themes (§4.2.H)
- [ ] **(Pro)** Automation & monitoring: auto-scan on publish, regression + critical alerts, CI/CD (§4.2.I)
- [ ] **(Pro)** Agency features: white-label, central dashboard add-on, bulk actions, client reports, reseller licenses (§4.2.J)
