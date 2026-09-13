/**
 * The full User Guide content as React data. Mirrors
 * `plugin-foundation-files/launch/docs-page.md` and `faq.md` — keep them in
 * sync. Fix accordions are built from the localized fix registry so labels and
 * descriptions stay in sync with the Settings screen automatically.
 */
import { createInterpolateElement } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Interpolate the inline tags allowed in guide body text (strong/em/code,
 * plus any extra tokens the caller supplies, e.g. a link).
 *
 * @param {string} text  Translated string with inline tag tokens.
 * @param {Object} extra Additional token → element mappings.
 * @return {JSX.Element}
 */
const rich = ( text, extra = {} ) =>
	createInterpolateElement( text, {
		strong: <strong />,
		em: <em />,
		code: <code />,
		...extra,
	} );

/**
 * Build the fix accordion items from the localized fix registry, followed by
 * two static care-and-feeding items.
 *
 * @param {Array}  fixes      Fix metadata: { id, label, description, contexts }.
 * @param {string} supportUrl Support forum URL.
 * @return {Array}
 */
const fixItems = ( fixes, supportUrl ) => [
	...fixes.map( ( fix ) => ( {
		title: fix.label,
		contexts: fix.contexts,
		body: <p>{ fix.description }</p>,
	} ) ),
	{
		title: __( 'Will the fixes change my design or slow my store?', 'accessibility-compliance-kit-for-woocommerce' ),
		body: (
			<p>
				{ __( 'Only “Visible focus states” has any visual effect — a high-contrast outline shown when an element is focused with the keyboard, which is exactly what WCAG requires. Performance impact is negligible: most fixes are PHP filters, and the rest load one small CSS/JS file only while enabled. No external requests, ever.', 'accessibility-compliance-kit-for-woocommerce' ) }
			</p>
		),
	},
	{
		title: __( 'If a fix conflicts with your theme', 'accessibility-compliance-kit-for-woocommerce' ),
		body: (
			<p>
				{ rich(
					__( 'Switch it off — the change is removed immediately — and report the theme + fix combination in the <a>support forum</a> so we can improve compatibility.', 'accessibility-compliance-kit-for-woocommerce' ),
					{
						a: <a href={ supportUrl } target="_blank" rel="noopener noreferrer" />,
					}
				) }
			</p>
		),
	},
];

/**
 * The FAQ accordion items (condensed from launch/faq.md; readme.txt carries
 * the short version — keep all three in sync when answers change).
 *
 * @return {Array}
 */
const faqItems = () => [
	{
		title: __( 'Does this plugin make my store legally compliant with the EAA or ADA?', 'accessibility-compliance-kit-for-woocommerce' ),
		open: true,
		body: (
			<p>
				{ __( 'No automated tool can, and you should distrust any that claims to. This plugin gives you an honest starting point: detected issues to review, opt-in fixes for common WooCommerce problems, and a statement template. It is not legal advice.', 'accessibility-compliance-kit-for-woocommerce' ) }
			</p>
		),
	},
	{
		title: __( 'Does the European Accessibility Act apply to my store?', 'accessibility-compliance-kit-for-woocommerce' ),
		body: (
			<p>
				{ rich( __( 'The EAA has been enforceable across all 27 EU member states since June 28, 2025. It generally applies to e-commerce sites selling to EU consumers and requires WCAG 2.1 Level AA. Micro-enterprises are exempt: fewer than 10 employees <strong>and</strong> annual turnover under €2M. Whether it applies to you specifically is a legal question — ask a professional.', 'accessibility-compliance-kit-for-woocommerce' ) ) }
			</p>
		),
	},
	{
		title: __( 'Is this an accessibility overlay like UserWay or accessiBe?', 'accessibility-compliance-kit-for-woocommerce' ),
		body: (
			<p>
				{ __( 'No. Overlays add a floating toolbar and scripts that mask problems without fixing the page; disability advocates and European regulators consider them inadequate. This plugin reports real issues in your markup and, only when you opt in, fixes the actual markup.', 'accessibility-compliance-kit-for-woocommerce' ) }
			</p>
		),
	},
	{
		title: __( 'Does any data leave my site? Do I need an account?', 'accessibility-compliance-kit-for-woocommerce' ),
		body: (
			<p>
				{ __( 'No and no. The scan engine is bundled in the plugin, scans run in your own browser against your own pages, and results are stored in your own WordPress database. The free version makes zero external requests.', 'accessibility-compliance-kit-for-woocommerce' ) }
			</p>
		),
	},
	{
		title: __( 'Do I need WooCommerce? Which versions are supported?', 'accessibility-compliance-kit-for-woocommerce' ),
		body: (
			<p>
				{ __( 'Yes — the plugin targets WooCommerce stores specifically. Requirements: WordPress 6.5+, WooCommerce 8.0+, PHP 7.4+.', 'accessibility-compliance-kit-for-woocommerce' ) }
			</p>
		),
	},
	{
		title: __( 'Why are all six fixes off by default?', 'accessibility-compliance-kit-for-woocommerce' ),
		body: (
			<p>
				{ __( 'Because every theme is different and we never alter your store without explicit opt-in. Recommended flow: run a scan, enable one fix, spot-check your store, repeat. Each fix switches off just as instantly.', 'accessibility-compliance-kit-for-woocommerce' ) }
			</p>
		),
	},
	{
		title: __( 'What happens to my data if I remove the plugin?', 'accessibility-compliance-kit-for-woocommerce' ),
		body: (
			<p>
				{ __( 'Deactivating keeps everything. Deleting the plugin from the Plugins screen removes the plugin’s database table and options. The generated Accessibility Statement page is a normal WordPress page and is never deleted automatically.', 'accessibility-compliance-kit-for-woocommerce' ) }
			</p>
		),
	},
	{
		title: __( 'Is there a Pro version?', 'accessibility-compliance-kit-for-woocommerce' ),
		body: (
			<p>
				{ __( 'Not yet. The free version is complete on its own. A Pro tier (full-site crawling, scheduled scans, PDF audit reports, advanced WooCommerce fixes) is planned post-launch.', 'accessibility-compliance-kit-for-woocommerce' ) }
			</p>
		),
	},
];

