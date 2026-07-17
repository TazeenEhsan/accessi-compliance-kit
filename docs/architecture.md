# Architecture — AccessiWoo

Implementation-focused view of `PLUGIN_PROPOSAL.md` §5 (Technical Architecture). Nothing here changes the proposal; it organizes it for building.

---

## 1. High-Level Shape

AccessiWoo is a self-contained WordPress plugin. Everything runs inside the merchant's install:

- **Scanner** — axe-core (bundled JS, MIT) runs in the browser against the merchant's own pages; results are stored server-side via admin-ajax. No data leaves the server (proposal §5.5).
- **Auto-fixes** — independent, toggleable PHP classes hooking WordPress/WooCommerce filters to repair markup at render time (proposal §5.6).
- **Statement generator** — creates a normal WordPress page from a bundled template (proposal §4.1).
- **Admin UI** — React app built with `@wordpress/element` + `@wordpress/components`, mounted on a single admin page under WooCommerce → Accessibility.
- **Pro layer** — `src/Pro/` loaded only behind a valid Freemius license; free plugin is fully functional without it (proposal §5.7).

**Hard constraint:** zero external calls, zero server costs. axe-core bundled, DomPDF runs locally, notifications (Pro) are outgoing-only webhooks (proposal §5.1, §4.2 intro).

## 2. Tech Stack (proposal §5.1)

| Layer | Technology | Notes |
|---|---|---|
| Core plugin | PHP 7.4+ (targeting 8.0+) | Standard WordPress plugin, GPLv2 |
| Scanner | axe-core (JS, MIT) | Bundled via npm, no CDN |
| Admin UI | React via `@wordpress/element` + WP components | Ships with WP core; use as webpack externals |
| PDF (Pro) | DomPDF (LGPL 2.1) | Bundled via Composer, local generation |
| Database | `$wpdb` + custom table | Scan history (see docs/database.md) |
| Licensing (Pro) | Freemius SDK | No upfront cost |
| Build | `@wordpress/scripts` | webpack config extends, never replaces |
| Testing | PHPUnit + Jest | Recommended per proposal |

Minimum targets (proposal §11): WordPress 6.5+, WooCommerce 8.0+, PHP 7.4.

## 3. File & Folder Structure

Authoritative layout is proposal §5.2 — reproduce it exactly. Summary of responsibilities:

| Path | Responsibility |
|---|---|
| `accessiwoo.php` | Headers, constants, autoloader require, activation/deactivation hook registration, boot `Plugin` |
| `uninstall.php` | Full cleanup on deletion (table + options) |
| `src/Plugin.php` | Singleton bootstrap; wires services on `plugins_loaded`; guarded Pro loader. No feature logic. |
| `src/Activator.php` / `src/Deactivator.php` | Create table + seed defaults / unschedule cron. Deactivation never deletes data. |
| `src/Admin/` | `AdminMenu`, `DashboardWidget`, `SettingsPage`, `ScanPage` — admin page registration, asset enqueueing, settings registration (docs/admin.md) |
| `src/Scanner/` | `ScanController` (AJAX), `ScanStorage` (DB access — sole owner of the scans table), `ViolationParser` (normalize axe output), `ScanCrawler` (Pro) |
| `src/Fixes/` | `FixManager`, `AbstractFix`, six free fix classes (docs/frontend.md) |
| `src/Statement/` | `StatementGenerator` + `templates/{en,de,...}.php` |
| `src/Pro/` | Pro-only: `CrawlerScheduler`, `PdfReportGenerator`, `AdvancedFixes/` — loaded only with valid license |
| `src/Utils/` | `Logger`, `Options` (all option access), `Capabilities` (all permission checks) |
| `assets/js/src/admin/` | React source: `App.jsx`, `ScanResults.jsx`, `Settings.jsx`, `Dashboard.jsx` |
| `assets/js/src/scanner/` | `runScan.js` — axe-core runner |
| `assets/css/` | `admin.css`, `frontend-fixes.css` (injected focus states etc.) |
| `build/` | Compiled output (gitignored) |
| `languages/` | `.pot` for i18n |
| `tests/phpunit/`, `tests/js/` | Test suites |

Note: the development repo folder is `accessi-compliance-kit/`; the internal structure above is what matters and follows the proposal.

## 4. Boot Sequence

