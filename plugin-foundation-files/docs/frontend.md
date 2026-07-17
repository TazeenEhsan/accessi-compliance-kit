# Frontend — Scanner & Auto-Fixes

Implements proposal §4.1 (Scanning, Basic auto-fixes), §5.5 (scan data flow), §5.6 (how auto-fixes work). Everything here runs on the merchant's front end.

---

## 1. Scanner Bundle (`assets/js/src/scanner/`)

- `runScan.js`: imports bundled axe-core (npm dependency — never CDN), runs `axe.run(document)`, formats results, `postMessage`s to the parent window (proposal §5.5, §10 prompt 3).
- Loading rule (proposal §5.5 step 3): the bundle is enqueued on the front end **only when** the request has `?accessi_compliance_kit_scan=1` **and** the current user passes `Capabilities::can_scan()`. Anonymous visitors never load it; there is zero front-end weight for normal traffic.
- Result formatting per violation: axe rule id, `impact` (maps to Critical/Serious/Moderate/Minor), description (why it fails), help text (how to fix), help URL, and per-node CSS selector + HTML snippet (proposal §4.1). Shape defined in docs/database.md §4.
- Message payload includes the handshake token localized into the script (docs/security.md §5) so the admin parent can reject spoofed messages.
- Free tier runs the default axe-core WCAG 2.1 A/AA rules. Extended rule sets (WCAG 2.2, Section 508, EN 301 549) are Pro (§4.2.A) — do not expose them in free.

## 2. Frontend Fix CSS (`assets/css/frontend-fixes.css`)

Enqueued via `wp_enqueue_scripts` only when at least one CSS-based fix is enabled. Keep it small and scoped; when fixes are active, `body_class` gets `accessi-compliance-kit-fixes-active` plus per-fix classes so CSS can target precisely (proposal §5.4).

## 3. Fix Framework (proposal §5.6)

Every fix extends `src/Fixes/AbstractFix.php`:

| Method | Contract |
|---|---|
| `id()` | stable slug, used as the settings key (e.g. `product_image_alt`) |
| `label()` / `description()` | translated strings for the Settings UI |
| `is_enabled()` | reads `accessi_compliance_kit_active_fixes` via `Utils/Options` |
| `applies_to()` | contexts: `'product'`, `'checkout'`, `'cart'`, `'global'` |
| `register()` | hooks the fix's filters/actions — called only when enabled |

`FixManager` instantiates all fixes on `init` and calls `register()` on enabled ones. Fixes never interact with each other. **All ship disabled** (proposal §9). Fixes must repair markup — never hide violations with JS (proposal §4.3).

## 4. The Six Free Fixes (proposal §4.1)

### 4.1 ProductImageAltFix — context: product
Fallback `alt` attributes for product images using the product title.
Hook: `wp_get_attachment_image_attributes` (proposal §5.4, §10 prompt 1). If `alt` is empty and the image belongs to a product context, set alt to the product title. Never overwrite a non-empty alt.

### 4.2 CheckoutLabelsFix — context: checkout
Add missing `<label>` associations on WooCommerce checkout form fields.
Hook: `woocommerce_form_field_args` (proposal §5.4). Ensure every field has an id-linked label; where WooCommerce hides labels visually, keep them present for screen readers (`screen-reader-text` class rather than removal).

### 4.3 FocusStatesFix — context: global
Visible focus states on all buttons and links via injected CSS (proposal §4.1).
Mechanism: enqueue `frontend-fixes.css` rules — a high-contrast `:focus-visible` outline (with `:focus` fallback) on `a, button, input, select, textarea, [tabindex]`. Must not remove theme focus styles, only guarantee visibility.

### 4.4 IconButtonAriaFix — context: global
`aria-label` on icon-only buttons: cart, search, wishlist icons (proposal §4.1).
Mechanism: a small front-end script (part of the fixes bundle, loaded only when this fix is on) that finds buttons/links with no accessible name and a known icon pattern, and adds a translated `aria-label`. This **adds** the missing accessible name to real markup — allowed; it is not overlay-style hiding (§4.3 distinction).

### 4.5 PriceScreenReaderFix — context: product, cart
Ensure prices are announced correctly: prepend `<span class="screen-reader-text">Price:</span>` to WooCommerce price HTML (proposal §4.1).
Hook: WooCommerce price output filter (e.g. `woocommerce_get_price_html`); guard against double-prefixing on repeated filter application.

### 4.6 EmptyLinkAnchorFix — context: product, global
Fix empty link anchors, e.g. product image links with no accessible name (proposal §4.1).
Mechanism: for WooCommerce loop product links, append `screen-reader-text` span with the product title (via the relevant WC loop filters); generic empty anchors handled by the same front-end script as 4.4 where server-side filtering can't reach.

Implementation note: fixes 4.4 and 4.6 need a tiny front-end JS file in addition to PHP filters (themes render icons client-side and markup varies). Keep it dependency-free, a few KB, enqueued only when those fixes are enabled. This is markup *repair* consistent with §5.6 — not an overlay widget.

## 5. Theme Compatibility (proposal §6 Phase 2, §11)

- Test matrix for every fix: Storefront, Astra, Kadence — individually enabled, then all six together. Front end must not visually break (launch criterion §11).
- Fixes must be defensive: bail silently if expected markup isn't found; never throw in the theme's render path.
- If a fix would ever require `woocommerce_locate_template` overrides, treat that as last resort (proposal §5.4 "use sparingly") and prefer filters.

## 6. Out of Scope on the Front End (proposal §4.3 — never build)

- Overlay widgets / accessibility toolbars (contrast toggles etc.)
- JS that hides violations rather than fixing markup
- Anything calling external services

## 7. Pro Fixes (Phase 5 — proposal §4.2.B)

The 17 advanced fixes (variation announcements, mini-cart focus trap, `aria-live` regions, gallery/lightbox focus, checkout steps, payment radios, coupon errors, review form, filters, ARIA tabs, carousels, skip link, landmarks, heading repair, page-builder packs, wishlist/compare packs) live under `src/Pro/AdvancedFixes/`, follow the same `AbstractFix` contract, and are specified when Phase 5 begins. Free code must not reference them.
