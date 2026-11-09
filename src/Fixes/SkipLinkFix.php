<?php
/**
 * "Skip to main content" link (WCAG 2.4.1 Bypass Blocks).
 *
 * @package AccessibilityComplianceKitForWooCommerce
 */

namespace AccessibilityComplianceKitForWooCommerce\Fixes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Prints a skip link at the start of `<body>` (`wp_body_open`). The link is
 * rendered `hidden`; the shared `fixes` front-end bundle reveals it only when
 * the page has a main landmark to target and the theme does not already ship
 * its own skip link, so it can never duplicate or point at nothing. The link is
 * visually hidden until it receives keyboard focus.
 */
class SkipLinkFix extends AbstractFix {

	const HANDLE = 'tazeen-store-accessibility-kit-for-woocommerce-fixes';

	const LINK_ID = 'tazeen-store-accessibility-kit-for-woocommerce-skip-link';

	const STYLE_HANDLE = 'tazeen-store-accessibility-kit-for-woocommerce-skip-link';

	/**
	 * {@inheritDoc}
	 */
	public function id() {
		return 'skip_link';
	}

	/**
	 * {@inheritDoc}
	 */
	public function label() {
		return __( 'Skip to main content link', 'tazeen-store-accessibility-kit-for-woocommerce' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function description() {
		// phpcs:ignore Generic.Files.LineLength.TooLong -- single translatable string, cannot be wrapped without breaking translation context.
		return __( 'Adds a keyboard-visible "Skip to main content" link, unless your theme already has one (WCAG 2.4.1).', 'tazeen-store-accessibility-kit-for-woocommerce' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function applies_to() {
		return array( 'global' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function register() {
		add_action( 'wp_body_open', array( $this, 'render_link' ), 1 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
	}

	/**
	 * Print the (initially hidden) skip link.
	 *
	 * @return void
	 */
	public function render_link() {
		printf(
			'<a id="%1$s" class="%1$s" href="#" hidden>%2$s</a>',
			esc_attr( self::LINK_ID ),
			esc_html__( 'Skip to main content', 'tazeen-store-accessibility-kit-for-woocommerce' )
		);
	}

	/**
	 * Enqueue the link's focus-reveal CSS and the shared fixes bundle.
	 *
	 * @return void
	 */
	public function enqueue() {
		// Handle with no source: carries only the inline rules below.
		wp_register_style( self::STYLE_HANDLE, false, array(), TSAKW_VERSION );
		wp_enqueue_style( self::STYLE_HANDLE );
		wp_add_inline_style( self::STYLE_HANDLE, $this->inline_css() );

		$asset_file = TSAKW_PATH . 'build/fixes.asset.php';

		if ( ! file_exists( $asset_file ) ) {
			return;
		}

		$asset = require $asset_file;

		wp_enqueue_script(
			self::HANDLE,
			TSAKW_URL . 'build/fixes.js',
			$asset['dependencies'],
			$asset['version'],
			true
		);

		wp_localize_script(
			self::HANDLE,
			'accessibilityComplianceKitForWooCommerceSkipLink',
			array(
				'enabled' => true,
				'linkId'  => self::LINK_ID,
			)
		);
	}

	/**
	 * CSS that keeps the link off-screen until it is focused.
	 *
	 * @return string
	 */
	private function inline_css() {
		$selector = '#' . self::LINK_ID;

		return $selector . '{position:absolute;left:-9999px;top:0;z-index:100000;padding:12px 16px;'
			. 'background:#fff;color:#1d2327;font-size:16px;line-height:1.4;text-decoration:underline;}'
			. $selector . ':focus{left:8px;top:8px;outline:3px solid #2271b1;outline-offset:2px;}';
	}
}
