<?php
/**
 * In-plugin "User Guide" admin page: documentation, troubleshooting, and FAQ.
 *
 * @package AccessiComplianceKit
 */

namespace AccessiComplianceKit\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AccessiComplianceKit\Fixes\FixManager;
use AccessiComplianceKit\Utils\Capabilities;

/**
 * Registers the Accessibility → User Guide submenu and renders a fully
 * server-rendered documentation page (accordion sections + FAQ) so the plugin
 * ships its own docs without needing an external site. Content mirrors
 * `plugin-foundation-files/launch/docs-page.md` and `faq.md` — keep them in sync.
 *
 * Accordions use native `<details>/<summary>` elements: keyboard- and
 * screen-reader-accessible with zero JavaScript, which is fitting for an
 * accessibility plugin.
 */
class GuidePage {

	const MENU_SLUG = 'accessi-compliance-kit-guide';

	const HANDLE = 'accessi-compliance-kit-guide';

	const SUPPORT_URL = 'https://wordpress.org/support/plugin/accessi-compliance-kit/';

	/**
	 * Inline tags allowed in guide body text (everything else is stripped).
	 *
	 * @var array
	 */
	const INLINE_TAGS = array(
		'strong' => array(),
		'em'     => array(),
		'code'   => array(),
		'br'     => array(),
		'a'      => array(
			'href'   => true,
			'target' => true,
			'rel'    => true,
		),
	);

	/**
	 * Hook the submenu registration and the conditional style enqueue.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'admin_menu', array( $this, 'add_menu_page' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'maybe_enqueue' ) );
	}

	/**
	 * Register the submenu page under Accessibility → User Guide.
	 *
	 * @return void
	 */
	public function add_menu_page() {
		add_submenu_page(
			AdminMenu::MENU_SLUG,
			__( 'User Guide', 'accessi-compliance-kit' ),
			__( 'User Guide', 'accessi-compliance-kit' ),
			Capabilities::SCAN,
			self::MENU_SLUG,
			array( $this, 'render_page' )
		);
	}

	/**
	 * Enqueue the shared admin stylesheet on the guide page only.
	 *
	 * The hook prefix comes from the parent menu's (translated) title, so only
	 * the stable `_page_{slug}` suffix is compared.
	 *
	 * @param string $hook_suffix Current admin page hook suffix.
	 * @return void
	 */
	public function maybe_enqueue( $hook_suffix ) {
		$suffix = '_page_' . self::MENU_SLUG;

		if ( substr( $hook_suffix, -strlen( $suffix ) ) !== $suffix ) {
			return;
		}

		wp_enqueue_style(
			self::HANDLE,
			ACCESSI_COMPLIANCE_KIT_URL . 'assets/css/admin.css',
			array(),
			ACCESSI_COMPLIANCE_KIT_VERSION
		);
	}

	/**
	 * Render the full guide page.
	 *
	 * @return void
	 */
	public function render_page() {
		$sections = $this->sections();

		echo '<div class="wrap accessi-compliance-kit-guide">';
		$this->render_header();
		$this->render_nav( $sections );

		foreach ( $sections as $id => $section ) {
			$this->render_section( $id, $section );
		}

		$this->render_footer();
		echo '</div>';
	}

	/**
	 * Render the page header: logo, title, tagline, and action buttons.
	 *
	 * @return void
	 */
	private function render_header() {
		echo '<header class="accessi-compliance-kit-guide-header">';
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static hard-coded SVG markup.
		echo '<div class="accessi-compliance-kit-guide-logo">' . AdminMenu::menu_icon_svg( 28, '' ) . '</div>';
		echo '<div>';
		printf( '<h1>%s</h1>', esc_html__( 'User Guide', 'accessi-compliance-kit' ) );
		printf(
			'<p class="accessi-compliance-kit-guide-tagline">%s</p>',
			esc_html__( 'Everything you need to scan, fix, and document the accessibility of your WooCommerce store.', 'accessi-compliance-kit' )
		);
		echo '</div>';
		echo '<div class="accessi-compliance-kit-guide-actions">';
		printf(
			'<a class="button button-primary" href="%s">%s</a>',
			esc_url( admin_url( 'admin.php?page=' . AdminMenu::MENU_SLUG ) ),
			esc_html__( 'Open Dashboard', 'accessi-compliance-kit' )
		);
		printf(
			'<a class="button" href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
			esc_url( self::SUPPORT_URL ),
			esc_html__( 'Support Forum', 'accessi-compliance-kit' )
		);
		echo '</div>';
		echo '</header>';
	}

