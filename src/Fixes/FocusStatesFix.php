<?php
/**
 * Visible focus states on all buttons and links (proposal §4.1).
 *
 * @package AccessiComplianceKit
 */

namespace AccessiComplianceKit\Fixes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueues `frontend-fixes.css`, which adds a high-contrast `:focus-visible`
 * outline scoped to this fix's `body_class` flag. Never removes theme focus
 * styles, only guarantees visibility (docs/frontend.md §4.3).
 */
class FocusStatesFix extends AbstractFix {

	const HANDLE = 'accessi-compliance-kit-frontend-fixes';

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
		return __( 'Visible focus states', 'accessi-compliance-kit' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function description() {
		return __( 'Adds a high-contrast focus outline to links, buttons, and form controls.', 'accessi-compliance-kit' );
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
			ACCESSI_COMPLIANCE_KIT_URL . 'assets/css/frontend-fixes.css',
			array(),
			ACCESSI_COMPLIANCE_KIT_VERSION
		);
	}
}
