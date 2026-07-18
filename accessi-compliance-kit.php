<?php
/**
 * Plugin Name:       Accessi Compliance Kit
 * Plugin URI:        https://accessiwoo.com
 * Description:       Scan WooCommerce stores for WCAG 2.1 AA violations, auto-fix common ones, and generate EAA compliance statements.
 * Version:           0.1.0
 * Requires at least: 6.5
 * Requires PHP:      7.4
 * Author:            AccessiWoo
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       accessi-compliance-kit
 * Domain Path:       /languages
 *
 * @package AccessiComplianceKit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ACCESSI_COMPLIANCE_KIT_VERSION', '0.1.0' );
define( 'ACCESSI_COMPLIANCE_KIT_FILE', __FILE__ );
define( 'ACCESSI_COMPLIANCE_KIT_PATH', plugin_dir_path( __FILE__ ) );
define( 'ACCESSI_COMPLIANCE_KIT_URL', plugin_dir_url( __FILE__ ) );

require ACCESSI_COMPLIANCE_KIT_PATH . 'vendor/autoload.php';

register_activation_hook( ACCESSI_COMPLIANCE_KIT_FILE, array( \AccessiComplianceKit\Activator::class, 'activate' ) );
register_deactivation_hook(
	ACCESSI_COMPLIANCE_KIT_FILE,
	array( \AccessiComplianceKit\Deactivator::class, 'deactivate' )
);

\AccessiComplianceKit\Plugin::instance();