	/**
	 * Render the jump-link pill navigation.
	 *
	 * @param array $sections Sections keyed by HTML id.
	 * @return void
	 */
	private function render_nav( $sections ) {
		printf(
			'<nav class="accessi-compliance-kit-guide-nav" aria-label="%s">',
			esc_attr__( 'Guide sections', 'accessi-compliance-kit' )
		);

		foreach ( $sections as $id => $section ) {
			printf( '<a href="#%s">%s</a>', esc_attr( $id ), esc_html( $section['title'] ) );
		}

		echo '</nav>';
	}

	/**
	 * Render one section card with its accordion items.
	 *
	 * @param string $id      Section HTML id (jump-link target).
	 * @param array  $section Section definition: title, optional intro, items.
	 * @return void
	 */
	private function render_section( $id, $section ) {
		printf( '<section id="%s" class="accessi-compliance-kit-guide-section">', esc_attr( $id ) );
		printf( '<h2>%s</h2>', esc_html( $section['title'] ) );

		if ( ! empty( $section['intro'] ) ) {
			printf(
				'<p class="accessi-compliance-kit-guide-intro">%s</p>',
				wp_kses( $section['intro'], self::INLINE_TAGS )
			);
		}

		foreach ( $section['items'] as $item ) {
			$this->render_item( $item );
		}

		echo '</section>';
	}

	/**
	 * Render one accordion item.
	 *
	 * @param array $item Item definition: title, optional open flag, optional contexts, blocks.
	 * @return void
	 */
	private function render_item( $item ) {
		echo empty( $item['open'] )
			? '<details class="accessi-compliance-kit-guide-item">'
			: '<details class="accessi-compliance-kit-guide-item" open>';
		printf( '<summary>%s</summary>', esc_html( $item['title'] ) );
		echo '<div class="accessi-compliance-kit-guide-item-body">';
		$this->render_blocks( $item['blocks'] );

		if ( ! empty( $item['contexts'] ) ) {
			echo '<div class="accessi-compliance-kit-fix-contexts">';
			foreach ( $item['contexts'] as $context ) {
				printf( '<span class="accessi-compliance-kit-context-badge">%s</span>', esc_html( $context ) );
			}
			echo '</div>';
		}

		echo '</div></details>';
	}

	/**
	 * Render an item's content blocks (paragraphs and lists).
	 *
	 * @param array $blocks Blocks: [type => p, text => …] or [type => ul|ol, items => […]].
	 * @return void
	 */
	private function render_blocks( $blocks ) {
		foreach ( $blocks as $block ) {
			if ( 'p' === $block['type'] ) {
				printf( '<p>%s</p>', wp_kses( $block['text'], self::INLINE_TAGS ) );
				continue;
			}

			echo 'ol' === $block['type'] ? '<ol>' : '<ul>';
			foreach ( $block['items'] as $list_item ) {
				printf( '<li>%s</li>', wp_kses( $list_item, self::INLINE_TAGS ) );
			}
			echo 'ol' === $block['type'] ? '</ol>' : '</ul>';
		}
	}

	/**
	 * Render the closing "Still need help?" card.
	 *
	 * @return void
	 */
	private function render_footer() {
		echo '<section class="accessi-compliance-kit-guide-section accessi-compliance-kit-guide-help">';
		printf( '<h2>%s</h2>', esc_html__( 'Still need help?', 'accessi-compliance-kit' ) );
		printf(
			'<p>%s</p>',
			esc_html__( 'Post in the free support forum and we will take a look. Please include:', 'accessi-compliance-kit' )
		);
		echo '<ul>';
		printf( '<li>%s</li>', esc_html__( 'The fix or feature involved', 'accessi-compliance-kit' ) );
		printf( '<li>%s</li>', esc_html__( 'Your theme name and version', 'accessi-compliance-kit' ) );
		printf( '<li>%s</li>', esc_html__( 'Your WooCommerce version', 'accessi-compliance-kit' ) );
		printf( '<li>%s</li>', esc_html__( 'What you observed vs. what you expected', 'accessi-compliance-kit' ) );
		echo '</ul>';
		printf(
			'<a class="button button-primary" href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
			esc_url( self::SUPPORT_URL ),
			esc_html__( 'Open the Support Forum', 'accessi-compliance-kit' )
		);
		echo '</section>';
	}

