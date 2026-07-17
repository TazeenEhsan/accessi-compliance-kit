<?php
/**
 * Accessible names for empty link anchors, e.g. product image links
 * (proposal §4.1).
 *
 * @package AccessiComplianceKit
 */

namespace AccessiComplianceKit\Fixes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * For the WooCommerce product loop, injects a screen-reader-text span with
 * the product title into the thumbnail link. Generic empty anchors that a
 * server-side filter can't reach are handled by the shared `fixes` front-end
 * bundle (docs/frontend.md §4.6), the same one used by IconButtonAriaFix.
 */
class EmptyLinkAnchorFix extends AbstractFix {

	const HANDLE = 'accessi-compliance-kit-fixes';

	/**
	 * {@inheritDoc}
	 */
	public function id() {
		return 'empty_link_anchor';
	}

	/**
	 * {@inheritDoc}
	 */
	public function label() {
		return __( 'Empty link names', 'accessi-compliance-kit' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function description() {
		return __( 'Gives accessible names to empty links, such as product image links.', 'accessi-compliance-kit' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function applies_to() {
		return array( 'product', 'global' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function register() {
		add_action( 'woocommerce_before_shop_loop_item_title', array( $this, 'inject_accessible_name' ), 5 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
	}

	/**
	 * Print a screen-reader-text span carrying the product title inside the
	 * loop item's thumbnail link.
	 *
	 * @return void
	 */
	public function inject_accessible_name() {
		global $product;

		if ( ! $product instanceof \WC_Product ) {
			return;
		}

		printf(
			'<span class="screen-reader-text accessi-compliance-kit-empty-link-label">%s</span>',
			esc_html( $product->get_name() )
		);
	}

	/**
	 * Enqueue the shared fixes bundle for generic empty anchors elsewhere on
	 * the page that this filter can't reach.
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
			'accessiComplianceKitEmptyLinkAnchor',
			array(
				'enabled'      => true,
				'fallbackText' => __( 'Link', 'accessi-compliance-kit' ),
			)
		);
	}
}
