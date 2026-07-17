<?php
/**
 * Registers the plugin's admin page under WooCommerce.
 *
 * @package AccessiComplianceKit
 */

namespace AccessiComplianceKit\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AccessiComplianceKit\Utils\Capabilities;

/**
 * Adds the "Accessibility" submenu page under WooCommerce (proposal §4.1).
 * The page callback renders only a React mount point.
 */
class AdminMenu {

	const MENU_SLUG = 'accessi-compliance-kit';

	/**
	 * Hook the admin menu registration.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'admin_menu', array( $this, 'add_menu_page' ) );
	}

	/**
	 * Register the submenu page under WooCommerce → Accessibility.
	 *
	 * @return void
	 */
	public function add_menu_page() {
		add_submenu_page(
			'woocommerce',
			__( 'Accessibility', 'accessi-compliance-kit' ),
			__( 'Accessibility', 'accessi-compliance-kit' ),
			Capabilities::SCAN,
			self::MENU_SLUG,
			array( $this, 'render_page' )
		);
	}

	/**
	 * Render the React mount point. All UI is rendered client-side.
	 *
	 * @return void
	 */
	public function render_page() {
		echo '<div id="accessi-compliance-kit-admin"></div>';
	}
}
