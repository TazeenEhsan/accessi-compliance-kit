<?php
/**
 * Enqueues the admin bundle and localizes data for the React scan UI.
 *
 * @package AccessibilityComplianceKitForWooCommerce
 */

namespace AccessibilityComplianceKitForWooCommerce\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AccessibilityComplianceKitForWooCommerce\Fixes\FixManager;
use AccessibilityComplianceKitForWooCommerce\Scanner\ScannerAssets;
use AccessibilityComplianceKitForWooCommerce\Utils\Options;

/**
 * Enqueues `build/admin.js` + `assets/css/admin.css` on the plugin's admin page
 * only, and localizes the data the React app needs (docs/admin.md §2).
 */
class ScanPage {

	const HANDLE = 'accessibility-compliance-kit-for-woocommerce-admin';

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
		if ( 'toplevel_page_' . AdminMenu::MENU_SLUG !== $hook_suffix ) {
			return;
		}

		$asset_file = ACCESSIBILITY_COMPLIANCE_KIT_FOR_WOOCOMMERCE_PATH . 'build/admin.asset.php';

		if ( ! file_exists( $asset_file ) ) {
			return;
		}

		$asset = require $asset_file;

		wp_enqueue_script(
			self::HANDLE,
			ACCESSIBILITY_COMPLIANCE_KIT_FOR_WOOCOMMERCE_URL . 'build/admin.js',
			$asset['dependencies'],
			$asset['version'],
			true
		);

		wp_enqueue_style(
			self::HANDLE,
			ACCESSIBILITY_COMPLIANCE_KIT_FOR_WOOCOMMERCE_URL . 'assets/css/admin.css',
			array(),
			ACCESSIBILITY_COMPLIANCE_KIT_FOR_WOOCOMMERCE_VERSION
		);

		wp_localize_script( self::HANDLE, 'accessibilityComplianceKitForWooCommerceAdmin', $this->localized_data() );
	}

	/**
	 * Build the data localized to the admin React app (docs/admin.md §2).
	 *
	 * @return array
	 */
	private function localized_data() {
		return array(
			'ajaxUrl'        => admin_url( 'admin-ajax.php' ),
			'guideUrl'       => admin_url( 'admin.php?page=' . GuidePage::MENU_SLUG ),
			'homeUrl'        => home_url( '/' ),
			'scannerToken'   => wp_create_nonce( 'accessibility_compliance_kit_for_woocommerce_scan_handshake' ),
			'prefillUrl'     => $this->prefill_url(),
			'lastScanId'     => Options::get_last_scan_id(),
			'scanQueryVar'   => ScannerAssets::QUERY_VAR,
			'nonces'         => array(
				'runScan'           => wp_create_nonce( 'accessibility_compliance_kit_for_woocommerce_run_scan' ),
				'getScan'           => wp_create_nonce( 'accessibility_compliance_kit_for_woocommerce_get_scan' ),
				'getScans'          => wp_create_nonce( 'accessibility_compliance_kit_for_woocommerce_get_scans' ),
				'saveSettings'      => wp_create_nonce( 'accessibility_compliance_kit_for_woocommerce_save_settings' ),
				'generateStatement' => wp_create_nonce( 'accessibility_compliance_kit_for_woocommerce_generate_statement' ),
			),
			'severityLabels' => array(
				'critical' => __( 'Critical', 'accessibility-compliance-kit-for-woocommerce' ),
				'serious'  => __( 'Serious', 'accessibility-compliance-kit-for-woocommerce' ),
				'moderate' => __( 'Moderate', 'accessibility-compliance-kit-for-woocommerce' ),
				'minor'    => __( 'Minor', 'accessibility-compliance-kit-for-woocommerce' ),
			),
			'fixes'          => $this->fixes_data(),
			'activeFixes'    => Options::get_active_fixes(),
			'settings'       => Options::get_settings(),
			'statement'      => $this->statement_data(),
		);
	}

	/**
	 * Build the current statement page state (docs/admin.md §7) so the
	 * Dashboard tab can render its status card without an extra AJAX round-trip.
	 *
	 * @return array
	 */
	private function statement_data() {
		$settings = Options::get_settings();
		$page_id  = isset( $settings['statement_page_id'] ) ? absint( $settings['statement_page_id'] ) : 0;

		if ( ! $page_id || 'page' !== get_post_type( $page_id ) ) {
			return array(
				'pageId'   => 0,
				'editLink' => '',
			);
		}

		return array(
			'pageId'   => $page_id,
			'editLink' => get_edit_post_link( $page_id, 'raw' ),
		);
	}

	/**
	 * Build the fix metadata (id, label, description, contexts) the Settings
	 * tab needs to render one `ToggleControl` per fix (docs/admin.md §6).
	 *
	 * @return array
	 */
	private function fixes_data() {
		return array_map(
			function ( $fix ) {
				return array(
					'id'          => $fix->id(),
					'label'       => $fix->label(),
					'description' => $fix->description(),
					'contexts'    => $fix->applies_to(),
				);
			},
			FixManager::all_fixes()
		);
	}

	/**
	 * Read a same-site URL to pre-fill the scan input (e.g. from the admin-bar
	 * "Scan this page" link, proposal §5.5 step 1). Off-site values are dropped.
	 *
	 * @return string
	 */
	private function prefill_url() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only prefill value, not a state change.
		if ( ! isset( $_GET['scan_url'] ) ) {
			return '';
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only prefill value, not a state change.
		$url = esc_url_raw( wp_unslash( $_GET['scan_url'] ) );

		$site_host = wp_parse_url( home_url(), PHP_URL_HOST );
		$url_host  = wp_parse_url( $url, PHP_URL_HOST );

		if ( empty( $site_host ) || empty( $url_host ) || strtolower( $url_host ) !== strtolower( $site_host ) ) {
			return '';
		}

		return $url;
	}
}
