=== Store Accessibility Kit for WooCommerce ===
Contributors: tazeenehsan
Tags: accessibility, woocommerce, wcag, ada, eaa
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.5
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

WooCommerce accessibility checker and WCAG 2.1 AA scanner — detect issues, apply one-click fixes, and generate an EAA/ADA-ready accessibility statement.

== Description ==

**Store Accessibility Kit for WooCommerce** is the WooCommerce accessibility plugin for store owners who need to meet WCAG 2.1 AA — the standard referenced by the European Accessibility Act (EAA) and the ADA — without installing an overlay widget, signing up for an external service, or letting any data leave their site.

Run a free accessibility scan of your shop, product, and checkout pages in one click, see exactly which WCAG rules fail and why, fix the most common WooCommerce issues with a single toggle, and publish an accessibility statement — all from one screen under **WooCommerce → Accessibility**.

= Why store owners choose this plugin =

* **Built for WooCommerce**, not a generic site-wide scanner — checks product pages, cart, and checkout against the rules that actually break WooCommerce storefronts
* **Real fixes, not a toolbar.** Overlay widgets hide problems behind a floating menu; this plugin edits the underlying markup, and only the fixes you turn on
* **No account, no external calls, no recurring fee.** The free version runs 100% on your own server
* **EAA- and ADA-aware**, with a statement generator built around what regulators actually ask for

= Why accessibility matters for a WooCommerce store =

Roughly 1 in 6 people live with some form of disability, and for an online shop that translates directly into lost sales: a customer who can't read low-contrast prices, can't tab through a checkout form with a keyboard, or can't tell a screen reader which button adds a product to the cart simply leaves. Beyond the business case, regulation has caught up with this reality:

* The **European Accessibility Act (EAA)** has applied across the EU since June 28, 2025 and generally covers e-commerce sold to EU consumers.
* The **Americans with Disabilities Act (ADA)** has repeatedly been applied by US courts to commercial websites, including WooCommerce stores, via lawsuits and demand letters.
* Both regulatory frameworks point back to the same technical yardstick: **WCAG 2.1 Level AA**, the W3C's Web Content Accessibility Guidelines.

Store Accessibility Kit for WooCommerce gives you a practical, honest starting point against that yardstick — what fails, why, and (for the most common WooCommerce issues) a one-click fix — without pretending a plugin alone can make you "compliant."

= How it works =

1. **Scan** — open any front-end page of your store (home, shop, a product page, cart, checkout) and run a scan, either from the Scan tab or the "Scan this page" admin-bar button. The scan runs axe-core in your own browser against that one page; nothing is crawled site-wide and nothing is uploaded anywhere.
2. **Review** — results are grouped by severity (Critical, Serious, Moderate, Minor). Each violation lists the WCAG rule, the exact element and CSS selector, a plain-English reason it failed, and how to fix it.
3. **Fix** — turn on the specific toggle(s) under Settings that address what you found (all start OFF), or hand the per-issue guidance to a developer/theme author for anything outside the six built-in fixes.
4. **Document** — generate the Accessibility Statement page once you've made real progress, so visitors and regulators can see what you support and how to reach you about gaps.

= Scanning =

