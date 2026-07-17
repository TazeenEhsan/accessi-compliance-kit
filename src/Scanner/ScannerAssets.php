<?php
/**
 * Conditional front-end enqueue of the axe-core scanner bundle.
 *
 * @package AccessiComplianceKit
 */

namespace AccessiComplianceKit\Scanner;

use AccessiComplianceKit\Utils\Capabilities;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Loads the scanner bundle on the front end only when the scan flag is present
 * and the current user is allowed to scan (proposal §5.5 step 3).
 */
class ScannerAssets {

	const HANDLE    = 'accessi-compliance-kit-scanner';
	const QUERY_VAR = 'accessi_compliance_kit_scan';

	/**
	 * Hook the conditional enqueue.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'wp_enqueue_scripts', array( $this, 'maybe_enqueue' ) );
	}

	/**
	 * Enqueue the scanner bundle when the scan flag is set and the user can scan.
	 *
	 * @return void
	 */
	public function maybe_enqueue() {
		if ( ! $this->is_scan_request() || ! Capabilities::can_scan() ) {
			return;
		}

		$asset_file = ACCESSI_COMPLIANCE_KIT_PATH . 'build/scanner.asset.php';

		if ( ! file_exists( $asset_file ) ) {
			return;
		}

		$asset = require $asset_file;

		wp_enqueue_script(
			self::HANDLE,
			ACCESSI_COMPLIANCE_KIT_URL . 'build/scanner.js',
			$asset['dependencies'],
			$asset['version'],
			true
		);

		wp_localize_script(
			self::HANDLE,
			'accessiComplianceKitScanner',
			array(
				'handshakeToken' => wp_create_nonce( 'accessi_compliance_kit_scan_handshake' ),
				'adminOrigin'    => home_url(),
			)
		);
	}

	/**
	 * Whether the current request is asking for a scan.
	 *
	 * @return bool
	 */
	private function is_scan_request() {
		if ( ! isset( $_GET[ self::QUERY_VAR ] ) ) {
			return false;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only feature flag, not a state change.
		return '1' === sanitize_text_field( wp_unslash( $_GET[ self::QUERY_VAR ] ) );
	}
}