	/**
	 * The full guide content, keyed by section HTML id.
	 *
	 * @return array
	 */
	private function sections() {
		return array(
			'getting-started' => array(
				'title' => __( 'Getting Started', 'accessi-compliance-kit' ),
				'items' => array(
					array(
						'title'  => __( 'What you need before you start', 'accessi-compliance-kit' ),
						'open'   => true,
						'blocks' => array(
							array(
								'type'  => 'ul',
								'items' => array(
									__( 'WordPress 6.5 or newer', 'accessi-compliance-kit' ),
									__( 'WooCommerce 8.0 or newer, installed and active — without it the plugin stays idle and shows a notice', 'accessi-compliance-kit' ),
									__( 'PHP 7.4 or newer', 'accessi-compliance-kit' ),
									__( 'An administrator account to run scans and change settings', 'accessi-compliance-kit' ),
								),
							),
						),
					),
					array(
						'title'  => __( 'Installation & activation', 'accessi-compliance-kit' ),
						'blocks' => array(
							array(
								'type'  => 'ol',
								'items' => array(
									__( 'Go to <strong>Plugins → Add New</strong>, search for “Accessi Compliance Kit”, click <strong>Install Now</strong>, then <strong>Activate</strong>. (Or upload the zip via <strong>Plugins → Add New → Upload Plugin</strong>.)', 'accessi-compliance-kit' ),
									__( 'On activation the plugin creates one database table for scan results and seeds its default settings. <strong>All fixes start OFF</strong> — activating changes nothing on your store’s front end.', 'accessi-compliance-kit' ),
									__( 'Find the plugin under <strong>WooCommerce → Accessibility</strong>.', 'accessi-compliance-kit' ),
								),
							),
						),
					),
					array(
						'title'  => __( 'Recommended first-run flow', 'accessi-compliance-kit' ),
						'blocks' => array(
							array(
								'type'  => 'ol',
								'items' => array(
									__( 'Run a scan of your home page from the <strong>Scan</strong> tab.', 'accessi-compliance-kit' ),
									__( 'Review the detected issues, starting with Critical and Serious.', 'accessi-compliance-kit' ),
									__( 'Enable one fix at a time in <strong>Settings</strong>, then spot-check your store.', 'accessi-compliance-kit' ),
									__( 'Generate the accessibility statement draft and edit it before publishing.', 'accessi-compliance-kit' ),
									__( 'Re-scan regularly — or turn on the weekly email reminder.', 'accessi-compliance-kit' ),
								),
							),
						),
					),
				),
			),
			'scanning'        => array(
				'title' => __( 'Running a Scan', 'accessi-compliance-kit' ),
				'items' => array(
					array(
						'title'  => __( 'Scan from the admin page', 'accessi-compliance-kit' ),
						'blocks' => array(
							array(
								'type'  => 'ol',
								'items' => array(
									__( 'Open <strong>WooCommerce → Accessibility</strong> and go to the <strong>Scan</strong> tab.', 'accessi-compliance-kit' ),
									__( 'The URL field is pre-filled with your home page; paste any URL <strong>on your own site</strong> (other domains are rejected by design).', 'accessi-compliance-kit' ),
									__( 'Click <strong>Scan this page</strong>. The page loads in a hidden frame and is checked in your browser by the bundled axe-core engine — nothing is sent anywhere.', 'accessi-compliance-kit' ),
									__( 'Results appear grouped by severity. Expand a row to see the affected element’s HTML, the CSS selector, why it was flagged, and a link to remediation guidance.', 'accessi-compliance-kit' ),
								),
							),
						),
					),
					array(
						'title'  => __( 'Scan from the front end', 'accessi-compliance-kit' ),
						'blocks' => array(
							array(
								'type' => 'p',
								'text' => __( 'While browsing your store logged in as an admin, click <strong>Scan this page</strong> in the admin bar — it opens the Scan tab with the current URL pre-filled.', 'accessi-compliance-kit' ),
							),
						),
					),
					array(
						'title'  => __( 'Scan history', 'accessi-compliance-kit' ),
						'blocks' => array(
							array(
								'type' => 'p',
								'text' => __( 'The <strong>History</strong> tab lists past scans (date, URL, status, per-severity counts). Click a row to reopen its full results. Results are stored in your own database and never leave your server.', 'accessi-compliance-kit' ),
							),
						),
					),
				),
			),
			'results'         => array(
				'title' => __( 'Understanding Results', 'accessi-compliance-kit' ),
				'items' => array(
					array(
						'title'  => __( 'The four severity levels', 'accessi-compliance-kit' ),
						'blocks' => array(
							array(
								'type'  => 'ul',
								'items' => array(
									__( '<strong>Critical</strong> — blocks some users completely (e.g. an image conveying information with no text alternative).', 'accessi-compliance-kit' ),
									__( '<strong>Serious</strong> — makes a core task very difficult (e.g. form fields without labels).', 'accessi-compliance-kit' ),
									__( '<strong>Moderate</strong> — causes real friction but usually has a workaround.', 'accessi-compliance-kit' ),
									__( '<strong>Minor</strong> — polish-level problems still worth cleaning up.', 'accessi-compliance-kit' ),
								),
							),
							array(
								'type' => 'p',
								'text' => __( 'Work top-down: clear Critical and Serious issues first.', 'accessi-compliance-kit' ),
							),
						),
					),
					array(
						'title'  => __( 'Why “detected issues” and not “violations”?', 'accessi-compliance-kit' ),
						'blocks' => array(
							array(
								'type' => 'p',
								'text' => __( 'Automated results always include findings that need human interpretation — for example contrast on decorative elements, or alternative text that exists but is unhelpful. We report what the engine detected and leave the judgment to you.', 'accessi-compliance-kit' ),
							),
						),
					),
					array(
						'title'  => __( 'What automated scans can and cannot find', 'accessi-compliance-kit' ),
						'blocks' => array(
							array(
								'type' => 'p',
								'text' => __( 'Automated checkers — including the industry-standard axe-core engine this plugin bundles — reliably detect roughly a third to a half of WCAG issues. The rest (keyboard flows, meaningful alt text, content clarity, sensible focus order) need human judgment. Treat a clean scan as a good baseline, not proof of compliance.', 'accessi-compliance-kit' ),
							),
						),
					),
				),
			),
			'fixes'           => array(
				'title' => __( 'The Six Fixes', 'accessi-compliance-kit' ),
				'intro' => __( 'Enable each fix under <strong>WooCommerce → Accessibility → Settings</strong>. Each is independent, off by default, and instant to switch off again. Recommended: enable one at a time and re-check your store.', 'accessi-compliance-kit' ),
				'items' => $this->fix_items(),
			),
			'statement'       => array(
				'title' => __( 'Accessibility Statement', 'accessi-compliance-kit' ),
				'items' => array(
					array(
						'title'  => __( 'Create the statement page', 'accessi-compliance-kit' ),
						'blocks' => array(
							array(
								'type'  => 'ol',
								'items' => array(
									__( 'Go to <strong>WooCommerce → Accessibility</strong> and find the <strong>Accessibility Statement</strong> card.', 'accessi-compliance-kit' ),
									__( 'Click <strong>Create statement page</strong>. A WordPress page titled “Accessibility Statement” is created from an EAA-oriented English template, pre-filled with your site name, contact details, and the current date.', 'accessi-compliance-kit' ),
									__( 'If a statement page already exists, the plugin warns you instead of creating a duplicate; creating a fresh page is an explicit second action.', 'accessi-compliance-kit' ),
								),
							),
						),
					),
					array(
						'title'  => __( 'Edit before you publish', 'accessi-compliance-kit' ),
						'blocks' => array(
							array(
								'type' => 'p',
								'text' => __( 'Treat the generated page as a strong draft, not a finished statement. It contains placeholders — notably the compliance level you claim and the “known limitations” section — that only you can fill in truthfully.', 'accessi-compliance-kit' ),
							),
						),
					),
					array(
						'title'  => __( 'Where to link it', 'accessi-compliance-kit' ),
						'blocks' => array(
							array(
								'type' => 'p',
								'text' => __( 'Link the published page from your site footer — most stores put it next to the privacy policy.', 'accessi-compliance-kit' ),
							),
						),
					),
				),
			),
			'notifications'   => array(
				'title' => __( 'Notifications', 'accessi-compliance-kit' ),
				'items' => array(
					array(
						'title'  => __( 'Dashboard widget & admin-bar notice', 'accessi-compliance-kit' ),
						'blocks' => array(
							array(
								'type' => 'p',
								'text' => __( 'The WordPress dashboard widget shows your last scan date and detected-issue counts by severity, with a link to the full results. An admin-bar notice appears when your most recent scan detected critical issues.', 'accessi-compliance-kit' ),
							),
						),
					),
					array(
						'title'  => __( 'Weekly email reminder (opt-in)', 'accessi-compliance-kit' ),
						'blocks' => array(
							array(
								'type' => 'p',
								'text' => __( 'Enable it in <strong>Settings</strong> to get a weekly email nudging you to re-scan. It uses WP-Cron and your site’s normal mail configuration; no external service is involved.', 'accessi-compliance-kit' ),
							),
						),
					),
				),
			),
			'troubleshooting' => array(
				'title' => __( 'Troubleshooting', 'accessi-compliance-kit' ),
				'items' => array(
					array(
						'title'  => __( 'The scan times out or never finishes', 'accessi-compliance-kit' ),
						'blocks' => array(
							array(
								'type' => 'p',
								'text' => __( 'The scanned page must load in an iframe from your own site. Security plugins or headers that block same-origin framing (<code>X-Frame-Options: DENY</code>, a strict <code>frame-ancestors</code> CSP) will prevent the scan — allow same-origin framing and retry. Scans also require you to be logged in as an admin; a scan fails if the URL redirects (e.g. to a login page).', 'accessi-compliance-kit' ),
							),
						),
					),
					array(
						'title'  => __( '“This URL is not part of this site.”', 'accessi-compliance-kit' ),
						'blocks' => array(
							array(
								'type' => 'p',
								'text' => __( 'By design the scanner only accepts URLs on your own domain. Check for a different subdomain, protocol, or a trailing typo.', 'accessi-compliance-kit' ),
							),
						),
					),
					array(
						'title'  => __( 'The menu item is missing', 'accessi-compliance-kit' ),
						'blocks' => array(
							array(
								'type' => 'p',
								'text' => __( 'The plugin registers nothing until WooCommerce 8.0+ is active — check the Plugins screen for WooCommerce. The menu lives under <strong>WooCommerce → Accessibility</strong> and requires an administrator account.', 'accessi-compliance-kit' ),
							),
						),
					),
					array(
						'title'  => __( 'A fix doesn’t seem to do anything', 'accessi-compliance-kit' ),
						'blocks' => array(
							array(
								'type' => 'p',
								'text' => __( 'Most fixes are invisible by design (screen-reader text, <code>alt</code>/<code>aria</code> attributes). Verify with your browser’s element inspector, or re-run a scan of the same page and compare detected-issue counts. Also check for full-page caching — purge the cache after toggling a fix.', 'accessi-compliance-kit' ),
							),
						),
					),
					array(
						'title'  => __( 'The scan reports issues from my theme or page builder', 'accessi-compliance-kit' ),
						'blocks' => array(
							array(
								'type' => 'p',
								'text' => __( 'The scanner reports everything on the rendered page regardless of origin. The per-issue guidance describes what needs to change; changes to theme markup need to happen in the theme (or a child theme), not in this plugin.', 'accessi-compliance-kit' ),
							),
						),
					),
				),
			),
			'privacy'         => array(
				'title' => __( 'Privacy & Data', 'accessi-compliance-kit' ),
				'items' => array(
					array(
						'title'  => __( 'What the plugin stores — and what leaves your site', 'accessi-compliance-kit' ),
						'blocks' => array(
							array(
								'type' => 'p',
								'text' => __( 'Zero external calls. The axe-core engine ships inside the plugin, scans run in your browser, and results live in your database. No telemetry, no CDN, no accounts, no API keys.', 'accessi-compliance-kit' ),
							),
							array(
								'type'  => 'ul',
								'items' => array(
									__( 'Scan results (one database table)', 'accessi-compliance-kit' ),
									__( 'The plugin’s settings', 'accessi-compliance-kit' ),
									__( 'The ID of the generated statement page', 'accessi-compliance-kit' ),
								),
							),
						),
					),
					array(
						'title'  => __( 'Deactivating vs. deleting', 'accessi-compliance-kit' ),
						'blocks' => array(
							array(
								'type' => 'p',
								'text' => __( 'Deactivating keeps your data. <strong>Deleting</strong> the plugin via the Plugins screen removes the scans table and all plugin options. The generated Accessibility Statement page is a normal WordPress page and is never deleted automatically.', 'accessi-compliance-kit' ),
							),
						),
					),
				),
			),
			'developers'      => array(
				'title' => __( 'For Developers', 'accessi-compliance-kit' ),
				'items' => array(
					array(
						'title'  => __( 'Prefixes, body classes & data', 'accessi-compliance-kit' ),
						'blocks' => array(
							array(
								'type'  => 'ul',
								'items' => array(
									__( 'All AJAX actions, options, hooks, CSS classes, and script handles are prefixed <code>accessi_compliance_kit_</code> / <code>.accessi-compliance-kit-</code>.', 'accessi-compliance-kit' ),
									__( 'When any fix is active, <code>&lt;body&gt;</code> gets the class <code>accessi-compliance-kit-fixes-active</code> plus one class per active fix — use these to scope your own CSS overrides.', 'accessi-compliance-kit' ),
									__( 'Scan results live in the <code>accessi_compliance_kit_scans</code> table (with your site’s table prefix); treat it as read-only.', 'accessi-compliance-kit' ),
									__( 'Source JS is included under <code>assets/js/src/</code>; builds use <code>@wordpress/scripts</code>.', 'accessi-compliance-kit' ),
								),
							),
						),
					),
				),
			),
			'faq'             => array(
				'title' => __( 'FAQ', 'accessi-compliance-kit' ),
				'items' => $this->faq_items(),
			),
		);
	}

