<?php
/**
 * Plugin Name:       AccessiWoo
 * Plugin URI:        https://accessiwoo.com
 * Description:       Scan WooCommerce stores for WCAG 2.1 AA violations, auto-fix common ones, and generate EAA compliance statements.
 * Version:           0.1.0
 * Requires at least: 6.5
 * Requires PHP:      7.4
 * Author:            AccessiWoo
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       accessiwoo
 * Domain Path:       /languages
 *
 * @package AccessiWoo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ACCESSIWOO_VERSION', '0.1.0' );
define( 'ACCESSIWOO_FILE', __FILE__ );
define( 'ACCESSIWOO_PATH', plugin_dir_path( __FILE__ ) );
define( 'ACCESSIWOO_URL', plugin_dir_url( __FILE__ ) );

require ACCESSIWOO_PATH . 'vendor/autoload.php';

register_activation_hook( ACCESSIWOO_FILE, array( \AccessiWoo\Activator::class, 'activate' ) );
register_deactivation_hook( ACCESSIWOO_FILE, array( \AccessiWoo\Deactivator::class, 'deactivate' ) );

\AccessiWoo\Plugin::instance();
