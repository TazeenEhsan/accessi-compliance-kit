<?php
/**
 * Ensures WooCommerce checkout fields keep a screen-reader-visible label
 * (proposal §4.1, §5.4).
 *
 * @package AccessiComplianceKit
 */

namespace AccessiComplianceKit\Fixes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Fields configured with only a placeholder (no visible or hidden label)
 * get a screen-reader-text label built from the placeholder, rather than
 * having their label removed (docs/frontend.md §4.2).
 */
class CheckoutLabelsFix extends AbstractFix {

	/**
	 * {@inheritDoc}
	 */
	public function id() {
		return 'checkout_labels';
	}

	/**
	 * {@inheritDoc}
	 */
	public function label() {
		return __( 'Checkout field labels', 'accessi-compliance-kit' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function description() {
		// phpcs:ignore Generic.Files.LineLength.TooLong -- single translatable string, cannot be wrapped without breaking translation context.
		return __( 'Adds a screen-reader label to checkout fields that only show a placeholder.', 'accessi-compliance-kit' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function applies_to() {
		return array( 'checkout' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function register() {
		add_filter( 'woocommerce_form_field_args', array( $this, 'ensure_label' ), 10, 2 );
	}

	/**
	 * Give a placeholder-only field a screen-reader-text label.
	 *
	 * @param array  $args Field arguments.
	 * @param string $key  Field key.
	 * @return array
	 */
	public function ensure_label( $args, $key ) {
		if ( ! empty( $args['label'] ) || empty( $args['placeholder'] ) ) {
			return $args;
		}

		$args['label']       = $args['placeholder'];
		$existing_classes    = isset( $args['label_class'] ) && is_array( $args['label_class'] )
			? $args['label_class']
			: array();
		$args['label_class'] = array_unique( array_merge( $existing_classes, array( 'screen-reader-text' ) ) );

		return $args;
	}
}