	/**
	 * Build the fix accordions from the real fix registry (labels and
	 * descriptions stay in sync with the Settings screen automatically),
	 * followed by two static care-and-feeding items.
	 *
	 * @return array
	 */
	private function fix_items() {
		$items = array_map(
			function ( $fix ) {
				return array(
					'title'    => $fix->label(),
					'contexts' => $fix->applies_to(),
					'blocks'   => array(
						array(
							'type' => 'p',
							'text' => $fix->description(),
						),
					),
				);
			},
			FixManager::all_fixes()
		);

		$items[] = array(
			'title'  => __( 'Will the fixes change my design or slow my store?', 'accessi-compliance-kit' ),
			'blocks' => array(
				array(
					'type' => 'p',
					'text' => __( 'Only “Visible focus states” has any visual effect — a high-contrast outline shown when an element is focused with the keyboard, which is exactly what WCAG requires. Performance impact is negligible: most fixes are PHP filters, and the rest load one small CSS/JS file only while enabled. No external requests, ever.', 'accessi-compliance-kit' ),
				),
			),
		);

		$items[] = array(
			'title'  => __( 'If a fix conflicts with your theme', 'accessi-compliance-kit' ),
			'blocks' => array(
				array(
					'type' => 'p',
					'text' => sprintf(
						/* translators: %s: WordPress.org support forum URL. */
						__( 'Switch it off — the change is removed immediately — and report the theme + fix combination in the <a href="%s" target="_blank" rel="noopener noreferrer">support forum</a> so we can improve compatibility.', 'accessi-compliance-kit' ),
						esc_url( self::SUPPORT_URL )
					),
				),
			),
		);

		return $items;
	}

