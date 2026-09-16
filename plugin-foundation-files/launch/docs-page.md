# Accessibility Compliance Kit for WooCommerce — Documentation

> Source content for the public docs page (publish on the plugin site / accessiwoo.com before WP.org launch). Also the reference for support-forum answers — see `support-templates.md` for canned responses and `faq.md` for the public FAQ.
>
> **This content also ships inside the plugin** as the Accessibility → User Guide page (`src/Admin/GuidePage.php`), since no external docs site exists yet. When you change anything here, update `GuidePage.php` too (and vice versa). Note: the plugin now has a top-level "Accessibility" admin menu; **WooCommerce → Accessibility** remains as a pointer link, so the navigation paths written below still work.

## 1. Requirements

- WordPress 6.5 or newer
- WooCommerce 8.0 or newer (must be active — the plugin shows a notice and stays idle without it)
- PHP 7.4 or newer
- A user with the Administrator role (`manage_options`) to run scans and change settings

## 2. Installation

1. **Plugins → Add New**, search for "Accessibility Compliance Kit for WooCommerce", click **Install Now**, then **Activate**. (Or upload the zip via **Plugins → Add New → Upload Plugin**.)
2. On activation the plugin creates one database table for scan results and seeds its default settings. **All fixes start OFF** — activating the plugin changes nothing on your store's front end.
3. Find the plugin under **WooCommerce → Accessibility**.

## 3. Running a scan

### From the admin page

1. Go to **WooCommerce → Accessibility → Scan** tab.
2. The URL field is pre-filled with your home page; paste any URL **on your own site** (other domains are rejected by design).
3. Click **Scan this page**. The page loads in a hidden frame and is checked in your browser by the bundled axe-core engine — nothing is sent anywhere.
4. Results appear grouped by severity: **Critical, Serious, Moderate, Minor**. Expand a row to see the affected element's HTML, the CSS selector, why it was flagged, and a link to detailed remediation guidance.

### From the front end

While browsing your store logged in as an admin, click **Scan this page** in the admin bar — it opens the Scan tab with the current URL pre-filled.

### Scan history

The **History** tab lists past scans (date, URL, status, per-severity counts). Click a row to reopen its full results. Results are stored in your own database and never leave your server.

## 4. The six fixes

Enable each fix under **WooCommerce → Accessibility → Settings**. Each is independent; enable one at a time and re-check your store. All are OFF by default.

| Fix | What it does | Visible change? |
|---|---|---|
| Product image alt text | Fills in missing `alt` attributes on product images using the product title | No |
| Checkout field labels | Adds a screen-reader label to checkout fields that only show a placeholder | No |
| Visible focus states | Adds a high-contrast outline to links, buttons, and form controls when focused via keyboard | Yes (on keyboard focus only — this is what WCAG requires) |
| Icon button labels | Adds accessible names to icon-only cart, search, and wishlist controls | No |
| Screen reader price label | Prepends a visually-hidden "Price:" label so screen readers announce prices correctly | No |
| Empty link names | Gives accessible names to empty links, such as product image links | No |

**If a fix conflicts with your theme:** switch it off (the change is removed immediately) and report the theme + fix combination in the support forum so we can improve compatibility.

## 5. The accessibility statement

1. Go to **WooCommerce → Accessibility** and find the **Accessibility Statement** card.
2. Click **Create statement page**. A WordPress page titled "Accessibility Statement" is created from an EAA-oriented English template, pre-filled with your site name, contact details, and the current date.
3. **Edit the page before publishing.** The template contains placeholders — notably the compliance level you claim and the "known limitations" section — that only you can fill in truthfully.
4. If a statement page already exists, the plugin warns you instead of creating a duplicate; creating a fresh page is an explicit second action.
5. Link the published page from your site footer (most stores put it next to the privacy policy).

## 6. Dashboard widget & notifications

- **Dashboard widget:** last scan date and detected-issue counts by severity, with a link to the full results.
- **Admin-bar notice:** shown when your most recent scan detected critical issues.
- **Weekly email reminder (opt-in):** enable it in **Settings** to get a weekly email nudging you to re-scan. Uses WP-Cron and your site's normal mail configuration; no external service.

## 7. Privacy & data

- Zero external calls. The axe-core engine ships inside the plugin; scans run in your browser; results live in your database.
- The plugin stores: scan results (one table), its settings, and the ID of the generated statement page.
- Deactivating keeps your data. **Deleting** the plugin via the Plugins screen removes the scans table and all plugin options.

## 8. Troubleshooting

**The scan times out / never finishes.**
The scanned page must load in an iframe from your own site. Security plugins or headers that block framing from the same origin (`X-Frame-Options: DENY`, a strict `frame-ancestors` CSP) will prevent the scan. Allow same-origin framing and retry. Scans also require you to be logged in as an admin — a scan fails if the URL redirects (e.g. to a login page).

**"This URL is not part of this site."**
By design the scanner only accepts URLs on your own domain. Check for a different subdomain, protocol, or a trailing typo.

**The menu item is missing.**
The plugin registers nothing until WooCommerce 8.0+ is active — check **Plugins** for WooCommerce. The menu lives under **WooCommerce → Accessibility** and requires an administrator account.

**A fix doesn't seem to do anything.**
Most fixes are invisible by design (screen-reader text, `alt`/`aria` attributes). Verify with your browser's element inspector, or re-run a scan of the same page and compare detected-issue counts. Also check for full-page caching — purge the cache after toggling a fix.

**The scan reports issues from my theme or page builder.**
The scanner reports everything on the rendered page regardless of origin. The per-issue guidance describes what needs to change; changes to theme markup need to happen in the theme (or a child theme), not in this plugin.

## 9. For developers

- All AJAX actions, options, hooks, CSS classes, and script handles are prefixed `accessibility_compliance_kit_for_woocommerce_` / `.tazeen-store-accessibility-kit-for-woocommerce-`.
- When any fix is active, `<body>` gets the class `tazeen-store-accessibility-kit-for-woocommerce-fixes-active` plus one class per active fix — use these to scope your own CSS overrides.
- Scan results live in the `{$wpdb->prefix}accessibility_compliance_kit_for_woocommerce_scans` table; treat it as read-only.
- Source JS is included in the plugin under `assets/js/src/`; builds use `@wordpress/scripts`.
