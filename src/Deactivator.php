<?php
/**
 * Runs on plugin deactivation.
 *
 * @package AccessibilityComplianceKitForWooCommerce
 */

namespace AccessibilityComplianceKitForWooCommerce;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AccessibilityComplianceKitForWooCommerce\Admin\EmailReminder;

/**
 * Unschedules cron events. Never deletes data — see `uninstall.php` for cleanup.
 */
class Deactivator {

	/**
	 * Deactivation entry point.
	 *
	 * @return void
	 */
	public static function deactivate() {
		EmailReminder::unschedule();
	}
}
