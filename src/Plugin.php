<?php
/**
 * Main plugin bootstrap.
 *
 * @package AccessibilityComplianceKitForWooCommerce
 */

namespace AccessibilityComplianceKitForWooCommerce;

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
	 * Boot the plugin: verify dependencies, register services. Translations load
	 * automatically for WordPress.org-hosted plugins since WP 4.6; no manual
	 * `load_plugin_textdomain()` call is needed (Plugin Check flags it as discouraged).
	 *
	 * @return void
	 */
	public function boot() {
		if ( ! $this->is_woocommerce_active() ) {
			add_action( 'admin_notices', array( $this, 'render_woocommerce_missing_notice' ) );
			return;
		}

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
			// phpcs:ignore Generic.Files.LineLength.TooLong -- single translatable string, cannot be wrapped without breaking translation context.
			esc_html__( 'Accessibility Compliance Kit for WooCommerce requires WooCommerce 8.0+ to be installed and active.', 'accessibility-compliance-kit-for-woocommerce' )
		);
	}

	/**
	 * Register admin services (menu, dashboard widget, settings, scan page).
	 *
	 * @return void
	 */
	private function register_admin() {
		( new Admin\AdminMenu() )->register();
		( new Admin\GuidePage() )->register();
		( new Admin\PluginLinks() )->register();
		( new Admin\ScanPage() )->register();
		( new Admin\SettingsPage() )->register();
		( new Admin\DashboardWidget( new Scanner\ScanStorage() ) )->register();
		( new Admin\EmailReminder() )->register();
	}

	/**
	 * Register scanner services (AJAX handlers, front-end asset).
	 *
	 * @return void
	 */
	private function register_scanner() {
		( new Scanner\ScannerAssets( new Scanner\ScanStorage() ) )->register();
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
