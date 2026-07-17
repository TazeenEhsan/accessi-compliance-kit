# AI_RULES.md — Rules for AI-Assisted Development of AccessiWoo

These rules govern every code-generation session.

## Project Priority

Always follow this priority order:

1. AI_RULES.md
2. TASKS.md
3. Relevant files in docs/
4. PLUGIN_PROPOSAL.md (source of truth for requirements)

If any documentation conflicts with PLUGIN_PROPOSAL.md, do not guess.
Stop and report the conflict.

---

## Project Workflow

Before writing any code:

1. Read AI_RULES.md.
2. Read TASKS.md.
3. Find the first unchecked task.
4. Read only the documentation required for that task.
5. Consult PLUGIN_PROPOSAL.md only if documentation is missing, incomplete, or conflicting.

During implementation:

- Implement only ONE task.
- Make the smallest necessary changes.
- Never modify unrelated code.
- Never refactor unless the task explicitly requires it.
- Never invent new requirements.

After implementation:

1. Update TASKS.md.
2. Update any affected documentation.
3. Stop immediately.

---

## Coding Rules

- Follow WordPress Coding Standards.
- PHP 7.4+
- Namespaces
- PSR-4
- Escape Output
- Sanitize Input
- Verify Nonces
- Capability Checks
- Keep methods small
- Never modify completed code unless requested.

---

## Documentation Rules

- TASKS.md controls implementation order.
- docs/ contains implementation documentation.
- PLUGIN_PROPOSAL.md is the source of truth.
- If code changes affect documentation, update the relevant docs before stopping.
- Never leave TASKS.md and docs inconsistent.

---

## 1. Project Identity

- **Plugin name:** AccessiWoo (working title per proposal §1)
- **Purpose:** Scan WooCommerce stores for WCAG 2.1 AA violations, auto-fix common ones, generate EAA compliance statements
- **License:** GPLv2 or later — every bundled dependency must be GPL-compatible (axe-core MIT ✓, DomPDF LGPL 2.1 ✓)
- **Distribution:** WordPress.org (free) + Freemius (Pro). Free version must pass WordPress.org plugin review on the first submission.
- **Repo note:** the development folder is `accessi-compliance-kit/`; the plugin's internal structure follows proposal §5.2 (`accessiwoo.php`, `src/`, `assets/`, etc.).

## 2. Absolute Constraints (never violate)

1. **Zero external calls in the free version.** No CDN scripts, no telemetry, no phone-home, no external APIs. axe-core is bundled locally. Scan data never leaves the merchant's server (proposal §5.5).
2. **No overlay widgets, no JS that hides violations instead of fixing them, no AI content generation, no automated legal-risk scoring** (proposal §4.3 — explicitly out of scope).
3. **Every auto-fix ships OFF by default** and is individually toggleable (proposal §9 risk mitigation). Never enable a fix without explicit user opt-in.
4. **Violations are always presented as "detected issues," never "confirmed violations"** — interpretation is left to the merchant (proposal §9).
5. **The free plugin must work fully without any `src/Pro/` class.** Pro code loads only when a valid license is detected (proposal §5.7).
6. **Do not add features not in the proposal.** If something seems missing, ask; don't invent.

## 3. Environment & Compatibility

- **PHP:** 7.4 minimum, code targeting 8.0+ (no PHP-8-only syntax such as constructor property promotion, `match`, enums, readonly properties, named-argument reliance)
- **WordPress:** 6.5+ (MVP success criterion, proposal §11)
- **WooCommerce:** 8.0+
- **Admin UI:** React via `@wordpress/element` and `@wordpress/components` — always use the WordPress-bundled packages as externals, never bundle a second React
- **Build tooling:** `@wordpress/scripts` (webpack config may extend it, never replace it)
- **Composer:** DomPDF and dev dependencies only; production autoloader is PSR-4

## 4. Naming & Code Conventions

