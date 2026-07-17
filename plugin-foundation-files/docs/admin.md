# Admin UI — Accessi Compliance Kit

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

- `AdminMenu.php` adds a submenu under the WooCommerce parent menu (`woocommerce`), slug `accessi-compliance-kit`, capability from `Utils/Capabilities` (default `manage_options`).
- Callback renders only `<div id="accessi-compliance-kit-admin"></div>`; everything else is React.
- `ScanPage.php` enqueues `build/admin.js` + `assets/css/admin.css` **only** on this screen (check `$hook_suffix`), and localizes:
  - `ajaxUrl`, per-action nonces
  - `homeUrl` (for the default scan target + same-origin validation)
  - `scannerToken` (postMessage handshake, docs/security.md §5)
  - severity labels (translated) and `lastScanId`

## 3. React App Structure (`assets/js/src/admin/`)

Per proposal §5.2: `App.jsx`, `ScanResults.jsx`, `Settings.jsx`, `Dashboard.jsx` (plus `ScanRunner.jsx` for iframe orchestration, and `ScanHistory.jsx` for the History tab, built in task 1.4).

```
App.jsx                — TabPanel: Scan | History | Settings
├── ScanRunner.jsx     — URL input, "Scan this page" button, hidden iframe, progress state
├── ScanResults.jsx    — violations grouped by severity with expandable detail (used by both the Scan and History tabs)
├── ScanHistory.jsx    — paginated past-scans table, click-through loads a scan via ScanResults
└── Settings.jsx       — fix toggles + email opt-in (task 2.3 — the Settings tab is a placeholder until then)

utils/ajax.js           — shared `admin-ajax.php` POST helper used by ScanRunner/ScanHistory/App
```

`Dashboard.jsx` (last-scan summary + statement status card) is not part of task 1.4's scope and has not been built yet; it belongs with the Phase 3/4 statement and dashboard-widget work.

Use `@wordpress/components` (`TabPanel`, `Card`, `Button`, `ToggleControl`, `Notice`, `Spinner`) — no custom design system.

## 4. Scan Tab Behavior (proposal §5.5)

1. URL input pre-filled with `homeUrl` (or the URL passed from the admin-bar link); must validate as same-site before enabling the button.
2. Click → create scan row (`running`) → mount hidden iframe `targetUrl + ?accessi_compliance_kit_scan=1`.
3. Listen for `message` events; accept only events whose payload carries the handshake token and whose origin matches the site origin.
4. On result → POST to `admin-ajax.php` action `accessi_compliance_kit_run_scan` with nonce → show saved results.
5. Timeout (no message in ~60s) or iframe error → mark scan failed via AJAX, show error notice.
6. Results view: four severity groups (Critical, Serious, Moderate, Minor); each violation row = rule name, selector, why it fails, how to fix; expandable HTML snippet + axe help link. Word everything as **"detected issues"** (proposal §9).

## 5. History Tab

- Paged table from the `accessi_compliance_kit_get_scans` AJAX action: date (site timezone), URL, status, severity counts.
- Row click loads that scan's stored results into the results view (`accessi_compliance_kit_get_scan`).

## 6. Settings Tab (proposal §4.1 "Basic auto-fixes — toggleable per fix in Settings")

- One `ToggleControl` per fix: label, one-line description, context badge from `applies_to()` (product / checkout / cart / global).
- **All toggles default OFF** — user opts in per fix (proposal §9).
- Email reminder opt-in toggle (weekly scan reminder, §4.1).
- Save via `accessi_compliance_kit_save_settings` AJAX (nonce + capability); optimistic UI with success/error `Notice`.
- Server side (`SettingsPage.php`): register/sanitize via Settings API on `admin_init`; whitelist known fix IDs, cast to booleans.

## 7. Statement Card (proposal §4.1, §6 Phase 3)

On the Dashboard area of the admin page:

- If no statement page exists: "Create statement page" button → `accessi_compliance_kit_generate_statement` AJAX → creates the "Accessibility Statement" page from the EN template → show link to edit it.
- If it exists (page ID stored in `accessi_compliance_kit_settings`): show status + edit link; re-creating requires explicit confirmation (no silent duplicates). The merchant edits the page normally afterward (proposal §4.1).
- Decision (implementation detail, does not change requirements): create the page as **draft** so the merchant reviews before publishing; surface this in the success message.

## 8. Dashboard Widget (proposal §4.1)

- `wp_add_dashboard_widget` for users with the plugin capability.
- Reads `accessi_compliance_kit_last_scan_id` → shows last scan date + counts by severity + link to the full plugin page. Plain PHP/escaped HTML — no React needed here.
- Empty state: "No scans yet" + link to run the first scan.

## 9. Notifications (proposal §4.1)

- **Admin-bar notice:** when the last scan's summary has `critical > 0`, add a highlighted admin-bar item linking to the results.
- **Weekly email reminder:** opt-in only. WP-Cron event `accessi_compliance_kit_weekly_reminder` scheduled when the setting turns on, cleared when it turns off and on deactivation. Email: plain, translated, links to the scan page. Sent via `wp_mail()` — no external service.

## 10. Copy Rules

- All strings translatable, text domain `accessi-compliance-kit` (JS via `@wordpress/i18n`).
- Violations are "detected issues," never "confirmed violations" (proposal §9).
- No dark patterns, no nag screens; the review prompt (proposal §8) appears only after 30 days of usage and is dismissible — build it in Phase 4 launch prep or shortly after.
