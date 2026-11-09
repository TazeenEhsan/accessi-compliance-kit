<?php
/**
 * Neutralises positive tabindex values (WCAG 2.4.3 Focus Order).
 *
 * @package AccessibilityComplianceKitForWooCommerce
 */

namespace AccessibilityComplianceKitForWooCommerce\Fixes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueues the shared `fixes` front-end bundle, which resets any
 * `tabindex` greater than zero to `0` so keyboard focus follows the DOM
 * order instead of an arbitrary author-defined order. The original value is
 * kept in a `data-` attribute. Nothing is hidden or removed.
 */
class PositiveTabindexFix extends AbstractFix {

	const HANDLE = 'tazeen-store-accessibility-kit-for-woocommerce-fixes';

	/**
	 * {@inheritDoc}
	 */
	public function id() {
		return 'positive_tabindex';
	}

	/**
	 * {@inheritDoc}
	 */
	public function label() {
		return __( 'Reset positive tabindex', 'tazeen-store-accessibility-kit-for-woocommerce' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function description() {
		// phpcs:ignore Generic.Files.LineLength.TooLong -- single translatable string, cannot be wrapped without breaking translation context.
		return __( 'Resets tabindex values above 0 so keyboard focus follows the natural page order (WCAG 2.4.3).', 'tazeen-store-accessibility-kit-for-woocommerce' );
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
	 * Enqueue the shared fixes bundle and flag this fix as enabled.
	 *
	 * @return void
	 */
	public function enqueue() {
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
			'accessibilityComplianceKitForWooCommercePositiveTabindex',
			array( 'enabled' => true )
		);
	}
}
