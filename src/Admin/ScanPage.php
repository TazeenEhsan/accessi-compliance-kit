<?php
/**
 * Enqueues the admin bundle and localizes data for the React scan UI.
 *
 * @package AccessiComplianceKit
 */

namespace AccessiComplianceKit\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AccessiComplianceKit\Scanner\ScannerAssets;
use AccessiComplianceKit\Utils\Options;

/**
 * Enqueues `build/admin.js` + `assets/css/admin.css` on the plugin's admin page
 * only, and localizes the data the React app needs (docs/admin.md §2).
 */
class ScanPage {

	const HANDLE = 'accessi-compliance-kit-admin';

	/**
	 * Hook the conditional enqueue.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'admin_enqueue_scripts', array( $this, 'maybe_enqueue' ) );
	}

	/**
	 * Enqueue the admin bundle only on the plugin's own admin page.
	 *
	 * @param string $hook_suffix Current admin page hook suffix.
	 * @return void
	 */
	public function maybe_enqueue( $hook_suffix ) {
		if ( 'woocommerce_page_' . AdminMenu::MENU_SLUG !== $hook_suffix ) {
			return;
		}

		$asset_file = ACCESSI_COMPLIANCE_KIT_PATH . 'build/admin.asset.php';

		if ( ! file_exists( $asset_file ) ) {
			return;
		}

		$asset = require $asset_file;

		wp_enqueue_script(
			self::HANDLE,
			ACCESSI_COMPLIANCE_KIT_URL . 'build/admin.js',
			$asset['dependencies'],
			$asset['version'],
			true
		);

		wp_enqueue_style(
			self::HANDLE,
			ACCESSI_COMPLIANCE_KIT_URL . 'assets/css/admin.css',
			array(),
			ACCESSI_COMPLIANCE_KIT_VERSION
		);

		wp_localize_script( self::HANDLE, 'accessiComplianceKitAdmin', $this->localized_data() );
	}

	/**
	 * Build the data localized to the admin React app (docs/admin.md §2).
	 *
	 * @return array
	 */
	private function localized_data() {
		return array(
			'ajaxUrl'        => admin_url( 'admin-ajax.php' ),
			'homeUrl'        => home_url( '/' ),
			'scannerToken'   => wp_create_nonce( 'accessi_compliance_kit_scan_handshake' ),
			'prefillUrl'     => $this->prefill_url(),
			'lastScanId'     => Options::get_last_scan_id(),
			'scanQueryVar'   => ScannerAssets::QUERY_VAR,
			'nonces'         => array(
				'runScan'  => wp_create_nonce( 'accessi_compliance_kit_run_scan' ),
				'getScan'  => wp_create_nonce( 'accessi_compliance_kit_get_scan' ),
				'getScans' => wp_create_nonce( 'accessi_compliance_kit_get_scans' ),
			),
			'severityLabels' => array(
				'critical' => __( 'Critical', 'accessi-compliance-kit' ),
				'serious'  => __( 'Serious', 'accessi-compliance-kit' ),
				'moderate' => __( 'Moderate', 'accessi-compliance-kit' ),
				'minor'    => __( 'Minor', 'accessi-compliance-kit' ),
			),
		);
	}

	/**
	 * Read a same-site URL to pre-fill the scan input (e.g. from the admin-bar
	 * "Scan this page" link, proposal §5.5 step 1). Off-site values are dropped.
	 *
	 * @return string
	 */
	private function prefill_url() {
		if ( ! isset( $_GET['scan_url'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only prefill value, not a state change.
			return '';
		}

		$url = esc_url_raw( wp_unslash( $_GET['scan_url'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only prefill value, not a state change.

		$site_host = wp_parse_url( home_url(), PHP_URL_HOST );
		$url_host  = wp_parse_url( $url, PHP_URL_HOST );

		if ( empty( $site_host ) || empty( $url_host ) || strtolower( $url_host ) !== strtolower( $site_host ) ) {
			return '';
		}

		return $url;
	}
}
