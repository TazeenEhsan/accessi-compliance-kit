# Database — Accessibility Compliance Kit for WooCommerce

Implements proposal §5.3. The schema below is the proposal's schema; do not alter columns without updating `PLUGIN_PROPOSAL.md` first.

---

## 1. Custom Table: `{$wpdb->prefix}accessibility_compliance_kit_for_woocommerce_scans`

Created on activation by `src/Activator.php` via `dbDelta()`. Read/written **only** through `src/Scanner/ScanStorage.php`.

| Column | Type | Notes |
|---|---|---|
| `id` | `BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY` | |
| `scan_type` | `VARCHAR(20)` | `'single'` (free), `'crawl'` (Pro) |
| `url` | `TEXT` | Page scanned |
| `started_at` | `DATETIME` | |
| `completed_at` | `DATETIME NULL` | NULL while running |
| `status` | `VARCHAR(20)` | `'running'`, `'complete'`, `'failed'` |
| `violations_json` | `LONGTEXT` | Normalized axe-core output (see §4) |
| `summary_json` | `TEXT` | Counts by severity for quick display |
| `triggered_by` | `BIGINT UNSIGNED` | WP user ID |

Implementation notes:

- Use the site's charset/collation via `$wpdb->get_charset_collate()`.
- Add a `KEY status (status)` and `KEY started_at (started_at)` index for the history listing and "last scan" lookups (index-only addition; column set stays exactly as specified).
- Store datetimes in UTC (`current_time( 'mysql', true )` / `gmdate`); format for display with the site timezone.
- Store the schema version in the `accessibility_compliance_kit_for_woocommerce_db_version` option; on upgrade, `Plugin` compares and re-runs `dbDelta()` when it changes.

## 2. Scan Row Lifecycle

1. `create_scan( $url, $type, $user_id )` → inserts row: `status = 'running'`, `started_at = now`, empty JSON columns.
2. On successful result: `complete_scan( $id, $violations, $summary )` → `status = 'complete'`, `completed_at = now`, JSON columns filled, `accessibility_compliance_kit_for_woocommerce_last_scan_id` option updated.
3. On error/timeout: `fail_scan( $id, $reason )` → `status = 'failed'`, `completed_at = now` (reason may be stored inside `summary_json`).

All queries use `$wpdb->prepare()`; JSON is encoded with `wp_json_encode()` and decoded defensively (invalid JSON → treated as failed scan, logged).

## 3. Options (`wp_options`) — proposal §5.3

| Option | Contents | Autoload |
|---|---|---|
| `accessibility_compliance_kit_for_woocommerce_settings` | Plugin configuration array (email opt-in, statement page ID, misc settings) | yes |
| `accessibility_compliance_kit_for_woocommerce_active_fixes` | Map of fix ID → bool; which fixes are enabled. **All false by default** (proposal §9) | yes |
| `accessibility_compliance_kit_for_woocommerce_license` | Pro license data (via Freemius or custom) — Phase 5 | yes |
| `accessibility_compliance_kit_for_woocommerce_last_scan_id` | Pointer for the dashboard widget | yes |

Plus the internal `accessibility_compliance_kit_for_woocommerce_db_version` (schema version). All option access goes through `src/Utils/Options.php` — no ad-hoc `get_option()` calls in feature code.

## 4. Stored JSON Shapes

`ViolationParser` normalizes raw axe-core output before storage so the UI and future reports don't depend on axe's exact format.

**`violations_json`** — array of:

```json
{
  "rule": "image-alt",
  "impact": "critical",
  "description": "why it fails (axe description)",
  "help": "how to fix (axe help text)",
  "help_url": "https://dequeuniversity.com/rules/axe/...",
  "nodes": [
    { "selector": "css > selector", "html": "<img src=...>", "failure_summary": "..." }
  ]
}
```

**`summary_json`**:

```json
{ "critical": 3, "serious": 5, "moderate": 2, "minor": 1, "total": 11 }
```

Severity buckets are exactly the four from proposal §4.1: Critical, Serious, Moderate, Minor (axe-core's `impact` values map 1:1). Each violation must carry rule name, affected element selector, why it fails, and how to fix (proposal §4.1).

Cap stored node HTML snippets to a sane length (e.g. 500 chars per node) so `LONGTEXT` rows stay manageable on pathological pages.

## 5. Cleanup Rules

- **Deactivation** (`Deactivator.php`): unschedule cron events only. Never delete data.
- **Uninstall** (`uninstall.php`): drop `{$wpdb->prefix}accessibility_compliance_kit_for_woocommerce_scans`, delete all `accessibility_compliance_kit_for_woocommerce_*` options. Guarded by `defined( 'WP_UNINSTALL_PLUGIN' )`.

## 6. Pro-Era Additions (Phase 5 — design later, don't build now)

The proposal implies future storage needs that must NOT be built in the MVP: crawl queues (§4.2.A), violation mutes/justifications (§4.2.A), remediation status + comments + activity log (§4.2.E), statement version history (§4.2.D), immutable audit history (§4.2.C). When Phase 5 starts, design these as separate tables — do not overload `wp_accessibility_compliance_kit_for_woocommerce_scans`.
