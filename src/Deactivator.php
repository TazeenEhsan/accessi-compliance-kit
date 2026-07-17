<?php
/**
 * Runs on plugin deactivation.
 *
 * @package AccessiComplianceKit
 */

namespace AccessiComplianceKit;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AccessiComplianceKit\Admin\EmailReminder;

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
