<?php
/**
 * Centralized capability checks.
 *
 * @package AccessibilityComplianceKitForWooCommerce
 */

namespace AccessibilityComplianceKitForWooCommerce\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * All permission checks go through this class rather than ad-hoc `current_user_can()` calls.
 */
class Capabilities {

	const SCAN            = 'manage_options';
	const MANAGE_SETTINGS = 'manage_options';

	/**
	 * Whether the current user may run a scan.
	 *
	 * @return bool
	 */
	public static function can_scan() {
		return current_user_can( self::SCAN );
	}

	/**
	 * Whether the current user may manage plugin settings.
	 *
	 * @return bool
	 */
	public static function can_manage_settings() {
		return current_user_can( self::MANAGE_SETTINGS );
	}
}
