# Accessi Compliance Kit — Public FAQ

> Extended FAQ for the docs site / support-forum sticky. The short version ships in `readme.txt`, and a condensed version ships in-plugin on the Accessibility → User Guide page (`src/Admin/GuidePage.php`); keep all three in sync when answers change.

### Does this plugin make my store legally compliant with the EAA or ADA?

No automated tool can, and you should distrust any that claims to. Automated checkers (including the industry-standard axe-core engine this plugin uses) reliably detect roughly a third to a half of WCAG issues; the rest — keyboard flows, meaningful alt text, content clarity, sensible focus order — need human judgment. Accessi Compliance Kit gives you an honest starting point: detected issues to review, opt-in fixes for common WooCommerce problems, and a statement template. It is not legal advice.

### Does the European Accessibility Act apply to my store?

The EAA has been enforceable across all 27 EU member states since **June 28, 2025**. It generally applies to e-commerce sites selling to EU consumers and requires WCAG 2.1 Level AA. **Micro-enterprises are exempt**: fewer than 10 employees *and* annual turnover under €2M. Whether it applies to you specifically is a legal question — ask a professional.

### Why "detected issues" and not "violations"?

Automated results always include findings that need human interpretation (e.g. contrast on decorative elements, alternative text that exists but is unhelpful). We report what the engine detected and leave the judgment to you.

### Does any data leave my site? Do I need an account?

No and no. The scan engine is bundled in the plugin, scans run in your own browser against your own pages, and results are stored in your own WordPress database. The free version makes zero external requests — no telemetry, no CDN, no accounts, no API keys.

### Is this an accessibility overlay like UserWay or accessiBe?

No. Overlays add a floating toolbar and scripts that mask problems without fixing the page; disability advocates and European regulators consider them inadequate, and they have not protected sites from complaints. This plugin reports real issues in your markup and, only when you opt in, fixes the actual markup.

### Do I need WooCommerce? Which versions are supported?

Yes — the plugin targets WooCommerce stores specifically. Requirements: WordPress 6.5+, WooCommerce 8.0+, PHP 7.4+.

### Why are all six fixes off by default?

Because every theme is different and we never alter your store without explicit opt-in. Recommended flow: run a scan, enable one fix, spot-check your store, repeat. Each fix switches off just as instantly.

### Will the fixes slow down my store?

No meaningful impact. Five fixes are PHP filters that adjust markup as it's generated; two ship a small CSS/JS file loaded only while the fix is enabled. There are no external requests and no background processing on the front end.

### Will the fixes change my store's design?

Only "Visible focus states" has any visual effect, and only when an element is focused with the keyboard — a high-contrast outline, which is precisely what WCAG 2.1 requires. Everything else is invisible assistive markup.

### Can it fix issues caused by my theme or another plugin?

The scanner reports everything it detects on the rendered page, whatever the source, with guidance per issue. The included fixes target common WooCommerce storefront patterns; problems in a theme's own markup generally need fixing in the theme. Support for more theme-specific patterns is on the roadmap.

### The scan won't run — what should I check?

1. You're logged in as an administrator (scans are admin-only).
2. The URL is on this site (external URLs are rejected by design).
3. Nothing blocks same-origin iframes (`X-Frame-Options: DENY` or a strict `frame-ancestors` CSP will stop the scan).
4. The page doesn't redirect (e.g. a members-only page redirecting to login).

### Is the generated accessibility statement ready to publish as-is?

No — treat it as a strong draft. It's pre-filled with your site details and structured to cover what the EAA expects (compliance level, known limitations, contact route, review date), but the claims in it must reflect your store's real state. Review and edit before publishing.

### What happens to my data if I remove the plugin?

Deactivating keeps everything. Deleting the plugin from the Plugins screen removes the plugin's database table and options. The generated Accessibility Statement page is a normal WordPress page and is never deleted automatically.

### Is there a Pro version?

Not yet. The free version is complete on its own. A Pro tier (full-site crawling, scheduled scans, PDF audit reports, advanced WooCommerce fixes) is planned post-launch.

### How do I report a bug or a theme conflict?

Post in the WordPress.org support forum with: the fix or feature involved, your theme name + version, WooCommerce version, and what you observed vs. expected. For fix conflicts, please include the affected page's URL structure (product page, checkout, etc.).