	/**
	 * The FAQ accordion items (condensed from launch/faq.md; readme.txt carries
	 * the short version — keep all three in sync when answers change).
	 *
	 * @return array
	 */
	private function faq_items() {
		return array(
			array(
				'title'  => __( 'Does this plugin make my store legally compliant with the EAA or ADA?', 'accessi-compliance-kit' ),
				'open'   => true,
				'blocks' => array(
					array(
						'type' => 'p',
						'text' => __( 'No automated tool can, and you should distrust any that claims to. This plugin gives you an honest starting point: detected issues to review, opt-in fixes for common WooCommerce problems, and a statement template. It is not legal advice.', 'accessi-compliance-kit' ),
					),
				),
			),
			array(
				'title'  => __( 'Does the European Accessibility Act apply to my store?', 'accessi-compliance-kit' ),
				'blocks' => array(
					array(
						'type' => 'p',
						'text' => __( 'The EAA has been enforceable across all 27 EU member states since June 28, 2025. It generally applies to e-commerce sites selling to EU consumers and requires WCAG 2.1 Level AA. Micro-enterprises are exempt: fewer than 10 employees <strong>and</strong> annual turnover under €2M. Whether it applies to you specifically is a legal question — ask a professional.', 'accessi-compliance-kit' ),
					),
				),
			),
			array(
				'title'  => __( 'Is this an accessibility overlay like UserWay or accessiBe?', 'accessi-compliance-kit' ),
				'blocks' => array(
					array(
						'type' => 'p',
						'text' => __( 'No. Overlays add a floating toolbar and scripts that mask problems without fixing the page; disability advocates and European regulators consider them inadequate. This plugin reports real issues in your markup and, only when you opt in, fixes the actual markup.', 'accessi-compliance-kit' ),
					),
				),
			),
			array(
				'title'  => __( 'Does any data leave my site? Do I need an account?', 'accessi-compliance-kit' ),
				'blocks' => array(
					array(
						'type' => 'p',
						'text' => __( 'No and no. The scan engine is bundled in the plugin, scans run in your own browser against your own pages, and results are stored in your own WordPress database. The free version makes zero external requests.', 'accessi-compliance-kit' ),
					),
				),
			),
			array(
				'title'  => __( 'Do I need WooCommerce? Which versions are supported?', 'accessi-compliance-kit' ),
				'blocks' => array(
					array(
						'type' => 'p',
						'text' => __( 'Yes — the plugin targets WooCommerce stores specifically. Requirements: WordPress 6.5+, WooCommerce 8.0+, PHP 7.4+.', 'accessi-compliance-kit' ),
					),
				),
			),
			array(
				'title'  => __( 'Why are all six fixes off by default?', 'accessi-compliance-kit' ),
				'blocks' => array(
					array(
						'type' => 'p',
						'text' => __( 'Because every theme is different and we never alter your store without explicit opt-in. Recommended flow: run a scan, enable one fix, spot-check your store, repeat. Each fix switches off just as instantly.', 'accessi-compliance-kit' ),
					),
				),
			),
			array(
				'title'  => __( 'What happens to my data if I remove the plugin?', 'accessi-compliance-kit' ),
				'blocks' => array(
					array(
						'type' => 'p',
						'text' => __( 'Deactivating keeps everything. Deleting the plugin from the Plugins screen removes the plugin’s database table and options. The generated Accessibility Statement page is a normal WordPress page and is never deleted automatically.', 'accessi-compliance-kit' ),
					),
				),
			),
			array(
				'title'  => __( 'Is there a Pro version?', 'accessi-compliance-kit' ),
				'blocks' => array(
					array(
						'type' => 'p',
						'text' => __( 'Not yet. The free version is complete on its own. A Pro tier (full-site crawling, scheduled scans, PDF audit reports, advanced WooCommerce fixes) is planned post-launch.', 'accessi-compliance-kit' ),
					),
				),
			),
		);
	}
}
