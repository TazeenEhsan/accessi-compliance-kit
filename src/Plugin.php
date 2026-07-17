<?php
/**
 * Main plugin bootstrap.
 *
 * @package AccessiComplianceKit
 */

namespace AccessiComplianceKit;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Singleton bootstrap. Wires services on `plugins_loaded`; contains no feature logic.
 */
class Plugin {

	/**
	 * Singleton instance.
	 *
	 * @var Plugin|null
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor. Hooks the boot sequence; performs no work itself.
	 */
	private function __construct() {
		add_action( 'plugins_loaded', array( $this, 'boot' ) );
	}

	/**
	 * Boot the plugin: verify dependencies, load i18n, register services.
	 *
	 * @return void
	 */
	public function boot() {
		if ( ! $this->is_woocommerce_active() ) {
			add_action( 'admin_notices', array( $this, 'render_woocommerce_missing_notice' ) );
			return;
		}

		load_plugin_textdomain( 'accessi-compliance-kit', false, dirname( plugin_basename( ACCESSI_COMPLIANCE_KIT_FILE ) ) . '/languages' );

		$this->register_admin();
		$this->register_scanner();
		$this->register_fixes();
		$this->register_statement();
	}

	/**
	 * Check whether WooCommerce is active.
	 *
	 * @return bool
	 */
	private function is_woocommerce_active() {
		return class_exists( 'WooCommerce' );
	}

	/**
	 * Render an admin notice when WooCommerce is missing.
	 *
	 * @return void
	 */
	public function render_woocommerce_missing_notice() {
		printf(
			'<div class="notice notice-error"><p>%s</p></div>',
			esc_html__( 'Accessi Compliance Kit requires WooCommerce 8.0+ to be installed and active.', 'accessi-compliance-kit' )
		);
	}

	/**
	 * Register admin services (menu, dashboard widget, settings, scan page).
	 *
	 * @return void
	 */
	private function register_admin() {
		( new Admin\AdminMenu() )->register();
		( new Admin\ScanPage() )->register();
		( new Admin\SettingsPage() )->register();
	}

	/**
	 * Register scanner services (AJAX handlers, front-end asset).
	 *
	 * @return void
	 */
	private function register_scanner() {
		( new Scanner\ScannerAssets() )->register();
		( new Scanner\ScanController( new Scanner\ScanStorage() ) )->register();
	}

	/**
	 * Register the fix manager.
	 *
	 * @return void
	 */
	private function register_fixes() {
		( new Fixes\FixManager() )->register();
	}

	/**
	 * Register statement generator services.
	 *
	 * @return void
	 */
	private function register_statement() {
		( new Statement\StatementGenerator() )->register();
	}
}
