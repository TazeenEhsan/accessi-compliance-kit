# Launch Announcements — Drafts

> Per proposal §6 Phase 4 / §8. Post **after** WP.org approval; replace `[WPORG-LINK]` with the live directory URL. Reddit: read each sub's self-promo rules first, post as a genuine "I built this" story, and stay in the thread answering questions for the first few hours. Never post the same text twice — each draft below is written for its channel.

---

## 1. r/woocommerce

**Title:** I built a free, no-overlay accessibility scanner + fixer specifically for WooCommerce (EAA/WCAG) — feedback welcome

**Body:**

The EU Accessibility Act became enforceable last year, and while helping store owners I kept running into the same problem: the generic accessibility plugins are overlay widgets (which regulators explicitly consider inadequate), and the serious tools are $500+/year enterprise services. Nothing targeted WooCommerce's actual weak spots — checkout field labels, icon-only cart buttons, product image alt text, screen-reader price announcements.

So I built **Accessi Compliance Kit** (free, on WordPress.org): [WPORG-LINK]

What it does:

- Scans any page of your store with axe-core (the same engine behind Lighthouse's accessibility audit), grouped by severity, with per-issue "why it fails / how to fix"
- Six opt-in fixes for the most common WooCommerce issues — every single one OFF by default, because I'm not touching your theme without permission
- One-click accessibility statement page (the thing the EAA actually requires you to publish), as an editable draft
- Zero external calls. The engine is bundled; scan data never leaves your database. No account, no telemetry.

What it deliberately doesn't do: overlays, "compliance guaranteed" claims, or AI anything. Automated tools catch maybe half of WCAG issues — it says "detected issues", not "you're compliant", because the latter would be a lie.

It's v1.0.0 and I'd genuinely value beatings from real stores: weird themes, checkout customizations, whatever breaks it. I'll be in the comments.

---

## 2. r/wordpress

**Title:** Released my first plugin: a WooCommerce accessibility scanner with no overlay, no SaaS, no phone-home

**Body:**

After watching the accessibility-overlay backlash (and the EAA becoming enforceable in the EU), I wanted to prove the opposite approach works as a plugin: detect real issues, fix real markup, keep everything on the user's own server.

**Accessi Compliance Kit** — free on WordPress.org: [WPORG-LINK]

- axe-core bundled locally (no CDN), runs in your browser, results stored in your own DB — zero external requests in the entire plugin
- Severity-grouped scan results with selectors + remediation guidance, scan history
- Six individually-toggleable fixes for common WooCommerce issues (alt text, checkout labels, focus states, icon-button labels, screen-reader prices, empty links) — all off by default
- Generates the accessibility statement page the EAA expects, as a draft you edit

Tech, for the curious: PHP 7.4+, PSR-4, React admin via `@wordpress/element`/`@wordpress/components` (no second React bundled), `@wordpress/scripts` build, one custom table, everything nonce+capability checked. Source JS ships in the plugin.

Happy to answer anything about the build or the WP.org review process.

---

## 3. WP Tavern — pitch email (they take submissions)

**Subject:** Story pitch: a WooCommerce-specific answer to the accessibility-overlay problem

Hi [editor name],

The overlay-widget backlash is well covered, but a year into EAA enforcement small WooCommerce merchants still have basically two options: an overlay, or a $500+/year enterprise service. I've just released a third option on WordPress.org and I think there's a story in the approach rather than the plugin itself:

- **No overlay, no SaaS:** the axe-core engine is bundled into the plugin, scans run in the merchant's own browser, data never leaves their server — a deliberate counter-design to both overlays and cloud scanners.
- **WooCommerce-specific fixes** (checkout labels, icon-only cart buttons, screen-reader price announcements) that ship OFF by default — every fix is opt-in, per the "don't break my theme" lesson from overlay complaints.
- **Honest framing:** results are "detected issues", never "you are compliant" — automated tooling can't determine conformance, and the plugin says so in its own FAQ.

Plugin: Accessi Compliance Kit — [WPORG-LINK]. I'm a solo developer; happy to talk about the EAA angle, why overlays persist, or the WP.org review experience.

Thanks for considering it,
[Name] — [site] — [email]

---

## 4. Twitter/X — thread

**1/** WooCommerce stores selling into the EU have been subject to the European Accessibility Act since June 2025. Most "solutions" are overlay widgets — which EU regulators consider inadequate.

I built the boring, honest alternative. Free, on WordPress.org 🧵

**2/** Accessi Compliance Kit:
▸ Scans any page with axe-core (bundled, no CDN)
▸ Issues grouped Critical → Minor, each with selector + how to fix
▸ 6 opt-in fixes for WooCommerce's usual suspects (checkout labels, alt text, icon buttons…)
▸ 1-click accessibility statement page

**3/** Design principles:
▸ Zero external calls — scan data never leaves your server
▸ Every fix OFF by default; your theme is yours
▸ "Detected issues", never "compliance guaranteed" — automated tools can't promise that, and anyone who says otherwise is selling you risk

**4/** It's free, GPL, v1.0.0, and I want it stress-tested by real stores. Grab it: [WPORG-LINK]

Bug reports and grumpy feedback welcome. RTs appreciated 🙏

---

## 5. LinkedIn

**One year into EAA enforcement, most small WooCommerce stores still aren't ready — and the tools aren't helping.**

The European Accessibility Act has applied to online shops selling to EU consumers since June 2025 (micro-enterprises exempt). What I kept seeing in practice: merchants installing overlay widgets that regulators and disability advocates consider inadequate, or being quoted enterprise prices for monitoring services.

So I built and just released a free alternative on WordPress.org: **Accessi Compliance Kit**.

▸ Scans store pages against WCAG 2.1 A/AA using axe-core — bundled locally, so scan data never leaves the merchant's server
▸ Fixes six of the most common WooCommerce accessibility issues (checkout field labels, missing alt text, icon-only buttons, screen-reader price announcements…) — each fix opt-in, off by default
▸ Generates the accessibility statement page the EAA expects, as an editable draft
▸ No overlay, no account, no telemetry — and no false "compliance guaranteed" claims, because automated tools detect only a subset of WCAG issues

If you run a WooCommerce store, manage client stores, or work in digital accessibility, I'd genuinely value your feedback — especially where it falls short: [WPORG-LINK]

#accessibility #WooCommerce #WordPress #EAA #WCAG #a11y #ecommerce

---

## Posting checklist

- [ ] WP.org approval received; `[WPORG-LINK]` replaced everywhere
- [ ] Screenshots/GIF ready to attach (Reddit posts with a results-screen GIF perform far better)
- [ ] r/woocommerce + r/wordpress self-promo rules re-read; account has recent non-promo activity
- [ ] WP Tavern pitch personalized to a named editor
- [ ] Calendar block reserved for first-day comment replies
- [ ] Stagger: Reddit day 1, LinkedIn day 2, X thread day 2, Tavern pitch day 1 (their lead time is days-weeks)
