<?php
/**
 * Weekly opt-in email reminder to run a scan.
 *
 * @package AccessiComplianceKit
 */

namespace AccessiComplianceKit\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AccessiComplianceKit\Utils\Options;

/**
 * Schedules/unschedules the `accessi_compliance_kit_weekly_reminder` WP-Cron
 * event to match the email opt-in setting, and sends the reminder (proposal
 * §4.1 "Notifications"; docs/admin.md §9).
 */
class EmailReminder {

	const CRON_HOOK = 'accessi_compliance_kit_weekly_reminder';
	const SCHEDULE  = 'accessi_compliance_kit_weekly';

	/**
	 * Hook the cron sender, the custom schedule, and settings-driven (re)scheduling.
	 *
	 * @return void
	 */
	public function register() {
		// phpcs:ignore WordPress.WP.CronInterval.CronSchedulesInterval -- fixed weekly interval is the documented behavior (proposal §4.1).
		add_filter( 'cron_schedules', array( $this, 'add_weekly_schedule' ) );
		add_action( self::CRON_HOOK, array( $this, 'send_reminder' ) );
		add_action( 'add_option_' . Options::SETTINGS, array( $this, 'handle_option_added' ), 10, 2 );
		add_action( 'update_option_' . Options::SETTINGS, array( $this, 'handle_option_updated' ), 10, 2 );
	}

	/**
	 * Register a weekly interval for `wp_schedule_event()`; WP core only ships
	 * hourly/twicedaily/daily.
	 *
	 * @param array $schedules Existing cron schedules.
	 * @return array
	 */
	public function add_weekly_schedule( $schedules ) {
		if ( ! isset( $schedules[ self::SCHEDULE ] ) ) {
			$schedules[ self::SCHEDULE ] = array(
				'interval' => WEEK_IN_SECONDS,
				'display'  => __( 'Once Weekly (Accessi Compliance Kit)', 'accessi-compliance-kit' ),
			);
		}

		return $schedules;
	}

	/**
	 * Sync the cron schedule the first time the settings option is created.
	 *
	 * @param string $option Option name (always `Options::SETTINGS` here).
	 * @param mixed  $value  New option value.
	 * @return void
	 */
	public function handle_option_added( $option, $value ) {
		$this->sync_schedule( $value );
	}

	/**
	 * Sync the cron schedule whenever the settings option is updated.
	 *
	 * @param mixed $old_value Previous option value.
	 * @param mixed $value     New option value.
	 * @return void
	 */
	public function handle_option_updated( $old_value, $value ) {
		$this->sync_schedule( $value );
	}

	/**
	 * Schedule or unschedule the weekly cron event to match the opt-in state.
	 *
	 * @param mixed $settings Plugin settings array.
	 * @return void
	 */
	private function sync_schedule( $settings ) {
		$enabled   = is_array( $settings ) && ! empty( $settings['email_reminder_opt_in'] );
		$scheduled = wp_next_scheduled( self::CRON_HOOK );

		if ( $enabled && ! $scheduled ) {
			wp_schedule_event( time(), self::SCHEDULE, self::CRON_HOOK );

			return;
		}

		if ( ! $enabled && $scheduled ) {
			wp_unschedule_event( $scheduled, self::CRON_HOOK );
		}
	}

	/**
	 * Send the weekly reminder email to the site admin, linking to the scan page.
	 *
	 * @return void
	 */
	public function send_reminder() {
		$settings = Options::get_settings();

		if ( empty( $settings['email_reminder_opt_in'] ) ) {
			return;
		}

		$scan_page_url = add_query_arg( 'page', AdminMenu::MENU_SLUG, admin_url( 'admin.php' ) );

		wp_mail(
			get_option( 'admin_email' ),
			__( 'Accessi Compliance Kit — weekly scan reminder', 'accessi-compliance-kit' ),
			sprintf(
				/* translators: %s: URL to the plugin's scan page. */
				__( "It's been a week since your last accessibility scan. Run a new scan to check for issues:\n\n%s", 'accessi-compliance-kit' ), // phpcs:ignore Generic.Files.LineLength.TooLong -- single translatable string, cannot be wrapped without breaking translation context.
				esc_url_raw( $scan_page_url )
			)
		);
	}

	/**
	 * Unschedule the weekly cron event. Called on plugin deactivation
	 * (docs/database.md §5: deactivation unschedules cron, never deletes data).
	 *
	 * @return void
	 */
	public static function unschedule() {
		$scheduled = wp_next_scheduled( self::CRON_HOOK );

		if ( $scheduled ) {
			wp_unschedule_event( $scheduled, self::CRON_HOOK );
		}
	}
}