1. WordPress loads `accessiwoo.php` → constants defined, Composer autoloader required, activation/deactivation hooks registered.
2. `plugins_loaded` → `Plugin::instance()->boot()`:
   - Check WooCommerce is active; if not, show admin notice and skip WC-dependent services.
   - Load text domain.
   - Register services: Admin (menu, dashboard widget, settings, scan page assets), Scanner (AJAX handlers, conditional front-end asset), FixManager, StatementGenerator AJAX.
   - If a valid Pro license is detected → load `src/Pro/` services (Phase 5).
3. `init` → `FixManager` iterates registered fixes, calls `register()` on each enabled fix (proposal §5.6).
4. `admin_init` → Settings API registration.
5. `admin_menu` → WooCommerce → Accessibility submenu.
6. `admin_enqueue_scripts` / `wp_enqueue_scripts` → admin bundle (plugin page only) / `frontend-fixes.css` (when relevant fixes enabled) / scanner bundle (only with `?accessiwoo_scan=1` + capability).

## 5. Scan Data Flow (proposal §5.5)

1. Admin clicks "Scan this page" (admin bar or plugin page).
2. Admin app opens a **hidden iframe** pointing at the target URL with `?accessiwoo_scan=1`.
3. Front-end scanner script loads **only** when that flag is present AND the user has the scan capability; it runs axe-core on the loaded page.
4. Scanner `postMessage`s formatted results to the parent window (with handshake token; see docs/security.md §5).
5. Parent posts results to `admin-ajax.php` action `accessiwoo_run_scan` (nonce + capability enforced).
6. PHP normalizes via `ViolationParser`, saves via `ScanStorage` to `wp_accessiwoo_scans`.
7. Admin UI receives the saved summary and renders results grouped by severity.

Failure paths to handle: iframe never loads / no message within timeout → mark scan `failed`; AJAX save fails → surface error, don't lose the client-side result silently.

## 6. Fix System (proposal §5.6)

Each fix extends `AbstractFix`:

- `is_enabled()` — reads the `accessiwoo_active_fixes` option (via `Utils/Options`)
- `applies_to()` — returns contexts: `'product'`, `'checkout'`, `'cart'`, `'global'`
- `register()` — hooks the appropriate WP/WC filters/actions

`FixManager` loops on `init`, checks each fix's enabled state, and calls `register()`. Fixes are independent and individually toggleable; **all default OFF** (proposal §9). See docs/frontend.md for per-fix specs and the hook each uses.

## 7. Hook Inventory (proposal §5.4)

**Actions:** `plugins_loaded` (bootstrap), `init` (fix registration), `admin_init` (settings), `admin_menu` (pages), `admin_enqueue_scripts`, `wp_enqueue_scripts`, `wp_ajax_accessiwoo_run_scan`, `wp_ajax_accessiwoo_save_settings`, `woocommerce_before_checkout_form`, `woocommerce_after_add_to_cart_button`.

**Filters:** `wp_get_attachment_image_attributes` (alt fallbacks), `woocommerce_form_field_args` (form labels), `woocommerce_locate_template` (template overrides — use sparingly, last resort), `the_content` (statement content), `body_class` (fixes-active class).

**REST:** `register_rest_route( 'accessiwoo/v1', '/scans', ... )` — optional/future, spec in docs/rest-api.md.

## 8. Free / Pro Boundary (proposal §5.7)

- Freemius SDK handles licensing; `src/Pro/` autoloads only when a valid license is detected.
- Every Pro class is optional — the free plugin must work fully without them.
- Free code never references Pro classes except through the single guarded loader in `Plugin.php`.
- MVP scope = free tier (proposal §4.1); Pro launches post-500-installs with categories A–D first (proposal §4.2 scope note, §6 Phase 5, §12).

## 9. Cross-Cutting Concerns

- **Options:** four keys in `wp_options` (see docs/database.md §3); all access via `Utils/Options`.
- **Capabilities:** default `manage_options`; centralized in `Utils/Capabilities` (Pro later adds an "Accessibility Auditor" read-only role, proposal §4.2.E).
- **Logging:** `Utils/Logger`, `WP_DEBUG`-gated, prefixed.
- **i18n:** text domain `accessiwoo`, `.pot` in `languages/`.
- **Cron:** one weekly reminder event (opt-in) in the free tier; Pro adds scan scheduling.
- **Security:** docs/security.md is the checklist; WordPress.org review compliance is a launch criterion (proposal §11).
