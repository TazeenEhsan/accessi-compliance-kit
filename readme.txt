=== Tazeen Store Accessibility Kit for WooCommerce ===
Contributors: tazeenehsan
Tags: accessibility, wcag, woocommerce, compliance, a11y
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Scan your WooCommerce store for accessibility issues (WCAG 2.1 A/AA), apply optional fixes, and generate an accessibility statement page.

== Description ==

**Tazeen Store Accessibility Kit for WooCommerce** helps WooCommerce store owners work toward the accessibility level that the European Accessibility Act (EAA) and WCAG 2.1 AA expect from online shops — without overlays, external services, or data ever leaving your site.

= Scanning =

* One-click accessibility scan of any front-end page of your store
* Powered by the industry-standard [axe-core](https://github.com/dequelabs/axe-core) engine (MPL-2.0 licensed, bundled locally — no CDN, no external calls)
* Detected issues grouped by severity: Critical, Serious, Moderate, Minor
* Every detected issue shows the rule name, the affected element (CSS selector), why it was flagged, and how to fix it
* Scan by URL, or jump straight from any front-end page via the "Scan this page" admin-bar button
* Scan history saved locally so you can track progress over time

= Optional fixes (each one individually toggleable, all OFF by default) =

* **Product image alt text** — fills in missing alt text on product images using the product title
* **Checkout field labels** — adds a screen-reader label to checkout fields that only show a placeholder
* **Visible focus states** — adds a high-contrast focus outline to links, buttons, and form controls
* **Icon button labels** — adds accessible names to icon-only cart, search, and wishlist controls
* **Screen reader price label** — announces "Price:" before prices so screen readers read them correctly
* **Empty link names** — gives accessible names to empty links, such as product image links

Nothing is changed on your store until you explicitly switch a fix on. Every fix can be turned off again at any time.

= Accessibility statement =

* One-click generator creates a WordPress page titled "Accessibility Statement" pre-populated with an EAA-oriented template
* Template covers: compliance level claimed, known limitations, contact for accessibility issues, and date of last review
* You edit and publish the page like any other WordPress page

= Admin dashboard =

* Dashboard widget summarizing your last scan: date and detected-issue counts by severity
* Full admin page under **WooCommerce → Accessibility**
* Admin-bar notice when the last scan detected critical issues
* Optional (opt-in) weekly email reminder to run a scan

### Source Code

[Source Code](https://github.com/TazeenEhsan/accessi-compliance-kit/tree/dev)

= Privacy: everything stays on your server =

The free version makes **zero external calls**. The scanning engine is bundled with the plugin, scans run in your own browser against your own site, and results are stored only in your own WordPress database. No telemetry, no phone-home, no accounts.

= What this plugin is not =

* It is **not an overlay widget**. It changes real markup (only when you opt in) instead of layering a toolbar over inaccessible pages.
* It does **not guarantee legal compliance**. Automated tools detect a subset of accessibility issues; results are presented as *detected issues* for you to review, and only a human audit can confirm conformance. This plugin is not legal advice.

== Installation ==

1. Install via **Plugins → Add New** (search for "Tazeen Store Accessibility Kit for WooCommerce"), or upload the plugin zip via **Plugins → Add New → Upload Plugin**.
2. Activate the plugin. WooCommerce 8.0+ must be installed and active.
3. Go to **WooCommerce → Accessibility**.
4. Run your first scan from the **Scan** tab (your home page is pre-filled — any page on your site works).
5. Review detected issues, then enable the fixes you want under the **Settings** tab (all fixes start OFF).
6. Optionally generate your Accessibility Statement page from the statement card and edit it to match your store.

== Frequently Asked Questions ==

= Does this plugin make my store legally compliant with the EAA or ADA? =

No automated tool can do that, and you should be wary of any that claims to. Tazeen Store Accessibility Kit for WooCommerce detects a subset of WCAG 2.1 A/AA issues automatically, fixes several common ones when you opt in, and gives you a statement template — a strong, honest starting point. Full conformance also requires human review (e.g. of keyboard flows, content clarity, and color use in images). Nothing in this plugin is legal advice.

= Does the European Accessibility Act apply to my store? =

The EAA has been enforceable across the EU since June 28, 2025 and generally applies to online shops selling to EU consumers. Micro-enterprises (fewer than 10 employees **and** under €2M annual turnover) are exempt. If you are unsure, consult a legal professional — we cannot assess your obligations for you.

= Does any of my data leave my site? =

No. The scanner (axe-core) is bundled inside the plugin and runs in your own browser; results are stored in your own database. The free version makes no external requests of any kind.

= Is this an accessibility overlay? =

No. Overlay widgets layer a toolbar over your site without fixing the underlying markup, and regulators and disability advocates consider them inadequate. Tazeen Store Accessibility Kit for WooCommerce reports real issues in your pages and — only when you enable a fix — corrects the actual markup.

= Do I need WooCommerce? =

Yes. The plugin is built specifically for WooCommerce stores and requires WooCommerce 8.0 or newer to be active.

= Why are all fixes off by default? =

Every theme is different, and we never change your store's front end without your explicit opt-in. Enable fixes one at a time and check your store as you go; each fix can be disabled again instantly.

= Will the fixes change how my store looks? =

Most fixes are invisible (alt text, screen-reader labels, accessible names). "Visible focus states" is the exception by design: it shows a high-contrast outline around the element currently focused via keyboard — this is exactly what WCAG asks for.

= A scan reports issues that come from my theme or another plugin. Can Tazeen Store Accessibility Kit for WooCommerce fix those? =

The scanner reports everything it detects on the rendered page, whatever the source. The six included fixes target the most common WooCommerce storefront issues. Issues originating in a theme or another plugin may need to be fixed there — the scan's per-issue guidance tells you what to change.

== Screenshots ==

1. Scan results grouped by severity, with per-issue detail (selector, why it fails, how to fix).
2. Settings tab — one toggle per fix, everything OFF by default.
3. Accessibility statement generator card with one-click page creation.
4. Dashboard widget with the last scan summary by severity.

== Changelog ==

= 1.0.4 =
* Updated the plugin display name to "Tazeen Store Accessibility Kit for WooCommerce" in the plugin header, readme, and documentation.

= 1.0.3 =
* Completed the internal rename to "Tazeen Store Accessibility Kit for WooCommerce"; if you activated a prior version, deactivate and reactivate after updating.

= 1.0.2 =
* Renamed plugin internals to "Tazeen Store Accessibility Kit for WooCommerce": constants, classes, and text domain now use the TSAKW/Tazeen Store naming convention.
* Plugin main file renamed to tazeen-store-accessibility-kit-for-woocommerce.php; translation file renamed to match.

= 1.0.1 =
* Renamed from "Accessi Compliance Kit" to "Accessibility Compliance Kit for WooCommerce" prior to first public release.
* Plugin slug/text domain changed to "tazeen-store-accessibility-kit-for-woocommerce" per WordPress.org plugin review.

= 1.0.0 =
* Initial release.
* Single-page accessibility scanner (axe-core, bundled) with severity-grouped results and scan history.
* Six optional fixes: product image alt text, checkout field labels, visible focus states, icon button labels, screen reader price label, empty link names — all individually toggleable, all OFF by default.
* One-click Accessibility Statement page generator (English template).
* Dashboard widget, admin-bar scan shortcut, critical-issue admin-bar notice, opt-in weekly email reminder.

== Upgrade Notice ==

= 1.0.4 =
Plugin display name updated to Tazeen Store Accessibility Kit for WooCommerce; no action needed.

= 1.0.3 =
Internal rename to Tazeen Store Accessibility Kit for WooCommerce; if you activated a prior version, deactivate and reactivate after updating.

= 1.0.1 =
Plugin renamed prior to first public release; no action needed.
