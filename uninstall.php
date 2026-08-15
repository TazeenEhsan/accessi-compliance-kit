<?php
/**
 * Fires on plugin deletion via the Plugins screen. Removes everything the
 * plugin stored: the scans table and all `accessi_compliance_kit_*` options
 * (docs/database.md §5). Deactivation never deletes data; this is the only
 * place that does.
 *
 * @package AccessiComplianceKit
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

$accessi_compliance_kit_table_name = $wpdb->prefix . 'accessi_compliance_kit_scans';

// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.NoCaching -- uninstall routine; dbDelta()-created table, no user input.
$wpdb->query( $wpdb->prepare( 'DROP TABLE IF EXISTS %i', $accessi_compliance_kit_table_name ) );

delete_option( 'accessi_compliance_kit_settings' );
delete_option( 'accessi_compliance_kit_active_fixes' );
delete_option( 'accessi_compliance_kit_license' );
delete_option( 'accessi_compliance_kit_last_scan_id' );
delete_option( 'accessi_compliance_kit_db_version' );