- **Prefix everything** with `accessiwoo` / `accessiwoo_` / `ACCESSIWOO_`: options, transients, AJAX actions, script/style handles, DB tables (`{$wpdb->prefix}accessiwoo_*`), REST namespace (`accessiwoo/v1`), CSS classes (`.accessiwoo-*`), JS globals.
- **Text domain:** `accessiwoo` — every user-facing string wrapped in `__()` / `_e()` / `esc_html__()` etc. with this domain. No variable text domains.
- **PHP namespace:** `AccessiWoo\` mapping to `src/` via PSR-4 (`AccessiWoo\Admin\AdminMenu` → `src/Admin/AdminMenu.php`). One class per file, filename equals class name.
- **Coding standard:** WordPress Coding Standards (WPCS) for PHP; `@wordpress/eslint-plugin` defaults for JS. See `docs/coding-guidelines.md`.
- **Hooks fired by the plugin** are prefixed `accessiwoo_` (e.g. `accessiwoo_after_scan_saved`).

## 5. Security Rules (non-negotiable, see docs/security.md for detail)

- Sanitize every input: `sanitize_text_field()`, `esc_url_raw()`, `absint()`, etc. — at the point of entry.
- Escape every output: `esc_html()`, `esc_attr()`, `esc_url()`, `wp_kses_post()` — at the point of output. Never trust stored data.
- Every AJAX/REST/form handler checks **both** a nonce and a capability. Default capability: `manage_options` (refined in `Utils/Capabilities.php`).
- All `$wpdb` queries use `$wpdb->prepare()` with placeholders. No string-interpolated SQL.
- No `eval()`, no `extract()`, no obfuscated code, no `base64_decode()` of executable payloads (all WordPress.org rejection triggers).
- Direct file access guard (`if ( ! defined( 'ABSPATH' ) ) exit;`) at the top of every PHP file.
- The front-end scanner script loads **only** when `?accessiwoo_scan=1` is present **and** the current user has the scan capability (proposal §5.5, step 3).

## 6. Architecture Rules

- Follow the file/folder structure in proposal §5.2 exactly. New files must fit an existing folder's responsibility; if none fits, stop and ask.
- `Plugin.php` is a singleton bootstrap; it wires services, it does not contain feature logic.
- Every auto-fix is a class extending `AbstractFix` implementing `is_enabled()`, `applies_to()`, `register()` (proposal §5.6). `FixManager` is the only place fixes are instantiated.
- Activation logic lives in `Activator.php` (table creation via `dbDelta()`), deactivation in `Deactivator.php` (no data deletion), uninstall cleanup in `uninstall.php` only.
- Options access goes through `Utils/Options.php`; capability checks through `Utils/Capabilities.php`. Don't call `get_option()`/`current_user_can()` ad hoc in feature code.
- Scan storage goes through `Scanner/ScanStorage.php`; nothing else touches `wp_accessiwoo_scans`.
- Use the hooks enumerated in proposal §5.4. `woocommerce_locate_template` overrides only as a last resort ("use sparingly").

## 7. Workflow Rules for AI Sessions

1. **One task at a time.** Work from `TASKS.md`, complete the task, check its box, stop. Don't bundle unrelated tasks into one change.
2. **Don't refactor code outside the current task's scope** unless the task says so.
3. **Every fix class gets a PHPUnit test**; `FixManager` behavior (only enabled fixes register) is tested (proposal §10 example prompt).
4. **Never mark an MVP success criterion (proposal §11) as met without verifying it** on a real install.
5. When generating a file listed in proposal §5.2, use exactly that path and name.
6. Keep `readme.txt` (WordPress.org format) and `PLUGIN_PROPOSAL.md` in sync with shipped behavior; the proposal is a living spec (proposal §10, item 5).
7. Commit messages describe what changed and reference the TASKS.md task.
8. Do not commit `node_modules/`, `build/`, or generated bundles (gitignored per §5.2); `vendor/` policy is decided at packaging time — Composer install is part of the release build.

## 8. Free vs Pro Boundary

- MVP = free tier only: single-page scanner, 6 free fixes, statement generator (EN), dashboard widget, admin page, weekly email reminder (opt-in), admin-bar notice (proposal §4.1).
- Pro work (crawler, scheduling, PDF reports, advanced fixes, Freemius) begins **only after** the free launch phase — post-launch at ~500+ installs (proposal §6, Phase 5). Tasks for it exist in TASKS.md but are gated.
- Never reference Pro classes from free code paths except through the guarded loader in `Plugin.php`.
