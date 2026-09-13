<?php
/**
 * Accessible names for icon-only buttons: cart, search, wishlist
 * (proposal §4.1).
 *
 * @package AccessibilityComplianceKitForWooCommerce
 */

namespace AccessibilityComplianceKitForWooCommerce\Fixes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Icon-only controls vary too much by theme for a server-side filter to
 * reach reliably, so this fix enqueues the small `fixes` front-end bundle
 * (docs/frontend.md §4.4) which adds an `aria-label` to recognized
 * icon-only cart/search/wishlist controls that have no accessible name.
 * This repairs real markup; it does not hide anything (proposal §4.3).
 */
class IconButtonAriaFix extends AbstractFix {

	const HANDLE = 'accessibility-compliance-kit-for-woocommerce-fixes';

	/**
	 * {@inheritDoc}
	 */
	public function id() {
		return 'icon_button_aria';
	}

	/**
	 * {@inheritDoc}
	 */
	public function label() {
		return __( 'Icon button labels', 'accessibility-compliance-kit-for-woocommerce' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function description() {
		// phpcs:ignore Generic.Files.LineLength.TooLong -- single translatable string, cannot be wrapped without breaking translation context.
		return __( 'Adds accessible names to icon-only cart, search, and wishlist controls.', 'accessibility-compliance-kit-for-woocommerce' );
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
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
	}

	/**
	 * Enqueue the shared fixes bundle and localize this fix's translated labels.
	 *
	 * @return void
	 */
	public function enqueue() {
		$asset_file = ACKFW_PATH . 'build/fixes.asset.php';

		if ( ! file_exists( $asset_file ) ) {
			return;
		}

		$asset = require $asset_file;

		wp_enqueue_script(
			self::HANDLE,
			ACKFW_URL . 'build/fixes.js',
			$asset['dependencies'],
			$asset['version'],
			true
		);

		wp_localize_script(
			self::HANDLE,
			'accessibilityComplianceKitForWooCommerceIconButtonAria',
			array(
				'enabled' => true,
				'labels'  => array(
					'cart'     => __( 'Cart', 'accessibility-compliance-kit-for-woocommerce' ),
					'search'   => __( 'Search', 'accessibility-compliance-kit-for-woocommerce' ),
					'wishlist' => __( 'Wishlist', 'accessibility-compliance-kit-for-woocommerce' ),
				),
			)
		);
	}
}