* One-click accessibility scan of any front-end page of your store
* Powered by the industry-standard [axe-core](https://github.com/dequelabs/axe-core) engine (MPL-2.0 licensed, bundled locally — no CDN, no external calls)
* Detected issues grouped by severity: Critical, Serious, Moderate, Minor
* Every detected issue shows the rule name, the affected element (CSS selector), why it was flagged, and how to fix it
* Scan by URL, or jump straight from any front-end page via the "Scan this page" admin-bar button
* Scan history saved locally so you can track progress over time

= Optional fixes (each one individually toggleable, all OFF by default) =

* **Product image alt text** — fills in missing alt text on product images using the product title. (WCAG 1.1.1 Non-text Content: screen readers announce "image" with no alt text, so shoppers using one have no idea what the product photo shows.)
* **Checkout field labels** — adds a screen-reader label to checkout fields that only show a placeholder. (WCAG 1.3.1 / 4.1.2: a placeholder disappears the moment you type, and most screen readers don't treat it as a label at all.)
* **Visible focus states** — adds a high-contrast focus outline to links, buttons, and form controls. (WCAG 2.4.7 Focus Visible: keyboard-only users need to see exactly which control is focused to navigate your checkout without a mouse.)
* **Icon button labels** — adds accessible names to icon-only cart, search, and wishlist controls. (WCAG 4.1.2 Name, Role, Value: a magnifying-glass icon with no text means a screen reader just announces "button," with no clue what it does.)
* **Screen reader price label** — announces "Price:" before prices so screen readers read them correctly. (Prevents a price like "$49.00" being announced as a bare, out-of-context number.)
* **Empty link names** — gives accessible names to empty links, such as product image links. (WCAG 2.4.4 Link Purpose: a linked product thumbnail with no text is announced as "link," not as what it links to.)

Nothing is changed on your store until you explicitly switch a fix on. Every fix can be turned off again at any time, and turning a fix off immediately stops it from altering output — no residual markup is left behind.

= Accessibility statement =

* One-click generator creates a WordPress page titled "Accessibility Statement" pre-populated with an EAA-oriented template
* Template sections: an opening commitment statement, **conformance status** (the WCAG level you're aiming for), **known limitations**, **feedback and contact information** for visitors who hit a barrier, and **date of last review**
* You edit and publish the page like any other WordPress page — nothing is published automatically
* Re-run the generator any time you want to refresh the review date after making further fixes

= Built-in user guide =

A searchable **User Guide** page ships inside the plugin (**WooCommerce → Accessibility → User Guide**) with accordion-style documentation and an FAQ, kept in sync with the actual fix registry — so the descriptions you read always match what each toggle in Settings really does.

= Admin dashboard =

* Dashboard widget summarizing your last scan: date and detected-issue counts by severity
* Full admin page under **WooCommerce → Accessibility**, with Scan, Settings, and User Guide tabs
* Admin-bar notice when the last scan detected critical issues
* Optional (opt-in) weekly email reminder to run a scan
* Scan history is kept so you can see whether a page's issue count is trending down over time

= Roles and permissions =

Running a scan or changing settings requires the `manage_options` capability (Administrator by default), the same permission level WordPress uses for most site-wide settings. No new custom role is created.

= Source code =

[Source Code](https://github.com/TazeenEhsan/accessi-compliance-kit/tree/dev)

= Privacy: everything stays on your server =

The free version makes **zero external calls**. The scanning engine is bundled with the plugin, scans run in your own browser against your own site, and results are stored only in your own WordPress database. No telemetry, no phone-home, no accounts.

= What this plugin is not =

* It is **not an overlay widget**. It changes real markup (only when you opt in) instead of layering a toolbar over inaccessible pages.
* It does **not guarantee legal compliance**. Automated tools detect a subset of accessibility issues; results are presented as *detected issues* for you to review, and only a human audit can confirm conformance. This plugin is not legal advice.

= Credits =

The scan engine is [axe-core](https://github.com/dequelabs/axe-core) by Deque Systems, the same open-source engine behind many professional accessibility testing tools, bundled locally under its MPL-2.0 license. We're not affiliated with Deque Systems; axe-core is simply the best-tested open-source option for in-browser WCAG rule checking.

== Installation ==

= Requirements =

* WordPress 6.5+
* WooCommerce 8.0+, installed and active
* PHP 7.4+

= Steps =

1. Install via **Plugins → Add New** (search for "Store Accessibility Kit for WooCommerce"), or upload the plugin zip via **Plugins → Add New → Upload Plugin**.
2. Activate the plugin. WooCommerce 8.0+ must be installed and active.
3. Go to **WooCommerce → Accessibility**.
4. Run your first scan from the **Scan** tab (your home page is pre-filled — any page on your site works).
5. Review detected issues, then enable the fixes you want under the **Settings** tab (all fixes start OFF).
6. Optionally generate your Accessibility Statement page from the statement card and edit it to match your store.
7. Repeat the scan on your other key pages — shop, a product page, cart, and checkout — since each page can surface different issues.

== Frequently Asked Questions ==

= Does this plugin make my store legally compliant with the EAA or ADA? =

No automated tool can do that, and you should be wary of any that claims to. Store Accessibility Kit for WooCommerce detects a subset of WCAG 2.1 A/AA issues automatically, fixes several common ones when you opt in, and gives you a statement template — a strong, honest starting point. Full conformance also requires human review (e.g. of keyboard flows, content clarity, and color use in images). Nothing in this plugin is legal advice.

= Does the European Accessibility Act apply to my store? =

The EAA has been enforceable across the EU since June 28, 2025 and generally applies to online shops selling to EU consumers. Micro-enterprises (fewer than 10 employees **and** under €2M annual turnover) are exempt. If you are unsure, consult a legal professional — we cannot assess your obligations for you.

= Does any of my data leave my site? =

No. The scanner (axe-core) is bundled inside the plugin and runs in your own browser; results are stored in your own database. The free version makes no external requests of any kind.

= Is this an accessibility overlay? =

No. Overlay widgets layer a toolbar over your site without fixing the underlying markup, and regulators and disability advocates consider them inadequate. Store Accessibility Kit for WooCommerce reports real issues in your pages and — only when you enable a fix — corrects the actual markup.

= Do I need WooCommerce? =

Yes. The plugin is built specifically for WooCommerce stores and requires WooCommerce 8.0 or newer to be active.

= Why are all fixes off by default? =

Every theme is different, and we never change your store's front end without your explicit opt-in. Enable fixes one at a time and check your store as you go; each fix can be disabled again instantly.

= Will the fixes change how my store looks? =

Most fixes are invisible (alt text, screen-reader labels, accessible names). "Visible focus states" is the exception by design: it shows a high-contrast outline around the element currently focused via keyboard — this is exactly what WCAG asks for.

= A scan reports issues that come from my theme or another plugin. Can Store Accessibility Kit for WooCommerce fix those? =

The scanner reports everything it detects on the rendered page, whatever the source. The six included fixes target the most common WooCommerce storefront issues. Issues originating in a theme or another plugin may need to be fixed there — the scan's per-issue guidance tells you what to change.

= Does this work with any WooCommerce theme? =

Yes. The scanner runs against the rendered HTML of whatever theme you use, so it works with Storefront, Astra, or any other WooCommerce-compatible theme. The six optional fixes target markup patterns common across most storefront themes; if a fix doesn't apply cleanly to your theme, simply leave it off.

= What does an accessibility scan actually check? =

Each scan runs the bundled axe-core engine against the page you choose and checks it against WCAG 2.1 A/AA success criteria — things like color contrast, missing alt text, form labels, link names, and ARIA usage. Results are grouped by severity (Critical, Serious, Moderate, Minor) with the specific element, rule, and fix guidance for each one.

= Is this plugin free? =

Yes, the WordPress.org version is completely free, makes no external calls, and has no account or sign-up requirement.

= Does it scan my whole site at once? =

No, and deliberately so. Each scan checks one URL you choose — your home page, a specific product, the cart, checkout, or any other page. There's no background crawler. This keeps scans fast, keeps the results focused on the page you're actually working on, and avoids any surprise load on your server. Scan each key page (shop, a product, cart, checkout) separately for full coverage.

= What user role do I need to run a scan or change settings? =

You need the `manage_options` capability, which Administrators have by default. This is the same permission level required for most core WordPress settings screens.

= How long does a scan take? =

Typically a few seconds, since axe-core runs entirely in your browser against the already-loaded page — there's no server round-trip to an external scanning service to wait on.

= Can I see whether my store is improving over time? =

Yes. Scan history is saved locally, and the dashboard widget shows your last scan's severity counts, so you can compare runs and confirm your issue count is trending down as you apply fixes.

= Is there a premium or Pro version? =

No. Everything described here — scanning, all six fixes, the statement generator, and the dashboard — is included in the free WordPress.org version.

= Where can I get help or report a bug? =

Use the [WordPress.org support forum](https://wordpress.org/support/plugin/tazeen-store-accessibility-kit-for-woocommerce/) for this plugin, or open an issue on the [GitHub repository](https://github.com/TazeenEhsan/accessi-compliance-kit).

== Screenshots ==

1. Scan results grouped by severity, with per-issue detail (selector, why it fails, how to fix).
2. Settings tab — one toggle per fix, everything OFF by default.
3. Accessibility statement generator card with one-click page creation.
4. Dashboard widget with the last scan summary by severity.

== Changelog ==

= 1.0.5 =
* Simplified the display name to "Store Accessibility Kit for WooCommerce" (dropped "Tazeen" from the plugin header, readme, admin UI, and banners). The plugin slug/text domain (`tazeen-store-accessibility-kit-for-woocommerce`) is unchanged — it's required by WordPress.org and stays as-is; if you activated a prior version, no action is needed.
* Refreshed the WordPress.org banner and header artwork to match the current plugin name (was still showing the pre-rename "Accessi Compliance Kit" branding).
* Substantially expanded the readme: sharper short description, more relevant tags, a "why this matters" explainer tying the fixes to specific WCAG success criteria, a "how it works" walkthrough, requirements list, credits, and a dozen FAQ entries covering the most common questions.

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

= 1.0.5 =
Display name simplified to "Store Accessibility Kit for WooCommerce"; refreshed listing artwork and readme. No functional changes, no action needed.

= 1.0.4 =
Plugin display name updated to Tazeen Store Accessibility Kit for WooCommerce; no action needed.

= 1.0.3 =
Internal rename to Tazeen Store Accessibility Kit for WooCommerce; if you activated a prior version, deactivate and reactivate after updating.

= 1.0.1 =
Plugin renamed prior to first public release; no action needed.
