# Security — Accessi Compliance Kit

Security is a launch gate: proposal §11 requires passing WordPress.org review ("no external calls, sanitized inputs, escaped outputs, GPL-compatible dependencies") and §9 lists review rejection as a named risk. This document is the enforceable checklist.

---

## 1. Threat Model (what we're protecting)

- **Admin-only tooling**: scans, settings, statement generation are privileged operations — vectors are CSRF, privilege escalation, and injection via stored scan data.
- **Stored scan payloads contain attacker-influenceable HTML** (snippets of the scanned page, which may include user-generated content like product reviews). Treat `violations_json` as untrusted on every render.
- **Front-end surface**: the scanner script and fix scripts run on the public site — they must never expose data or load for unauthorized users.
- **Reputation/compliance**: any external call in the free version is a WordPress.org rejection and a proposal violation (§5.1: "Nothing calls an external server").

## 2. Input Handling

- Sanitize at entry, every time: `sanitize_text_field`, `sanitize_key`, `absint`, `esc_url_raw`, `rest_sanitize_boolean` equivalents.
- Scan payload (axe results from the browser) is JSON: decode with `json_decode`, validate structure in `ViolationParser` (expected keys, impact whitelist `critical|serious|moderate|minor`, selectors/strings length-capped), discard anything unexpected.
- Settings: whitelist known fix IDs; booleans cast strictly; unknown keys dropped.
- Scan URL: `esc_url_raw` + **same-site host check** against `home_url()` — the scanner must not be usable to probe arbitrary external URLs.

## 3. Output Escaping

- Escape at output, every time: `esc_html`, `esc_attr`, `esc_url`, `wp_kses_post` (statement template content).
- Stored violation HTML snippets are **displayed as code**, never rendered: `esc_html` them (or render in `<code>` via React text nodes, which escape by default). Never `dangerouslySetInnerHTML` with scan data.
- Statement template output through `wp_kses_post` when inserted into the page.

## 4. AJAX / Forms (every handler, no exceptions)

Each `wp_ajax_accessi_compliance_kit_*` handler must, in order:

1. `check_ajax_referer( 'accessi_compliance_kit_<action>', ... )` — per-action nonces localized to the admin app
2. Capability check via `Utils/Capabilities` (default `manage_options`)
3. Sanitize/validate all inputs (§2)
4. Do the work through service classes
5. Respond with `wp_send_json_success` / `wp_send_json_error` (proper status codes; no raw echo)

No `wp_ajax_nopriv_*` handlers exist in this plugin. Ever.

## 5. Scanner postMessage Channel (proposal §5.5)

The iframe → parent `postMessage` path is a spoofing surface:

- Server generates a one-time/random **handshake token**, localized to both the admin app and the scanner script (only rendered for capable users).
- Parent (admin app) accepts a `message` event only if: `event.origin` equals the site origin AND the payload carries the matching token.
- Scanner posts with an explicit `targetOrigin` (site origin), never `*`.
- The saved result is still fully validated server-side (§2) — the token gates UI acceptance, not persistence trust.
- Scanner script itself is enqueued only when `?accessi_compliance_kit_scan=1` AND `Capabilities::can_scan()` (proposal §5.5 step 3) — anonymous requests with the flag get nothing.

## 6. Capabilities & Access

- Central definition in `src/Utils/Capabilities.php`; default `manage_options` for all MVP operations.
- Dashboard widget, admin-bar items, menu pages all gated by the same helpers.
- Pro later adds the read-only "Accessibility Auditor" role (§4.2.E) — the central helper design exists so that lands in one file.

## 7. Database

- 100% of queries through `$wpdb->prepare()` with `%d`/`%s` placeholders; table name interpolated only from `$wpdb->prefix . 'accessi_compliance_kit_scans'`.
- `dbDelta()` for schema; no raw `CREATE`/`ALTER` from request handlers.
- JSON stored via `wp_json_encode`; decode failures logged and treated as failed scans, never fatal.

## 8. Platform & Supply Chain

- `if ( ! defined( 'ABSPATH' ) ) exit;` at the top of every PHP file; `uninstall.php` guards on `WP_UNINSTALL_PLUGIN`.
- No `eval`, `extract`, `create_function`, dynamic includes from user input, or obfuscated code (WP.org rejection triggers, §9).
- Dependencies: axe-core (MIT) and DomPDF (LGPL 2.1) are GPL-compatible and **bundled** — no CDN, no runtime downloads (§5.1). Pin versions; review changelogs on upgrade.
- Free version makes zero outbound HTTP requests. Pro's Slack/custom webhooks (§4.2.E) are outgoing-only to merchant-configured URLs.
- Email via `wp_mail()` only; opt-in enforced before scheduling the reminder cron.
- Nonces + capability on the statement generator (it creates content); created page is authored as the acting user.

## 9. Pre-Release Security Checklist (run in Phase 4, tasks 4.2)

- [ ] Every AJAX handler: nonce ✚ capability ✚ sanitized input ✚ JSON response
- [ ] Every output path escapes (grep for `echo`/`printf` in `src/` and verify)
- [ ] Every `$wpdb` call uses `prepare`
- [ ] Every PHP file has the ABSPATH guard
- [ ] Scanner script provably absent for logged-out users and users without capability
- [ ] postMessage handler rejects wrong-origin and token-less messages
- [ ] No outbound HTTP anywhere in the free build (grep `wp_remote_`, `curl`, `file_get_contents` with URLs)
- [ ] Uninstall removes the table and all `accessi_compliance_kit_*` options
- [ ] WPCS + Plugin Check pass clean
