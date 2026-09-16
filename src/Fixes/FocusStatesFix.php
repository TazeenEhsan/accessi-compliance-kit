<?php
/**
 * Visible focus states on all buttons and links (proposal §4.1).
 *
 * @package AccessibilityComplianceKitForWooCommerce
 */

namespace AccessibilityComplianceKitForWooCommerce\Fixes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueues `frontend-fixes.css`, which adds a high-contrast `:focus-visible`
 * outline scoped to this fix's `body_class` flag. Never removes theme focus
 * styles, only guarantees visibility (docs/frontend.md §4.3).
 */
class FocusStatesFix extends AbstractFix {

	const HANDLE = 'tazeen-store-accessibility-kit-for-woocommerce-frontend-fixes';

	/**
	 * {@inheritDoc}
	 */
	public function id() {
		return 'focus_states';
	}

	/**
	 * {@inheritDoc}
	 */
	public function label() {
		return __( 'Visible focus states', 'tazeen-store-accessibility-kit-for-woocommerce' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function description() {
		// phpcs:ignore Generic.Files.LineLength.TooLong -- single translatable string, cannot be wrapped without breaking translation context.
		return __( 'Adds a high-contrast focus outline to links, buttons, and form controls.', 'tazeen-store-accessibility-kit-for-woocommerce' );
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
	 * Enqueue the frontend fixes stylesheet.
	 *
	 * @return void
	 */
	public function enqueue() {
		wp_enqueue_style(
			self::HANDLE,
			TSAKW_URL . 'assets/css/frontend-fixes.css',
			array(),
			TSAKW_VERSION
		);
	}
}
