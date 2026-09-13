# TASKS.md — Accessibility Compliance Kit for WooCommerce Implementation Tasks

Source of truth: `PLUGIN_PROPOSAL.md`. Rules: `AI_RULES.md`. Design detail: `docs/`.
Each task is sized for roughly 10–20 minutes. Work top to bottom within a phase; phases follow proposal §6. Check a box only when the task is done **and verified** (file exists, code runs, test passes — whichever applies).

Legend: tasks marked **(Pro)** are Phase 5+ and must not be started before the free version launches (proposal §6, Phase 5 gate: ~500+ active installs).

---

## Phase 0 — Setup (proposal §6, Week 1)

### 0.1 Repository & Tooling

- [x] Create the plugin folder skeleton: empty `src/`, `assets/js/src/`, `assets/css/`, `assets/images/`, `languages/`, `tests/phpunit/`, `tests/js/` directories per proposal §5.2
- [x] Write `.gitignore` covering `node_modules/`, `build/`, `vendor/`, OS/editor files
- [x] Write `LICENSE.txt` (GPLv2 full text) — *note (2026-07-18, task 4.3):* the file was found missing from the working tree (and had never been committed) during release packaging; restored with the pure GPLv2 text and included in the release zip.
- [x] Write `composer.json`: project metadata, PHP 7.4 platform requirement, PSR-4 autoload `AccessibilityComplianceKitForWooCommerce\` → `src/`, require `dompdf/dompdf`, require-dev PHPUnit
- [x] Run `composer install`; verify `vendor/autoload.php` exists and autoloads a dummy class from `src/`
- [x] Write `package.json` with `@wordpress/scripts` dev dependency and `build`/`start` scripts for two entry points (admin app, scanner)
- [x] Write `webpack.config.js` extending `@wordpress/scripts` default config with entries `assets/js/src/admin/index.js` and `assets/js/src/scanner/index.js` outputting to `build/`
- [x] Run `npm install` and a first `npm run build` with placeholder entry files; verify `build/` output
- [x] Add `axe-core` as an npm dependency (bundled, no CDN — AI_RULES §2.1); verify it appears in the scanner bundle

### 0.2 Plugin Bootstrap

- [x] Write `accessibility-compliance-kit-for-woocommerce.php` main file: plugin headers (name, description, version, requires WP 6.5, requires PHP 7.4, license GPLv2, text domain `accessibility-compliance-kit-for-woocommerce`), `ABSPATH` guard, define constants (`ACCESSIBILITY_COMPLIANCE_KIT_FOR_WOOCOMMERCE_VERSION`, `ACCESSIBILITY_COMPLIANCE_KIT_FOR_WOOCOMMERCE_FILE`, `ACCESSIBILITY_COMPLIANCE_KIT_FOR_WOOCOMMERCE_PATH`, `ACCESSIBILITY_COMPLIANCE_KIT_FOR_WOOCOMMERCE_URL`), require Composer autoloader
- [x] Write `src/Plugin.php` singleton: `instance()`, `boot()` hooked on `plugins_loaded`, empty service-registration method stubs (admin, scanner, fixes, statement)
- [x] Add a WooCommerce-active check in `Plugin.php` with an admin notice when WooCommerce is missing (plugin targets WC 8.0+, proposal §11)
- [x] Write `src/Activator.php` stub + `src/Deactivator.php` stub; register `register_activation_hook` / `register_deactivation_hook` in `accessibility-compliance-kit-for-woocommerce.php`
- [x] Implement table creation in `Activator.php`: `wp_accessibility_compliance_kit_for_woocommerce_scans` via `dbDelta()` exactly per proposal §5.3 schema (see docs/database.md), store `accessibility_compliance_kit_for_woocommerce_db_version` option
- [x] Implement default options seeding in `Activator.php`: `accessibility_compliance_kit_for_woocommerce_settings` and `accessibility_compliance_kit_for_woocommerce_active_fixes` (all fixes OFF per AI_RULES §2.3)
- [x] Write `uninstall.php`: drop the scans table and delete all `accessibility_compliance_kit_for_woocommerce_*` options (guarded by `WP_UNINSTALL_PLUGIN`)
- [x] Write `src/Utils/Options.php`: typed getters/setters wrapping `get_option`/`update_option` for the four option keys in proposal §5.3
- [x] Write `src/Utils/Capabilities.php`: capability constants and `can_scan()` / `can_manage_settings()` helpers (default `manage_options`)
- [x] Write `src/Utils/Logger.php`: thin wrapper around `error_log` gated by `WP_DEBUG`, with a `accessibility_compliance_kit_for_woocommerce_` prefix
- [x] Activate the plugin on the local dev site; verify: no errors, table created with correct columns, default options present
- [x] Set up `tests/phpunit/` bootstrap (WP test suite or Brain Monkey — pick per docs/coding-guidelines.md) and one smoke test asserting `Plugin::instance()` returns a singleton

## Phase 1 — Single-Page Scanner (proposal §6, Weeks 2–3)

### 1.1 Front-End Scanner Runner

- [x] Write `assets/js/src/scanner/runScan.js`: import axe-core, run `axe.run(document)`, format results (rule id, impact, description, help text, nodes with CSS selectors + HTML snippets), `postMessage` payload to `window.parent` (proposal §5.5 step 4, §10 prompt 3)
- [x] Write the scanner entry `assets/js/src/scanner/index.js`: run on DOM ready, include a origin/handshake token read from a localized variable so the parent can verify messages (docs/security.md §5)
- [x] Add conditional enqueue in a new `src/Scanner/ScannerAssets.php` (registered from `Plugin.php`): load scanner bundle on front-end **only** when `?accessibility_compliance_kit_for_woocommerce_scan=1` AND `Capabilities::can_scan()` (proposal §5.5 step 3)
- [x] Localize scanner script with the handshake token + admin origin; verify manually that the script loads only for admins with the flag

### 1.2 Scan Persistence (PHP)

- [x] Write `src/Scanner/ViolationParser.php`: normalize raw axe-core JSON into the stored shape (violations array + summary counts by severity: critical/serious/moderate/minor per proposal §4.1)
- [x] Unit-test `ViolationParser` with a fixture of real axe-core output (empty results, mixed severities)
- [x] Write `src/Scanner/ScanStorage.php` part 1: `create_scan( $url, $type, $user_id )` inserting a `running` row, `complete_scan( $id, $violations, $summary )`, `fail_scan( $id )` — all via `$wpdb->prepare` (docs/database.md)
- [x] Write `src/Scanner/ScanStorage.php` part 2: `get_scan( $id )`, `get_recent_scans( $limit, $offset )`, `get_last_scan()` (updates/reads `accessibility_compliance_kit_for_woocommerce_last_scan_id`), JSON decode helpers
- [x] Unit-test `ScanStorage` round-trip (insert → complete → fetch)

### 1.3 AJAX Endpoints

- [x] Write `src/Scanner/ScanController.php`: register `wp_ajax_accessibility_compliance_kit_for_woocommerce_run_scan`; handler validates nonce + capability, sanitizes URL and payload, runs `ViolationParser`, saves via `ScanStorage`, returns JSON summary (proposal §5.4, §5.5 steps 5–6)
- [x] Add a second handler `wp_ajax_accessibility_compliance_kit_for_woocommerce_get_scan` (fetch one scan's stored results for the results UI) with nonce + capability checks
- [x] Add URL validation for scan targets: same-site origin only (`esc_url_raw` + host check against `home_url()`) — prevents scanning arbitrary external URLs (docs/security.md §6)
- [x] Test the endpoints with a REST client / curl: valid nonce succeeds, missing nonce and non-admin fail with proper error codes

### 1.4 Admin Scan UI (React)

- [x] Write `src/Admin/AdminMenu.php`: register submenu page under WooCommerce → "Accessibility" (proposal §4.1), page callback renders a root `<div id="accessibility-compliance-kit-for-woocommerce-admin">`
- [x] Write `src/Admin/ScanPage.php`: enqueue admin bundle + `admin.css` on the plugin page only (`admin_enqueue_scripts`), `wp_localize_script` with ajaxurl, nonces, current-site URL, severity labels
- [x] Write React shell `assets/js/src/admin/App.jsx` + entry `index.js`: tab layout (Scan | History | Settings) using `@wordpress/components`
- [x] Write `assets/js/src/admin/ScanRunner.jsx` (iframe orchestration): URL input (default: home page), "Scan this page" button, create hidden iframe with `?accessibility_compliance_kit_for_woocommerce_scan=1`, listen for `postMessage`, verify handshake token, show progress state (proposal §5.5 steps 1–4)
- [x] Wire ScanRunner results to the `accessibility_compliance_kit_for_woocommerce_run_scan` AJAX action; handle success/error/timeout (e.g. 60s no-message timeout → failed state)
- [x] Write `assets/js/src/admin/ScanResults.jsx`: violations grouped by severity (Critical, Serious, Moderate, Minor), each row shows rule name, affected element selector, why it fails, how to fix (proposal §4.1) — worded as "detected issues" (AI_RULES §2.4)
- [x] Add expandable violation detail (HTML snippet, help URL from axe-core data) to `ScanResults.jsx`
- [x] Write scan History tab: table of past scans from a new `wp_ajax_accessibility_compliance_kit_for_woocommerce_get_scans` list endpoint (date, URL, status, severity counts), click-through to results view
- [x] Implement the `accessibility_compliance_kit_for_woocommerce_get_scans` list AJAX handler in `ScanController.php` (nonce + capability + pagination)
- [x] Write `assets/css/admin.css`: base layout/severity color coding consistent with WP admin styles
- [ ] End-to-end test on the dev site: scan the shop page, see grouped violations, row saved in DB, history lists it — **not yet verified**: build is clean (`npm run build`), `vendor/bin/phpunit` passes (17/17), and `/wp-admin/admin.php?page=accessibility-compliance-kit-for-woocommerce` + the front page both respond without fatal errors, but no browser/WP-CLI session was available in this environment to click through the actual scan flow. Needs a manual pass in a logged-in browser.

### 1.5 Scan Entry Points

- [x] Add "Scan this page" admin-bar node on front-end pages for users with scan capability, linking to the admin page with the current URL pre-filled (proposal §5.5 step 1)
- [ ] Verify the URL-input scan path: paste any same-site URL into the Scan tab and run it (proposal §4.1 "scan by URL input") — **not yet verified**, same limitation as above; needs a manual browser pass.

## Phase 2 — Auto-Fixes (proposal §6, Weeks 3–5)

### 2.1 Fix Framework

- [x] Write `src/Fixes/AbstractFix.php`: abstract base with `id()`, `label()`, `description()`, `is_enabled()` (reads `accessibility_compliance_kit_for_woocommerce_active_fixes` via Options), `applies_to()` returning contexts ('product', 'checkout', 'cart', 'global'), abstract `register()` (proposal §5.6)
- [x] Write `src/Fixes/FixManager.php`: hard-coded registry of the 6 free fix classes, loops on `init`, instantiates each, calls `register()` only when `is_enabled()` (proposal §5.6)
- [x] Add `body_class` filter in `FixManager`: append `accessibility-compliance-kit-for-woocommerce-fixes-active` (plus per-fix classes) when any fix is enabled (proposal §5.4)
- [x] PHPUnit test: `FixManager` registers only enabled fixes (proposal §10 prompt 4)

### 2.2 The Six Free Fixes (one task each — see docs/frontend.md for specs)

- [x] `ProductImageAltFix.php` — filter `wp_get_attachment_image_attributes`; when alt is empty on product images, fall back to the product title; only on product contexts (proposal §4.1, §10 prompt 1)
- [x] Unit-test `ProductImageAltFix` (empty alt gets title, existing alt untouched)
- [x] `CheckoutLabelsFix.php` — filter `woocommerce_form_field_args` to ensure checkout fields have proper `<label>` associations (proposal §4.1, §5.4)
- [x] `FocusStatesFix.php` — enqueue `assets/css/frontend-fixes.css` (via `wp_enqueue_scripts`) adding visible focus states to all buttons and links; write the CSS with `:focus-visible` and a high-contrast outline
- [x] `IconButtonAriaFix.php` — add `aria-label` to icon-only buttons (cart, search, wishlist icons); implemented via a small shared front-end bundle (`assets/js/src/fixes/index.js`, new `fixes` webpack entry — see note below) enqueued only when this fix is enabled
- [x] `PriceScreenReaderFix.php` — prepend `<span class="screen-reader-text">Price:</span>` to WooCommerce price output so screen readers announce prices correctly (proposal §4.1)
- [x] `EmptyLinkAnchorFix.php` — give accessible names to empty link anchors (e.g. product image links), e.g. inject screen-reader text with the product title
- [x] Unit-test the remaining five fixes (one focused test file each in `tests/phpunit/Fixes/`)

### 2.3 Settings

- [x] Register settings via `admin_init` in `src/Admin/SettingsPage.php`: sanitization callback for `accessibility_compliance_kit_for_woocommerce_settings` and `accessibility_compliance_kit_for_woocommerce_active_fixes` (whitelist known fix IDs, booleans only)
- [x] Add `wp_ajax_accessibility_compliance_kit_for_woocommerce_save_settings` handler (nonce + capability + sanitize) per proposal §5.4
- [x] Write `assets/js/src/admin/Settings.jsx`: one ToggleControl per fix (label + description + context badge), all default OFF, save via AJAX with success/error notice (proposal §9: individually toggleable, off by default)
- [x] Add email-reminder opt-in toggle to Settings UI (stores in `accessibility_compliance_kit_for_woocommerce_settings`; used in Phase 4 notifications)
- [ ] Manual test matrix: enable each fix one at a time on Storefront; confirm the fix applies and nothing visually breaks (proposal §6 Phase 2) — **not yet verified**: `vendor/bin/phpunit` passes (48/48) and `npm run build` is clean, but no browser/WP-CLI session with WooCommerce + Storefront was available in this environment. Needs a manual pass in a logged-in browser with each fix toggled on individually.
- [ ] Repeat quick fix smoke-test on Astra and Kadence (proposal §6 Phase 2; full matrix again in Phase 4) — **not yet verified**, same limitation as above.

**Note on `docs/frontend.md` vs. proposal §5.2:** `docs/frontend.md` §4.4/§4.6 explicitly calls for "a tiny front-end JS file in addition to PHP filters" for `IconButtonAriaFix` and `EmptyLinkAnchorFix`, since icon markup and empty-anchor patterns vary by theme. Proposal §5.2's file tree doesn't enumerate this file (it only shows `admin/` and `scanner/` under `assets/js/src/`). This isn't a contradiction — the proposal tree is illustrative, not exhaustive (it already omits the real `build/` output naming used elsewhere) — so a third `assets/js/src/fixes/index.js` entry (bundled via a new `fixes` webpack entry to `build/fixes.js`) was added rather than stopping. Flagging here per AI_RULES §"if documentation conflicts... stop and report" in case that reading is wrong.

## Phase 3 — Accessibility Statement (proposal §6, Week 6)

- [x] Write `src/Statement/templates/en.php`: EAA-compliant statement template with placeholders — compliance level claimed, known limitations section, contact for accessibility issues, date of last review (proposal §4.1)
- [x] Write `src/Statement/StatementGenerator.php`: `generate()` creates a WordPress page titled "Accessibility Statement" populated from the template with site name/contact/date substitutions (`wp_insert_post`, draft-or-publish per settings decision in docs/admin.md)
- [x] Handle re-generation: if a statement page already exists (store page ID in `accessibility_compliance_kit_for_woocommerce_settings`), warn instead of duplicating; offer "create new" explicitly
- [x] Add `wp_ajax_accessibility_compliance_kit_for_woocommerce_generate_statement` handler (nonce + capability) returning the created page's edit link
- [x] Add "Create statement page" button + status card (exists / not created, link to edit) to the admin React app (proposal §6 Phase 3)
- [x] Unit-test `StatementGenerator` (page created with expected title/content, no duplicate on second call)

## Phase 4 — Polish & Free Launch (proposal §6, Weeks 7–8)

### 4.1 Dashboard Widget & Notifications

- [x] Write `src/Admin/DashboardWidget.php`: WP dashboard summary widget — last scan date, violation count by severity, link to full page (proposal §4.1, §11)
- [x] Add admin-bar notice when the last scan found critical issues (proposal §4.1 "Notifications") — implemented in `src/Scanner/ScannerAssets.php::add_critical_notice_node()`, alongside the existing "Scan this page" node (docs/admin.md §1: "Admin bar ... registered from Scanner service")
- [x] Implement weekly email reminder: WP-Cron event scheduled only when the opt-in setting is on, unscheduled on deactivation/opt-out; email links to the scan page (proposal §4.1) — `src/Admin/EmailReminder.php`, wired to `Options::SETTINGS` add/update hooks; `Deactivator::deactivate()` calls `EmailReminder::unschedule()`
- [ ] Test cron scheduling/unscheduling (toggle opt-in, deactivate plugin, check `wp cron event list`) — **not yet verified**: `tests/phpunit/Admin/EmailReminderTest.php` unit-tests the scheduling/unscheduling logic against stubbed `wp_next_scheduled`/`wp_schedule_event`/`wp_unschedule_event` (`vendor/bin/phpunit` passes, 75/75), but no WP-CLI or browser session was available in this environment to toggle the real opt-in setting and check `wp cron event list` end-to-end. Needs a manual pass, same limitation as tasks 1.4/1.5/2.3.

**Note on `src/Admin/EmailReminder.php` vs. proposal §5.2:** the weekly-reminder scheduling/sending logic needed a home, and proposal §5.2's file tree doesn't list a file for it (it only names `AdminMenu.php`, `DashboardWidget.php`, `SettingsPage.php`, `ScanPage.php` under `Admin/`). `docs/admin.md` §1/§9 already specifies the behavior (cron hook `accessibility_compliance_kit_for_woocommerce_weekly_reminder`, scheduled on opt-in, cleared on opt-out/deactivation) without naming a file, so a new `src/Admin/EmailReminder.php` was added — same "tree is illustrative, not exhaustive" reasoning as the Phase 2 note below. `docs/architecture.md`'s `src/Admin/` responsibility row was updated to list it. Flagging here per AI_RULES in case that reading is wrong.

### 4.2 i18n & Hardening

- [x] Sweep all PHP/JS for untranslated strings; load text domain; generate `languages/accessibility-compliance-kit-for-woocommerce.pot` (`wp i18n make-pot`) — one gap found (`assets/js/src/admin/utils/ajax.js`'s fallback error string) and fixed; `languages/accessibility-compliance-kit-for-woocommerce.pot` generated via `wp i18n make-pot` (98 strings). Text domain was already loaded correctly from task 0.2.
- [x] Security pass 1: verify every AJAX handler has nonce + capability + sanitization + escaped output (checklist in docs/security.md §9) — all six `wp_ajax_accessibility_compliance_kit_for_woocommerce_*` handlers checked clean (nonce → capability → sanitize → service call → `wp_send_json_*`); no `wp_ajax_nopriv_*` handlers exist.
- [x] Security pass 2: verify all `$wpdb` calls use `prepare()`, all templates escape output, every PHP file has the ABSPATH guard — all clean except one real gap: `Activator.php` called `get_option()`/`update_option()` directly instead of through `Utils/Options.php` (AI_RULES §6). Fixed by adding `Options::get_db_version()`/`update_db_version()`/`option_exists()` and updating `Activator.php` to use them.
- [x] Run WPCS (`phpcs` with WordPress ruleset) across `src/`; fix violations — added `phpcs.xml.dist` (WordPress + WordPress-Docs + PHPCompatibilityWP, testVersion 7.4-, PSR-4 filenames exempted per AI_RULES §4, `ScanStorage.php`/`uninstall.php` exempted from the direct-DB-query sniffs since they're the sole gateway to the plugin's custom table). Added `squizlabs/php_codesniffer`, `wp-coding-standards/wpcs`, `phpcompatibility/phpcompatibility-wp` as composer dev dependencies. Fixed all 30 errors and 52 warnings found (false-positive sanitization flags annotated with `phpcs:ignore` + reason, real issues like reserved-keyword param names and a non-Yoda condition fixed outright). `vendor/bin/phpcs src/ accessibility-compliance-kit-for-woocommerce.php uninstall.php` now reports 0 errors, 0 warnings.
- [x] Run Plugin Check (WordPress.org's plugin-check tool) and fix everything it flags — ran the real `wp plugin check` (WordPress.org's plugin-check plugin, already installed on the dev site) against a live WP install. Fixed: removed `composer.phar` from the plugin folder (already gitignored, doesn't belong in the working tree), removed 5 stale `.gitkeep` files from directories that are no longer empty, deleted `.phpunit.result.cache` and set `cacheResult="false"` in `phpunit.xml.dist` so it stops regenerating, added the ABSPATH guard to all 17 PHPUnit test files that lacked it (docs/security.md §9 says "every PHP file"; the fake `ABSPATH` constant test bootstrap already defines makes this a safe no-op), removed the deprecated `load_plugin_textdomain()` call from `Plugin.php` (discouraged since WP 4.6 for WP.org-hosted plugins), and annotated remaining false positives (`ExceptionNotEscaped` on test-only exception doubles, `PrefixAllGlobals` on tests simulating WooCommerce's own `$product` global, direct-DB-query warnings) with `phpcs:ignore` + reason. Remaining flags are all dev-scaffolding files that are correctly excluded from the WP.org release zip at packaging time, not source-code problems: `phpcs.xml.dist`, `phpunit.xml.dist`, `.gitignore`, the two still-genuinely-empty `.gitkeep` placeholders (`assets/images/`, `tests/js/`), and the not-yet-written `readme.txt` — all squarely task 4.3's job.

### 4.3 Release Assets & Submission

- [x] Write `readme.txt` in WordPress.org format: short/long description, installation, FAQ, screenshots section, changelog; validate in the WP.org readme preview tool (proposal §11) — written at plugin root, content matches shipped behavior (fix labels taken verbatim from the `src/Fixes/` classes; "detected issues" wording per AI_RULES §2.4; no compliance-guarantee claims). Validated by POSTing the file to the real WP.org readme validator: **0 errors**; one expected warning — `Contributors: accessiwoo` is not a registered WP.org username yet and **must be replaced with the real WP.org account username before submission** — plus an optional "no donate link" note.
- [ ] Fresh-install test: new WP 6.5+ / WC 8.0+ site, install + activate without errors, run through every MVP success criterion in proposal §11 and record results — **not yet done**: requires a fresh WP site + browser session, neither available in this environment. The built zip (see below) extracts cleanly and its production autoloader resolves plugin + DomPDF classes, but the actual clean-site run-through is pending.
- [ ] Full theme regression: all 6 fixes enabled simultaneously on Storefront, Astra, Kadence — front-end must not break (proposal §11) — **not yet done**: needs a browser session, and only Kadence is installed on the local dev site (Storefront and Astra are not present in `wp-content/themes/`). Install both, enable all 6 fixes, and click through shop/product/cart/checkout on each theme.
- [ ] Take WP.org screenshots (scan results, settings, statement button, dashboard widget) and store in the assets-for-wp.org folder — **prepared, not shot**: `plugin-foundation-files/assets-for-wp.org/` created with a README covering the 4-shot list (numbering matches readme.txt's Screenshots section — don't reorder one without the other), banner/icon specs, and capture prep notes. The captures themselves need a browser.
- [ ] Record the demo video (script: scan → review violations → enable a fix → generate statement) (proposal §6 Phase 4) — **script written** (~90s, 6 scenes, in `plugin-foundation-files/assets-for-wp.org/README.md`); recording needs a human at a browser.
- [x] Prepare docs page + FAQ content for support-forum readiness (proposal §6 Phase 4) — written in `plugin-foundation-files/launch/`: `docs-page.md` (requirements, install, scanning, the six fixes, statement, notifications, privacy, troubleshooting, developer notes), `faq.md` (extended public FAQ; readme.txt carries the short version — keep in sync), and `support-templates.md` (8 canned forum responses per proposal §9). Publish `docs-page.md`/`faq.md` on the plugin site before launch.
- [x] Ship the docs in-plugin (2026-07-19; no external docs site exists yet, so the plugin carries its own): new **Accessibility → User Guide** admin page (`src/Admin/GuidePage.php`) — server-rendered, zero-JS `<details>` accordions covering getting started / scanning / results / the six fixes (pulled live from `FixManager` so labels stay in sync with Settings) / statement / notifications / troubleshooting / privacy / developers, plus a condensed FAQ and a "Still need help?" support-forum card. Content mirrors `launch/docs-page.md` + `launch/faq.md` — **keep all three in sync**. Also added: Dashboard / User Guide action links on the Plugins screen (`src/Admin/PluginLinks.php`). Verified: `vendor/bin/phpunit` passes, `vendor/bin/phpcs` clean, `.pot` regenerated.
- [x] Restructure the admin menu (2026-07-19, follow-up direction): the plugin now registers a **top-level "Accessibility" menu** (`AdminMenu::add_menu_page()`) with the universal-access logo as SVG-data-URI menu icon (`AdminMenu::menu_icon_data_uri()`, drawn in `#a7aaad` since WP doesn't recolor SVG menu icons) and submenus **Dashboard** (first item, same slug as parent so WP doesn't duplicate the parent label) and **User Guide**. The original **WooCommerce → Accessibility** entry is kept as a pointer link (`admin.php?page=` slug form, small inline-SVG logo in its title) to the same page. The React app header gained a highlighted **User Guide** button (`guideUrl` in `ScanPage::localized_data()`). Note: the main page's hook suffix changed from `woocommerce_page_accessibility-compliance-kit-for-woocommerce` to `toplevel_page_accessibility-compliance-kit-for-woocommerce`; the guide page's is `{translated-parent-title}_page_accessibility-compliance-kit-for-woocommerce-guide`, so `GuidePage::maybe_enqueue()` matches by suffix. Deviation from proposal §4.1 ("submenu under WooCommerce"): the WC entry point is preserved, so this is an addition, not a removal — flagging per AI_RULES in case that reading is wrong. Verified: `vendor/bin/phpunit` 85/85, `vendor/bin/phpcs` clean, `npm run build` clean, `.pot` regenerated.
- [ ] Build the distributable zip (build JS, `composer install --no-dev`, exclude dev files); test-install the zip on a clean site — **built and structurally verified, clean-site install pending**: `dist/accessibility-compliance-kit-for-woocommerce-1.0.0.zip` (gitignored). Packaging is now scripted (2026-07-18): `tools/build-zip.js` runs automatically as part of `npm run build` (or standalone via `npm run zip`) and writes the zip to `dist/` at the plugin root — the earlier one-off `plugin-foundation-files/dist/` location is retired. The script stages a clean copy, runs `composer install --no-dev --optimize-autoloader` (vendor = dompdf + masterminds/phenx/sabberworm only; needs Composer on PATH or a gitignored `composer.phar` at the plugin root), includes readme.txt/LICENSE.txt/src/build/assets(css+js src)/languages/composer.json, excludes all dev files (verified: 0 matches for node_modules/tests/phpunit/phpcs/webpack/package.json/.gitignore/composer.lock in the archive). Zips with bsdtar — proper zip (PK magic, forward-slash entries, single `accessibility-compliance-kit-for-woocommerce/` root; note: PowerShell 5.1 `Compress-Archive` and Git Bash's GNU tar both produce broken archives, the script avoids them). Every staged PHP file passes `php -l`; extracted-zip autoloader resolves `AccessibilityComplianceKitForWooCommerce\Plugin`, `FixManager`, and `Dompdf\Dompdf`. Remaining: install the zip on a clean WP site via Plugins → Upload.
- [ ] Submit to WordPress.org; log submission date (review takes 1–14 days, proposal §6 Phase 4) — **human-only**: needs the WP.org account (fix the `Contributors:` username in readme.txt first), the verified zip, and the screenshots. Blocked behind the three unchecked verification tasks above.
- [x] Draft launch announcements for r/woocommerce, r/wordpress, WP Tavern, Twitter/X, LinkedIn (proposal §6 Phase 4, §8) — drafted per-channel (not copy-paste duplicates) in `plugin-foundation-files/launch/announcements.md`, with a posting checklist and `[WPORG-LINK]` placeholders to fill after approval.

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