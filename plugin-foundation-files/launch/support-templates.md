# Support-Forum Template Responses

> Canned first-responses for the WP.org support forum (proposal §9: "template responses"). Personalize the first line; never paste more than one template per reply.

## T1 — Scan hangs / times out

Thanks for the report! The scanner loads your page in a hidden same-origin iframe, so the usual culprit is something blocking framing. Could you check:

1. Any security plugin or server header setting `X-Frame-Options: DENY` or a `frame-ancestors` CSP that excludes your own domain?
2. Does the URL redirect anywhere (login, geo/currency redirect)?
3. Are you logged in as an administrator in the same browser?

If you can share which security/caching plugins are active, that helps us reproduce it.

## T2 — Fix "does nothing"

Most of the fixes are intentionally invisible — they add screen-reader text and `alt`/`aria` attributes rather than visual changes. Two quick ways to confirm it's working:

1. Right-click the element → Inspect, and look for the added attribute/`<span class="screen-reader-text">`.
2. Re-run a scan of the same page and compare the detected-issue count before/after.

If you use a caching plugin, purge the page cache after toggling a fix — cached HTML predates the change.

## T3 — Fix conflicts with theme X

Sorry about that — please switch that fix off for now (the change is removed immediately). To help us ship a compatibility fix, could you share:

- Theme name + version (and whether it's a child theme)
- Which fix, and the page type where it breaks (product / cart / checkout)
- A screenshot or the affected markup if possible

We track theme-specific patterns and prioritize by report volume.

## T4 — "Is my store now compliant?"

The honest answer: no tool can tell you that automatically. The scanner detects a subset of WCAG 2.1 A/AA issues; a clean scan is necessary but not sufficient. For a compliance claim you'll want a human review of keyboard navigation, content, and the statement page's accuracy. We deliberately present results as "detected issues" — interpretation stays with you. (Nothing we say here is legal advice.)

## T5 — Does the EAA apply to me?

The EAA (enforceable since June 28, 2025) generally covers online shops selling to EU consumers, with an exemption for micro-enterprises (fewer than 10 employees **and** under €2M turnover). Whether it applies to your business is a legal question we can't answer for you — a lawyer or your trade association can.

## T6 — Feature request (Pro-scope: crawler, PDF, scheduling)

Great suggestion — full-site crawling / scheduled scans / PDF reports are on the roadmap for the planned Pro tier. I've noted your +1. The free version will always keep the single-page scanner, the six fixes, and the statement generator.

## T7 — Feature request (out of scope: overlay/widget, AI content, legal scoring)

Thanks for the idea! This one we've deliberately ruled out: we don't ship overlay widgets, AI-generated content, or automated legal-risk scores, because each of those creates more risk than it removes for merchants. Our approach stays: detect real issues, fix real markup (opt-in), and be honest about what automation can't do.

## T8 — 1-star review triage (reply in review thread)

Sorry the plugin let you down — that's not the experience we want. [Address the specific complaint in one sentence.] If you're open to it, I'd like to get this fixed: could you post the details in the support forum ([link]) — theme, WooCommerce version, and what happened? If we can't resolve it, that's on us, but most issues like this turn out to be fixable quickly.