/**
 * The full guide content: sections keyed by HTML id (jump-link target).
 *
 * @param {Object} settings Localized guide settings ({ fixes, supportUrl }).
 * @return {Array}
 */
export const getSections = ( { fixes = [], supportUrl = '' } ) => [
	{
		id: 'getting-started',
		title: __( 'Getting Started', 'accessibility-compliance-kit-for-woocommerce' ),
		items: [
			{
				title: __( 'What you need before you start', 'accessibility-compliance-kit-for-woocommerce' ),
				open: true,
				body: (
					<ul>
						<li>{ __( 'WordPress 6.5 or newer', 'accessibility-compliance-kit-for-woocommerce' ) }</li>
						<li>{ __( 'WooCommerce 8.0 or newer, installed and active — without it the plugin stays idle and shows a notice', 'accessibility-compliance-kit-for-woocommerce' ) }</li>
						<li>{ __( 'PHP 7.4 or newer', 'accessibility-compliance-kit-for-woocommerce' ) }</li>
						<li>{ __( 'An administrator account to run scans and change settings', 'accessibility-compliance-kit-for-woocommerce' ) }</li>
					</ul>
				),
			},
			{
				title: __( 'Installation & activation', 'accessibility-compliance-kit-for-woocommerce' ),
				body: (
					<ol>
						<li>{ rich( __( 'Go to <strong>Plugins → Add New</strong>, search for “Accessibility Compliance Kit for WooCommerce”, click <strong>Install Now</strong>, then <strong>Activate</strong>. (Or upload the zip via <strong>Plugins → Add New → Upload Plugin</strong>.)', 'accessibility-compliance-kit-for-woocommerce' ) ) }</li>
						<li>{ rich( __( 'On activation the plugin creates one database table for scan results and seeds its default settings. <strong>All fixes start OFF</strong> — activating changes nothing on your store’s front end.', 'accessibility-compliance-kit-for-woocommerce' ) ) }</li>
						<li>{ rich( __( 'Find the plugin under <strong>WooCommerce → Accessibility</strong>.', 'accessibility-compliance-kit-for-woocommerce' ) ) }</li>
					</ol>
				),
			},
			{
				title: __( 'Recommended first-run flow', 'accessibility-compliance-kit-for-woocommerce' ),
				body: (
					<ol>
						<li>{ rich( __( 'Run a scan of your home page from the <strong>Scan</strong> tab.', 'accessibility-compliance-kit-for-woocommerce' ) ) }</li>
						<li>{ __( 'Review the detected issues, starting with Critical and Serious.', 'accessibility-compliance-kit-for-woocommerce' ) }</li>
						<li>{ rich( __( 'Enable one fix at a time in <strong>Settings</strong>, then spot-check your store.', 'accessibility-compliance-kit-for-woocommerce' ) ) }</li>
						<li>{ __( 'Generate the accessibility statement draft and edit it before publishing.', 'accessibility-compliance-kit-for-woocommerce' ) }</li>
						<li>{ __( 'Re-scan regularly — or turn on the weekly email reminder.', 'accessibility-compliance-kit-for-woocommerce' ) }</li>
					</ol>
				),
			},
		],
	},
	{
		id: 'scanning',
		title: __( 'Running a Scan', 'accessibility-compliance-kit-for-woocommerce' ),
		items: [
			{
				title: __( 'Scan from the admin page', 'accessibility-compliance-kit-for-woocommerce' ),
				body: (
					<ol>
						<li>{ rich( __( 'Open <strong>WooCommerce → Accessibility</strong> and go to the <strong>Scan</strong> tab.', 'accessibility-compliance-kit-for-woocommerce' ) ) }</li>
						<li>{ rich( __( 'The URL field is pre-filled with your home page; paste any URL <strong>on your own site</strong> (other domains are rejected by design).', 'accessibility-compliance-kit-for-woocommerce' ) ) }</li>
						<li>{ rich( __( 'Click <strong>Scan this page</strong>. The page loads in a hidden frame and is checked in your browser by the bundled axe-core engine — nothing is sent anywhere.', 'accessibility-compliance-kit-for-woocommerce' ) ) }</li>
						<li>{ __( 'Results appear grouped by severity. Expand a row to see the affected element’s HTML, the CSS selector, why it was flagged, and a link to remediation guidance.', 'accessibility-compliance-kit-for-woocommerce' ) }</li>
					</ol>
				),
			},
			{
				title: __( 'Scan from the front end', 'accessibility-compliance-kit-for-woocommerce' ),
				body: (
					<p>{ rich( __( 'While browsing your store logged in as an admin, click <strong>Scan this page</strong> in the admin bar — it opens the Scan tab with the current URL pre-filled.', 'accessibility-compliance-kit-for-woocommerce' ) ) }</p>
				),
			},
			{
				title: __( 'Scan history', 'accessibility-compliance-kit-for-woocommerce' ),
				body: (
					<p>{ rich( __( 'The <strong>History</strong> tab lists past scans (date, URL, status, per-severity counts). Click a row to reopen its full results. Results are stored in your own database and never leave your server.', 'accessibility-compliance-kit-for-woocommerce' ) ) }</p>
				),
			},
		],
	},
	{
		id: 'results',
		title: __( 'Understanding Results', 'accessibility-compliance-kit-for-woocommerce' ),
		items: [
			{
				title: __( 'The four severity levels', 'accessibility-compliance-kit-for-woocommerce' ),
				body: (
					<>
						<ul>
							<li>{ rich( __( '<strong>Critical</strong> — blocks some users completely (e.g. an image conveying information with no text alternative).', 'accessibility-compliance-kit-for-woocommerce' ) ) }</li>
							<li>{ rich( __( '<strong>Serious</strong> — makes a core task very difficult (e.g. form fields without labels).', 'accessibility-compliance-kit-for-woocommerce' ) ) }</li>
							<li>{ rich( __( '<strong>Moderate</strong> — causes real friction but usually has a workaround.', 'accessibility-compliance-kit-for-woocommerce' ) ) }</li>
							<li>{ rich( __( '<strong>Minor</strong> — polish-level problems still worth cleaning up.', 'accessibility-compliance-kit-for-woocommerce' ) ) }</li>
						</ul>
						<p>{ __( 'Work top-down: clear Critical and Serious issues first.', 'accessibility-compliance-kit-for-woocommerce' ) }</p>
					</>
				),
			},
			{
				title: __( 'Why “detected issues” and not “violations”?', 'accessibility-compliance-kit-for-woocommerce' ),
				body: (
					<p>{ __( 'Automated results always include findings that need human interpretation — for example contrast on decorative elements, or alternative text that exists but is unhelpful. We report what the engine detected and leave the judgment to you.', 'accessibility-compliance-kit-for-woocommerce' ) }</p>
				),
			},
			{
				title: __( 'What automated scans can and cannot find', 'accessibility-compliance-kit-for-woocommerce' ),
				body: (
					<p>{ __( 'Automated checkers — including the industry-standard axe-core engine this plugin bundles — reliably detect roughly a third to a half of WCAG issues. The rest (keyboard flows, meaningful alt text, content clarity, sensible focus order) need human judgment. Treat a clean scan as a good baseline, not proof of compliance.', 'accessibility-compliance-kit-for-woocommerce' ) }</p>
				),
			},
		],
	},
	{
		id: 'fixes',
		title: __( 'The Six Fixes', 'accessibility-compliance-kit-for-woocommerce' ),
		intro: rich( __( 'Enable each fix under <strong>WooCommerce → Accessibility → Settings</strong>. Each is independent, off by default, and instant to switch off again. Recommended: enable one at a time and re-check your store.', 'accessibility-compliance-kit-for-woocommerce' ) ),
		items: fixItems( fixes, supportUrl ),
	},
	{
		id: 'statement',
		title: __( 'Accessibility Statement', 'accessibility-compliance-kit-for-woocommerce' ),
		items: [
			{
				title: __( 'Create the statement page', 'accessibility-compliance-kit-for-woocommerce' ),
				body: (
					<ol>
						<li>{ rich( __( 'Go to <strong>WooCommerce → Accessibility</strong> and find the <strong>Accessibility Statement</strong> card.', 'accessibility-compliance-kit-for-woocommerce' ) ) }</li>
						<li>{ rich( __( 'Click <strong>Create statement page</strong>. A WordPress page titled “Accessibility Statement” is created from an EAA-oriented English template, pre-filled with your site name, contact details, and the current date.', 'accessibility-compliance-kit-for-woocommerce' ) ) }</li>
						<li>{ __( 'If a statement page already exists, the plugin warns you instead of creating a duplicate; creating a fresh page is an explicit second action.', 'accessibility-compliance-kit-for-woocommerce' ) }</li>
					</ol>
				),
			},
			{
				title: __( 'Edit before you publish', 'accessibility-compliance-kit-for-woocommerce' ),
				body: (
					<p>{ __( 'Treat the generated page as a strong draft, not a finished statement. It contains placeholders — notably the compliance level you claim and the “known limitations” section — that only you can fill in truthfully.', 'accessibility-compliance-kit-for-woocommerce' ) }</p>
				),
			},
			{
				title: __( 'Where to link it', 'accessibility-compliance-kit-for-woocommerce' ),
				body: (
					<p>{ __( 'Link the published page from your site footer — most stores put it next to the privacy policy.', 'accessibility-compliance-kit-for-woocommerce' ) }</p>
				),
			},
		],
	},
	{
		id: 'notifications',
		title: __( 'Notifications', 'accessibility-compliance-kit-for-woocommerce' ),
		items: [
			{
				title: __( 'Dashboard widget & admin-bar notice', 'accessibility-compliance-kit-for-woocommerce' ),
				body: (
					<p>{ __( 'The WordPress dashboard widget shows your last scan date and detected-issue counts by severity, with a link to the full results. An admin-bar notice appears when your most recent scan detected critical issues.', 'accessibility-compliance-kit-for-woocommerce' ) }</p>
				),
			},
			{
				title: __( 'Weekly email reminder (opt-in)', 'accessibility-compliance-kit-for-woocommerce' ),
				body: (
					<p>{ rich( __( 'Enable it in <strong>Settings</strong> to get a weekly email nudging you to re-scan. It uses WP-Cron and your site’s normal mail configuration; no external service is involved.', 'accessibility-compliance-kit-for-woocommerce' ) ) }</p>
				),
			},
		],
	},
	{
		id: 'troubleshooting',
		title: __( 'Troubleshooting', 'accessibility-compliance-kit-for-woocommerce' ),
		items: [
			{
				title: __( 'The scan times out or never finishes', 'accessibility-compliance-kit-for-woocommerce' ),
				body: (
					<p>{ rich( __( 'The scanned page must load in an iframe from your own site. Security plugins or headers that block same-origin framing (<code>X-Frame-Options: DENY</code>, a strict <code>frame-ancestors</code> CSP) will prevent the scan — allow same-origin framing and retry. Scans also require you to be logged in as an admin; a scan fails if the URL redirects (e.g. to a login page).', 'accessibility-compliance-kit-for-woocommerce' ) ) }</p>
				),
			},
			{
				title: __( '“This URL is not part of this site.”', 'accessibility-compliance-kit-for-woocommerce' ),
				body: (
					<p>{ __( 'By design the scanner only accepts URLs on your own domain. Check for a different subdomain, protocol, or a trailing typo.', 'accessibility-compliance-kit-for-woocommerce' ) }</p>
				),
			},
			{
				title: __( 'The menu item is missing', 'accessibility-compliance-kit-for-woocommerce' ),
				body: (
					<p>{ rich( __( 'The plugin registers nothing until WooCommerce 8.0+ is active — check the Plugins screen for WooCommerce. The menu lives under <strong>WooCommerce → Accessibility</strong> and requires an administrator account.', 'accessibility-compliance-kit-for-woocommerce' ) ) }</p>
				),
			},
			{
				title: __( 'A fix doesn’t seem to do anything', 'accessibility-compliance-kit-for-woocommerce' ),
				body: (
					<p>{ rich( __( 'Most fixes are invisible by design (screen-reader text, <code>alt</code>/<code>aria</code> attributes). Verify with your browser’s element inspector, or re-run a scan of the same page and compare detected-issue counts. Also check for full-page caching — purge the cache after toggling a fix.', 'accessibility-compliance-kit-for-woocommerce' ) ) }</p>
				),
			},
			{
				title: __( 'The scan reports issues from my theme or page builder', 'accessibility-compliance-kit-for-woocommerce' ),
				body: (
					<p>{ __( 'The scanner reports everything on the rendered page regardless of origin. The per-issue guidance describes what needs to change; changes to theme markup need to happen in the theme (or a child theme), not in this plugin.', 'accessibility-compliance-kit-for-woocommerce' ) }</p>
				),
			},
		],
	},
	{
		id: 'privacy',
		title: __( 'Privacy & Data', 'accessibility-compliance-kit-for-woocommerce' ),
		items: [
			{
				title: __( 'What the plugin stores — and what leaves your site', 'accessibility-compliance-kit-for-woocommerce' ),
				body: (
					<>
						<p>{ __( 'Zero external calls. The axe-core engine ships inside the plugin, scans run in your browser, and results live in your database. No telemetry, no CDN, no accounts, no API keys.', 'accessibility-compliance-kit-for-woocommerce' ) }</p>
						<ul>
							<li>{ __( 'Scan results (one database table)', 'accessibility-compliance-kit-for-woocommerce' ) }</li>
							<li>{ __( 'The plugin’s settings', 'accessibility-compliance-kit-for-woocommerce' ) }</li>
							<li>{ __( 'The ID of the generated statement page', 'accessibility-compliance-kit-for-woocommerce' ) }</li>
						</ul>
					</>
				),
			},
			{
				title: __( 'Deactivating vs. deleting', 'accessibility-compliance-kit-for-woocommerce' ),
				body: (
					<p>{ rich( __( 'Deactivating keeps your data. <strong>Deleting</strong> the plugin via the Plugins screen removes the scans table and all plugin options. The generated Accessibility Statement page is a normal WordPress page and is never deleted automatically.', 'accessibility-compliance-kit-for-woocommerce' ) ) }</p>
				),
			},
		],
	},
	{
		id: 'developers',
		title: __( 'For Developers', 'accessibility-compliance-kit-for-woocommerce' ),
		items: [
			{
				title: __( 'Prefixes, body classes & data', 'accessibility-compliance-kit-for-woocommerce' ),
				body: (
					<ul>
						<li>{ rich( __( 'All AJAX actions, options, hooks, CSS classes, and script handles are prefixed <code>accessibility_compliance_kit_for_woocommerce_</code> / <code>.accessibility-compliance-kit-for-woocommerce-</code>.', 'accessibility-compliance-kit-for-woocommerce' ) ) }</li>
						<li>{ rich(
							__( 'When any fix is active, <bodyTag/> gets the class <code>accessibility-compliance-kit-for-woocommerce-fixes-active</code> plus one class per active fix — use these to scope your own CSS overrides.', 'accessibility-compliance-kit-for-woocommerce' ),
							{ bodyTag: <code>{ '<body>' }</code> }
						) }</li>
						<li>{ rich( __( 'Scan results live in the <code>accessibility_compliance_kit_for_woocommerce_scans</code> table (with your site’s table prefix); treat it as read-only.', 'accessibility-compliance-kit-for-woocommerce' ) ) }</li>
						<li>{ rich( __( 'Source JS is included under <code>assets/js/src/</code>; builds use <code>@wordpress/scripts</code>.', 'accessibility-compliance-kit-for-woocommerce' ) ) }</li>
					</ul>
				),
			},
		],
	},
	{
		id: 'faq',
		title: __( 'FAQ', 'accessibility-compliance-kit-for-woocommerce' ),
		items: faqItems(),
	},
];
