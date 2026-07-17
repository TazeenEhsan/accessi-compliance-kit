<?php
/**
 * Fired when the plugin is deleted. Drops the scans table and all `accessiwoo_*` options.
 *
 * @package AccessiWoo
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

$wpdb->query( $wpdb->prepare( 'DROP TABLE IF EXISTS %i', $wpdb->prefix . 'accessiwoo_scans' ) );

delete_option( 'accessiwoo_settings' );
delete_option( 'accessiwoo_active_fixes' );
delete_option( 'accessiwoo_license' );
delete_option( 'accessiwoo_last_scan_id' );
delete_option( 'accessiwoo_db_version' );
