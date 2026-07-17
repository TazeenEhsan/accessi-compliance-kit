<?php
/**
 * Fired when the plugin is deleted. Drops the scans table and all `accessi_compliance_kit_*` options.
 *
 * @package AccessiComplianceKit
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

$wpdb->query( $wpdb->prepare( 'DROP TABLE IF EXISTS %i', $wpdb->prefix . 'accessi_compliance_kit_scans' ) );

delete_option( 'accessi_compliance_kit_settings' );
delete_option( 'accessi_compliance_kit_active_fixes' );
delete_option( 'accessi_compliance_kit_license' );
delete_option( 'accessi_compliance_kit_last_scan_id' );
delete_option( 'accessi_compliance_kit_db_version' );
