<?php
/**
 * Typed access to the plugin's `wp_options` keys.
 *
 * @package AccessiComplianceKit
 */

namespace AccessiComplianceKit\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wraps `get_option()`/`update_option()` for the four option keys in proposal §5.3.
 * No other code should call `get_option()`/`update_option()` for these keys directly.
 */
class Options {

	const SETTINGS     = 'accessi_compliance_kit_settings';
	const ACTIVE_FIXES = 'accessi_compliance_kit_active_fixes';
	const LICENSE      = 'accessi_compliance_kit_license';
	const LAST_SCAN_ID = 'accessi_compliance_kit_last_scan_id';

	/**
	 * Get the plugin settings array.
	 *
	 * @return array
	 */
	public static function get_settings() {
		return get_option( self::SETTINGS, array() );
	}

	/**
	 * Update the plugin settings array.
	 *
	 * @param array $settings Settings to store.
	 * @return bool
	 */
	public static function update_settings( array $settings ) {
		return update_option( self::SETTINGS, $settings );
	}

	/**
	 * Get the fix ID => enabled map.
	 *
	 * @return array
	 */
	public static function get_active_fixes() {
		return get_option( self::ACTIVE_FIXES, array() );
	}

	/**
	 * Update the fix ID => enabled map.
	 *
	 * @param array $active_fixes Map of fix ID to boolean.
	 * @return bool
	 */
	public static function update_active_fixes( array $active_fixes ) {
		return update_option( self::ACTIVE_FIXES, $active_fixes );
	}

	/**
	 * Get the Pro license data.
	 *
	 * @return array
	 */
	public static function get_license() {
		return get_option( self::LICENSE, array() );
	}

	/**
	 * Update the Pro license data.
	 *
	 * @param array $license License data.
	 * @return bool
	 */
	public static function update_license( array $license ) {
		return update_option( self::LICENSE, $license );
	}

	/**
	 * Get the last scan ID, used by the dashboard widget.
	 *
	 * @return int
	 */
	public static function get_last_scan_id() {
		return absint( get_option( self::LAST_SCAN_ID, 0 ) );
	}

	/**
	 * Update the last scan ID.
	 *
	 * @param int $scan_id Scan row ID.
	 * @return bool
	 */
	public static function update_last_scan_id( $scan_id ) {
		return update_option( self::LAST_SCAN_ID, absint( $scan_id ) );
	}
}
