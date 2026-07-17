<?php
/**
 * Conditional front-end enqueue of the axe-core scanner bundle.
 *
 * @package AccessiComplianceKit
 */

namespace AccessiComplianceKit\Scanner;

use AccessiComplianceKit\Admin\AdminMenu;
use AccessiComplianceKit\Utils\Capabilities;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Loads the scanner bundle on the front end only when the scan flag is present
 * and the current user is allowed to scan (proposal §5.5 step 3). Also owns the
 * front-end admin-bar surface: the "Scan this page" node and the critical-issues
 * notice (docs/admin.md §1, §9).
 */
class ScannerAssets {

	const HANDLE    = 'accessi-compliance-kit-scanner';
	const QUERY_VAR = 'accessi_compliance_kit_scan';

	/**
	 * Scan storage service, used to read the last scan's severity summary.
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
	 * Hook the conditional enqueue.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'wp_enqueue_scripts', array( $this, 'maybe_enqueue' ) );
		add_action( 'admin_bar_menu', array( $this, 'add_admin_bar_node' ), 100 );
		add_action( 'admin_bar_menu', array( $this, 'add_critical_notice_node' ), 100 );
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

	/**
	 * Add the front-end "Scan this page" admin-bar node for capable users,
	 * linking to the admin page with the current URL pre-filled (proposal §5.5 step 1).
	 *
	 * @param \WP_Admin_Bar $wp_admin_bar Core admin bar instance.
	 * @return void
	 */
	public function add_admin_bar_node( $wp_admin_bar ) {
		if ( is_admin() || ! Capabilities::can_scan() ) {
			return;
		}

		$admin_url = add_query_arg(
			array(
				'page'     => AdminMenu::MENU_SLUG,
				'scan_url' => rawurlencode( $this->current_url() ),
			),
			admin_url( 'admin.php' )
		);

		$wp_admin_bar->add_node(
			array(
				'id'    => 'accessi-compliance-kit-scan',
				'title' => esc_html__( 'Scan this page', 'accessi-compliance-kit' ),
				'href'  => esc_url( $admin_url ),
				'meta'  => array(
					'title' => esc_attr__( 'Scan this page for accessibility issues', 'accessi-compliance-kit' ),
				),
			)
		);
	}

	/**
	 * Add a highlighted admin-bar notice when the last scan found critical
	 * issues, linking to the plugin's results (proposal §4.1 "Notifications";
	 * docs/admin.md §9).
	 *
	 * @param \WP_Admin_Bar $wp_admin_bar Core admin bar instance.
	 * @return void
	 */
	public function add_critical_notice_node( $wp_admin_bar ) {
		if ( ! Capabilities::can_scan() ) {
			return;
		}

		$scan = $this->scan_storage->get_last_scan();

		if ( ! $scan || empty( $scan['summary']['critical'] ) ) {
			return;
		}

		$title = sprintf(
			/* translators: %d: number of critical issues detected in the last scan. */
			__( '%d critical accessibility issues detected', 'accessi-compliance-kit' ),
			(int) $scan['summary']['critical']
		);

		$wp_admin_bar->add_node(
			array(
				'id'    => 'accessi-compliance-kit-critical-notice',
				'title' => esc_html( $title ),
				'href'  => esc_url( add_query_arg( 'page', AdminMenu::MENU_SLUG, admin_url( 'admin.php' ) ) ),
				'meta'  => array(
					'class' => 'accessi-compliance-kit-admin-bar-critical',
					'title' => esc_attr__( 'View the detected accessibility issues', 'accessi-compliance-kit' ),
				),
			)
		);
	}

	/**
	 * Build the current front-end request's full URL.
	 *
	 * @return string
	 */
	private function current_url() {
		$host = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';
		$uri  = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';

		if ( '' === $host ) {
			return home_url( '/' );
		}

		$scheme = is_ssl() ? 'https://' : 'http://';

		return esc_url_raw( $scheme . $host . $uri );
	}
}
