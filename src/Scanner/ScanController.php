<?php
/**
 * AJAX handlers for running and fetching scans.
 *
 * @package AccessibilityComplianceKitForWooCommerce
 */

namespace AccessibilityComplianceKitForWooCommerce\Scanner;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AccessibilityComplianceKitForWooCommerce\Utils\Capabilities;

/**
 * Registers and handles the `accessibility_compliance_kit_for_woocommerce_run_scan` and
 * `accessibility_compliance_kit_for_woocommerce_get_scan` AJAX actions (proposal §5.4, §5.5 steps 5–6).
 */
class ScanController {

	const DEFAULT_PER_PAGE = 20;
	const MAX_PER_PAGE     = 100;

	/**
	 * Scan storage service.
	 *
	 * @var ScanStorage
	 */
	private $scan_storage;

	/**
	 * Constructor.
	 *
	 * @param ScanStorage $scan_storage Scan storage service.
	 */
	public function __construct( ScanStorage $scan_storage ) {
		$this->scan_storage = $scan_storage;
	}

	/**
	 * Hook the AJAX handlers. No `wp_ajax_nopriv_*` handlers (docs/security.md §4).
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'wp_ajax_accessibility_compliance_kit_for_woocommerce_run_scan', array( $this, 'handle_run_scan' ) );
		add_action( 'wp_ajax_accessibility_compliance_kit_for_woocommerce_get_scan', array( $this, 'handle_get_scan' ) );
		add_action( 'wp_ajax_accessibility_compliance_kit_for_woocommerce_get_scans', array( $this, 'handle_get_scans' ) );
	}

	/**
	 * Save a completed client-side scan and return its summary.
	 *
	 * @return void
	 */
	public function handle_run_scan() {
		check_ajax_referer( 'accessibility_compliance_kit_for_woocommerce_run_scan', 'nonce' );

		if ( ! Capabilities::can_scan() ) {
			wp_send_json_error(
				array( 'message' => __( 'You are not allowed to run scans.', 'accessibility-compliance-kit-for-woocommerce' ) ),
				403
			);
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- nonce verified above via check_ajax_referer(); value is same-site validated below in sanitize_scan_url().
		$raw_url = isset( $_POST['url'] ) ? wp_unslash( $_POST['url'] ) : '';
		$url     = $this->sanitize_scan_url( $raw_url );

		if ( null === $url ) {
			wp_send_json_error(
				array( 'message' => __( 'The scan URL must be on this site.', 'accessibility-compliance-kit-for-woocommerce' ) ),
				400
			);
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- nonce verified above via check_ajax_referer(); value is JSON-decoded and field-sanitized below in ViolationParser::parse().
		$raw_violations_json = isset( $_POST['violations'] ) ? wp_unslash( $_POST['violations'] ) : '';
		$raw_violations      = $this->decode_violations( $raw_violations_json );

		if ( null === $raw_violations ) {
			wp_send_json_error(
				array( 'message' => __( 'The scan results were malformed.', 'accessibility-compliance-kit-for-woocommerce' ) ),
				400
			);
		}

		$scan_id = $this->scan_storage->create_scan( $url, 'single', get_current_user_id() );

		if ( ! $scan_id ) {
			wp_send_json_error( array( 'message' => __( 'Could not save the scan.', 'accessibility-compliance-kit-for-woocommerce' ) ), 500 );
		}

		$parsed = ViolationParser::parse( $raw_violations );

		$this->scan_storage->complete_scan( $scan_id, $parsed['violations'], $parsed['summary'] );

		wp_send_json_success(
			array(
				'id'      => $scan_id,
				'url'     => $url,
				'summary' => $parsed['summary'],
			)
		);
	}

	/**
	 * Fetch a single scan's stored results for the results UI.
	 *
	 * @return void
	 */
	public function handle_get_scan() {
		check_ajax_referer( 'accessibility_compliance_kit_for_woocommerce_get_scan', 'nonce' );

		if ( ! Capabilities::can_scan() ) {
			wp_send_json_error(
				array( 'message' => __( 'You are not allowed to view scans.', 'accessibility-compliance-kit-for-woocommerce' ) ),
				403
			);
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- verified above via check_ajax_referer.
		$scan_id = isset( $_POST['id'] ) ? absint( wp_unslash( $_POST['id'] ) ) : 0;

		if ( ! $scan_id ) {
			wp_send_json_error( array( 'message' => __( 'A scan ID is required.', 'accessibility-compliance-kit-for-woocommerce' ) ), 400 );
		}

		$scan = $this->scan_storage->get_scan( $scan_id );

		if ( null === $scan ) {
			wp_send_json_error( array( 'message' => __( 'Scan not found.', 'accessibility-compliance-kit-for-woocommerce' ) ), 404 );
		}

		wp_send_json_success( $scan );
	}

	/**
	 * Fetch a paginated list of past scans for the History tab.
	 *
	 * @return void
	 */
	public function handle_get_scans() {
		check_ajax_referer( 'accessibility_compliance_kit_for_woocommerce_get_scans', 'nonce' );

		if ( ! Capabilities::can_scan() ) {
			wp_send_json_error(
				array( 'message' => __( 'You are not allowed to view scans.', 'accessibility-compliance-kit-for-woocommerce' ) ),
				403
			);
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- verified above via check_ajax_referer.
		$page = isset( $_POST['page'] ) ? absint( wp_unslash( $_POST['page'] ) ) : 1;
		$page = max( 1, $page );
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- verified above via check_ajax_referer.
		$per_page = isset( $_POST['per_page'] ) ? absint( wp_unslash( $_POST['per_page'] ) ) : self::DEFAULT_PER_PAGE;
		$per_page = min( self::MAX_PER_PAGE, max( 1, $per_page ) );
		$offset   = ( $page - 1 ) * $per_page;

		// Fetch one extra row to determine whether a next page exists, without
		// requiring a separate count method on ScanStorage.
		$rows     = $this->scan_storage->get_recent_scans( $per_page + 1, $offset );
		$has_more = count( $rows ) > $per_page;

		if ( $has_more ) {
			array_pop( $rows );
		}

		wp_send_json_success(
			array(
				'scans'    => array_map( array( $this, 'summarize_scan' ), $rows ),
				'page'     => $page,
				'per_page' => $per_page,
				'has_more' => $has_more,
			)
		);
	}

	/**
	 * Reduce a hydrated scan row to the fields the History list needs.
	 *
	 * @param array $scan Hydrated scan row from ScanStorage.
	 * @return array
	 */
	private function summarize_scan( array $scan ) {
		return array(
			'id'           => $scan['id'],
			'url'          => $scan['url'],
			'status'       => $scan['status'],
			'started_at'   => get_date_from_gmt( $scan['started_at'], 'Y-m-d H:i' ),
			'completed_at' => $scan['completed_at'] ? get_date_from_gmt( $scan['completed_at'], 'Y-m-d H:i' ) : '',
			'summary'      => $scan['summary'],
		);
	}

	/**
	 * Validate a scan target: must resolve to an URL and stay on this site's host
	 * (docs/security.md §2 — the scanner must not probe arbitrary external URLs).
	 *
	 * @param string $raw_url Raw URL submitted by the admin app.
	 * @return string|null Sanitized same-site URL, or null when invalid/off-site.
	 */
	private function sanitize_scan_url( $raw_url ) {
		$url = esc_url_raw( $raw_url );

		if ( '' === $url ) {
			return null;
		}

		$site_host = wp_parse_url( home_url(), PHP_URL_HOST );
		$url_host  = wp_parse_url( $url, PHP_URL_HOST );

		if ( empty( $site_host ) || empty( $url_host ) || strtolower( $url_host ) !== strtolower( $site_host ) ) {
			return null;
		}

		return $url;
	}

	/**
	 * Decode the JSON violations payload posted by the scanner bundle.
	 *
	 * @param string $raw_json Raw JSON string, or empty when the page had no violations.
	 * @return array|null Decoded violations array, or null when the payload is malformed.
	 */
	private function decode_violations( $raw_json ) {
		if ( '' === $raw_json ) {
			return array();
		}

		$decoded = json_decode( $raw_json, true );

		if ( JSON_ERROR_NONE !== json_last_error() || ! is_array( $decoded ) ) {
			return null;
		}

		return $decoded;
	}
}
