<?php
/**
 * Runs on plugin deactivation.
 *
 * @package AccessiWoo
 */

namespace AccessiWoo;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Unschedules cron events. Never deletes data — see `uninstall.php` for cleanup.
 * Cron unscheduling is implemented alongside the weekly reminder in Phase 4.
 */
class Deactivator {

	/**
	 * Deactivation entry point.
	 *
	 * @return void
	 */
	public static function deactivate() {
	}
}
