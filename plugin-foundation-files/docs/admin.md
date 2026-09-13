# Admin UI — Accessibility Compliance Kit for WooCommerce

Implements proposal §4.1 (Admin dashboard, Notifications), §5.2 (Admin/ + assets/js/src/admin/), §5.5 (scan flow). React app on `@wordpress/element` + `@wordpress/components`, built with `@wordpress/scripts`.

---

## 1. Surfaces

| Surface | File(s) | What it shows |
|---|---|---|
| Plugin admin page | `src/Admin/AdminMenu.php`, `ScanPage.php`, React app | Full UI under **WooCommerce → Accessibility** (proposal §4.1) |
| Dashboard widget | `src/Admin/DashboardWidget.php` | Last scan date + violation count by severity (proposal §4.1, §11) |
| Admin bar | registered from Scanner service | "Scan this page" node on front-end (§5.5 step 1); warning notice when last scan found critical issues (§4.1) |
| Email | WP-Cron weekly | Opt-in scan reminder (§4.1) |

## 2. Admin Page Registration

- `AdminMenu.php` adds a submenu under the WooCommerce parent menu (`woocommerce`), slug `accessibility-compliance-kit-for-woocommerce`, capability from `Utils/Capabilities` (default `manage_options`).
- Callback renders only `<div id="accessibility-compliance-kit-for-woocommerce-admin"></div>`; everything else is React.
- `ScanPage.php` enqueues `build/admin.js` + `assets/css/admin.css` **only** on this screen (check `$hook_suffix`), and localizes:
  - `ajaxUrl`, per-action nonces
  - `homeUrl` (for the default scan target + same-origin validation)
  - `scannerToken` (postMessage handshake, docs/security.md §5)
  - severity labels (translated) and `lastScanId`

## 3. React App Structure (`assets/js/src/admin/`)

Per proposal §5.2: `App.jsx`, `ScanResults.jsx`, `Settings.jsx`, `Dashboard.jsx` (plus `ScanRunner.jsx` for iframe orchestration, and `ScanHistory.jsx` for the History tab, built in task 1.4).

```
App.jsx                — TabPanel: Dashboard | Scan | History | Settings
├── Dashboard.jsx      — accessibility statement status card (task 3, §7 below); last-scan summary is Phase 4 scope
├── ScanRunner.jsx     — URL input, "Scan this page" button, hidden iframe, progress state
├── ScanResults.jsx    — violations grouped by severity with expandable detail (used by both the Scan and History tabs)
├── ScanHistory.jsx    — paginated past-scans table, click-through loads a scan via ScanResults
└── Settings.jsx       — fix toggles + email opt-in (task 2.3 — the Settings tab is a placeholder until then)

utils/ajax.js           — shared `admin-ajax.php` POST helper used by ScanRunner/ScanHistory/Dashboard/App
```

`Dashboard.jsx` currently holds only the statement status card (task 3). The last-scan summary described in §8 below stays on the separate WP dashboard widget (`src/Admin/DashboardWidget.php`, plain PHP, Phase 4 scope) — it has not been duplicated into this React tab.

Use `@wordpress/components` (`TabPanel`, `Card`, `Button`, `ToggleControl`, `Notice`, `Spinner`) — no custom design system.

## 4. Scan Tab Behavior (proposal §5.5)

1. URL input pre-filled with `homeUrl` (or the URL passed from the admin-bar link); must validate as same-site before enabling the button.
2. Click → create scan row (`running`) → mount hidden iframe `targetUrl + ?accessibility_compliance_kit_for_woocommerce_scan=1`.
3. Listen for `message` events; accept only events whose payload carries the handshake token and whose origin matches the site origin.
4. On result → POST to `admin-ajax.php` action `accessibility_compliance_kit_for_woocommerce_run_scan` with nonce → show saved results.
5. Timeout (no message in ~60s) or iframe error → mark scan failed via AJAX, show error notice.
6. Results view: four severity groups (Critical, Serious, Moderate, Minor); each violation row = rule name, selector, why it fails, how to fix; expandable HTML snippet + axe help link. Word everything as **"detected issues"** (proposal §9).

## 5. History Tab

- Paged table from the `accessibility_compliance_kit_for_woocommerce_get_scans` AJAX action: date (site timezone), URL, status, severity counts.
- Row click loads that scan's stored results into the results view (`accessibility_compliance_kit_for_woocommerce_get_scan`).

## 6. Settings Tab (proposal §4.1 "Basic auto-fixes — toggleable per fix in Settings")

- One `ToggleControl` per fix: label, one-line description, context badge from `applies_to()` (product / checkout / cart / global).
- **All toggles default OFF** — user opts in per fix (proposal §9).
- Email reminder opt-in toggle (weekly scan reminder, §4.1).
- Save via `accessibility_compliance_kit_for_woocommerce_save_settings` AJAX (nonce + capability); optimistic UI with success/error `Notice`.
- Server side (`SettingsPage.php`): register/sanitize via Settings API on `admin_init`; whitelist known fix IDs, cast to booleans.

## 7. Statement Card (proposal §4.1, §6 Phase 3)

On the **Dashboard tab** of the admin page (`Dashboard.jsx`):

- If no statement page exists: "Create statement page" button → `accessibility_compliance_kit_for_woocommerce_generate_statement` AJAX (`force_new: '0'`) → `StatementGenerator::generate()` creates the "Accessibility Statement" page from the EN template (`src/Statement/templates/en.php`) as a **draft** → card shows the edit link.
- If it exists (page ID stored in `accessibility_compliance_kit_for_woocommerce_settings['statement_page_id']`, initial state localized as `statement.pageId`/`statement.editLink` in `ScanPage.php`): the card shows the edit link plus a "Create new statement page" button. That button re-posts with `force_new: '1'`, which bypasses the exists-check and inserts a separate page — the plain "Create statement page" click never silently duplicates; the AJAX handler returns `status: 'exists'` instead when a page is on record and `force_new` wasn't sent, and the card surfaces that as a warning notice.
- If the stored page was deleted, `StatementGenerator::generate()` treats it as absent (checks `get_post_type()`) and creates a fresh one on the next click.
- The merchant edits the page normally afterward (proposal §4.1).

## 8. Dashboard Widget (proposal §4.1)

- `wp_add_dashboard_widget` for users with the plugin capability.
- Reads `accessibility_compliance_kit_for_woocommerce_last_scan_id` → shows last scan date + counts by severity + link to the full plugin page. Plain PHP/escaped HTML — no React needed here.
- Empty state: "No scans yet" + link to run the first scan.

## 9. Notifications (proposal §4.1)

- **Admin-bar notice:** when the last scan's summary has `critical > 0`, add a highlighted admin-bar item linking to the results.
- **Weekly email reminder:** opt-in only. WP-Cron event `accessibility_compliance_kit_for_woocommerce_weekly_reminder` scheduled when the setting turns on, cleared when it turns off and on deactivation. Email: plain, translated, links to the scan page. Sent via `wp_mail()` — no external service.

## 10. Copy Rules

- All strings translatable, text domain `accessibility-compliance-kit-for-woocommerce` (JS via `@wordpress/i18n`).
- Violations are "detected issues," never "confirmed violations" (proposal §9).
- No dark patterns, no nag screens; the review prompt (proposal §8) appears only after 30 days of usage and is dismissible — build it in Phase 4 launch prep or shortly after.
