<?php
/**
 * Ensures WooCommerce prices are announced correctly by screen readers
 * (proposal §4.1).
 *
 * @package AccessibilityComplianceKitForWooCommerce
 */

namespace AccessibilityComplianceKitForWooCommerce\Fixes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Prepends a `screen-reader-text` "Price:" label to WooCommerce price HTML.
 * Guards against double-prefixing on repeated filter application
 * (docs/frontend.md §4.5).
 */
class PriceScreenReaderFix extends AbstractFix {

	const MARKER_CLASS = 'accessibility-compliance-kit-for-woocommerce-price-label';

	/**
	 * {@inheritDoc}
	 */
	public function id() {
		return 'price_screen_reader';
	}

	/**
	 * {@inheritDoc}
	 */
	public function label() {
		return __( 'Screen reader price label', 'accessibility-compliance-kit-for-woocommerce' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function description() {
		// phpcs:ignore Generic.Files.LineLength.TooLong -- single translatable string, cannot be wrapped without breaking translation context.
		return __( 'Announces "Price:" before prices so screen readers read them correctly.', 'accessibility-compliance-kit-for-woocommerce' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function applies_to() {
		return array( 'product', 'cart' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function register() {
		add_filter( 'woocommerce_get_price_html', array( $this, 'prefix_price' ), 20 );
	}

	/**
	 * Prepend the screen-reader-only "Price:" label.
	 *
	 * @param string $price_html Price HTML produced by WooCommerce.
	 * @return string
	 */
	public function prefix_price( $price_html ) {
		if ( '' === $price_html || false !== strpos( $price_html, self::MARKER_CLASS ) ) {
			return $price_html;
		}

		$label = sprintf(
			'<span class="screen-reader-text %1$s">%2$s</span>',
			esc_attr( self::MARKER_CLASS ),
			esc_html__( 'Price:', 'accessibility-compliance-kit-for-woocommerce' )
		);

		return $label . $price_html;
	}
}
