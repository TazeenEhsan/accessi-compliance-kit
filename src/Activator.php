<?php
/**
 * Runs on plugin activation.
 *
 * @package AccessiComplianceKit
 */

namespace AccessiComplianceKit;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AccessiComplianceKit\Utils\Options;

/**
 * Creates the scans table and seeds default options.
 */
class Activator {

	/**
	 * Current database schema version.
	 *
	 * @var string
	 */
	const DB_VERSION = '1.0.0';

	/**
	 * Activation entry point.
	 *
	 * @return void
	 */
	public static function activate() {
		self::create_scans_table();
		self::seed_default_options();
	}

	/**
	 * Create the `{$wpdb->prefix}accessi_compliance_kit_scans` table via dbDelta().
	 *
	 * @return void
	 */
	private static function create_scans_table() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$table_name      = $wpdb->prefix . 'accessi_compliance_kit_scans';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table_name} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			scan_type VARCHAR(20) NOT NULL,
			url TEXT NOT NULL,
			started_at DATETIME NOT NULL,
			completed_at DATETIME NULL,
			status VARCHAR(20) NOT NULL,
			violations_json LONGTEXT NULL,
			summary_json TEXT NULL,
			triggered_by BIGINT UNSIGNED NOT NULL,
			PRIMARY KEY  (id),
			KEY status (status),
			KEY started_at (started_at)
		) {$charset_collate};";

		dbDelta( $sql );

		Options::update_db_version( self::DB_VERSION );
	}

	/**
	 * Seed default options if they don't already exist. All fixes ship OFF (AI_RULES §2.3).
	 *
	 * @return void
	 */
	private static function seed_default_options() {
		if ( ! Options::option_exists( Options::SETTINGS ) ) {
			Options::update_settings(
				array(
					'email_reminder_opt_in' => false,
					'statement_page_id'     => 0,
				)
			);
		}

		if ( ! Options::option_exists( Options::ACTIVE_FIXES ) ) {
			Options::update_active_fixes(
				array(
					'product_image_alt'   => false,
					'checkout_labels'     => false,
					'focus_states'        => false,
					'icon_button_aria'    => false,
					'price_screen_reader' => false,
					'empty_link_anchor'   => false,
				)
			);
		}
	}
}
