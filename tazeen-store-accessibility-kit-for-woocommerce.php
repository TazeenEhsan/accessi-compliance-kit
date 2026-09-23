<?php
/**
 * Plugin Name:       Tazeen Store Accessibility Kit for WooCommerce
 * Description:       Scan WooCommerce stores for WCAG 2.1 AA violations, auto-fix common ones, and generate EAA compliance statements.
 * Version:           1.0.4
 * Requires at least: 6.5
 * Requires PHP:      7.4
 * Author:      Tazeen Ehsan
 * Author URI:  https://tazeenehsan.github.io/
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       tazeen-store-accessibility-kit-for-woocommerce
 * Domain Path:       /languages
 *
 * @package AccessibilityComplianceKitForWooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'TSAKW_VERSION', '1.0.4' );
define( 'TSAKW_FILE', __FILE__ );
define( 'TSAKW_PATH', plugin_dir_path( __FILE__ ) );
define( 'TSAKW_URL', plugin_dir_url( __FILE__ ) );

require TSAKW_PATH . 'vendor/autoload.php';

register_activation_hook( TSAKW_FILE, array( \AccessibilityComplianceKitForWooCommerce\Activator::class, 'activate' ) );
register_deactivation_hook(
	TSAKW_FILE,
	array( \AccessibilityComplianceKitForWooCommerce\Deactivator::class, 'deactivate' )
);

\AccessibilityComplianceKitForWooCommerce\Plugin::instance();
