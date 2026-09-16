# REST API — Accessibility Compliance Kit for WooCommerce

Status per proposal: **optional / future**. Proposal §5.4 lists `register_rest_route( 'tazeen-store-accessibility-kit-for-woocommerce/v1', '/scans', ... )` as "REST API (optional, for future SPA admin)", and full REST endpoints are a Pro integration feature (§4.2.F: "read scans, trigger scans, fetch reports for external tooling", enabling CI/CD per §4.2.I).

**MVP decision:** the free MVP uses admin-ajax (§5.4, §5.5) — do NOT build REST endpoints in Phases 0–4. This document pre-specifies the namespace and shapes so the AJAX layer is designed to migrate cleanly later.

---

## 1. Namespace & Conventions

- Namespace: `tazeen-store-accessibility-kit-for-woocommerce/v1` (proposal §5.4)
- Registration on `rest_api_init`, controllers under `src/` (Pro-era: `src/Pro/` or a shared `Rest/` folder decided at Phase 5)
- Auth: standard WordPress REST cookie auth + nonce for the SPA; Application Passwords for external tooling/CI. Never a custom auth scheme, never unauthenticated access.
- Every route declares `permission_callback` using `Utils/Capabilities` (no `__return_true` anywhere).
- All input via `args` schemas (type, sanitize_callback, validate_callback); all output through `rest_ensure_response`.

## 2. Planned Routes (build in Phase 5 unless the SPA admin needs them sooner)

| Route | Method | Purpose | Capability |
|---|---|---|---|
| `/tazeen-store-accessibility-kit-for-woocommerce/v1/scans` | GET | List scans (paged; mirrors History tab data) | scan read |
| `/tazeen-store-accessibility-kit-for-woocommerce/v1/scans` | POST | Trigger a scan (Pro: server-driven crawl; enables CI/CD per §4.2.I) | scan run |
| `/tazeen-store-accessibility-kit-for-woocommerce/v1/scans/{id}` | GET | One scan with full normalized violations | scan read |
| `/tazeen-store-accessibility-kit-for-woocommerce/v1/reports/{id}` | GET | Fetch a generated report (Pro, §4.2.F) | report read |

Response shapes reuse the stored JSON structures from docs/database.md §4 — the AJAX handlers and future REST controllers must share `ScanStorage`/`ViolationParser`, so the payloads are identical by construction.

## 3. AJAX Actions (the MVP's actual API surface)

These are the admin-ajax actions the MVP implements instead (all nonce + capability checked, see docs/security.md):

| Action | Purpose |
|---|---|
| `accessibility_compliance_kit_for_woocommerce_run_scan` | Save posted scan results (proposal §5.4, §5.5 step 5) |
| `accessibility_compliance_kit_for_woocommerce_get_scan` | Fetch one scan's stored results |
| `accessibility_compliance_kit_for_woocommerce_get_scans` | Paged history list |
| `accessibility_compliance_kit_for_woocommerce_save_settings` | Save settings/fix toggles (proposal §5.4) |
| `accessibility_compliance_kit_for_woocommerce_generate_statement` | Create the statement page |

Design rule: AJAX handlers are thin — parse/authorize, call the same service classes (`ScanStorage`, `StatementGenerator`, `Options`) a REST controller would call. Migrating to REST later is then a routing change, not a rewrite.

## 4. Related Pro Interfaces (Phase 5, for context)

- **WP-CLI** (§4.2.F): `wp tazeen-store-accessibility-kit-for-woocommerce scan`, `wp tazeen-store-accessibility-kit-for-woocommerce report` — same service layer, enables staging scans on deploy.
- **Outgoing webhooks** (§4.2.E): Slack incoming-webhook URL + custom POST webhook. Outgoing HTTP only — consistent with the zero-external-dependency rule (§4.2 intro).
