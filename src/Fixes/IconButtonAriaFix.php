<?php
/**
 * Accessible names for icon-only buttons: cart, search, wishlist
 * (proposal §4.1).
 *
 * @package AccessiComplianceKit
 */

namespace AccessiComplianceKit\Fixes;

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

	const HANDLE = 'accessi-compliance-kit-fixes';

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
		return __( 'Icon button labels', 'accessi-compliance-kit' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function description() {
		return __( 'Adds accessible names to icon-only cart, search, and wishlist controls.', 'accessi-compliance-kit' );
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
		$asset_file = ACCESSI_COMPLIANCE_KIT_PATH . 'build/fixes.asset.php';

		if ( ! file_exists( $asset_file ) ) {
			return;
		}

		$asset = require $asset_file;

		wp_enqueue_script(
			self::HANDLE,
			ACCESSI_COMPLIANCE_KIT_URL . 'build/fixes.js',
			$asset['dependencies'],
			$asset['version'],
			true
		);

		wp_localize_script(
			self::HANDLE,
			'accessiComplianceKitIconButtonAria',
			array(
				'enabled' => true,
				'labels'  => array(
					'cart'     => __( 'Cart', 'accessi-compliance-kit' ),
					'search'   => __( 'Search', 'accessi-compliance-kit' ),
					'wishlist' => __( 'Wishlist', 'accessi-compliance-kit' ),
				),
			)
		);
	}
}
