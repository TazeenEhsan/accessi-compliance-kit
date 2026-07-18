<?php
/**
 * Registers and saves the plugin's fix toggles and email opt-in.
 *
 * @package AccessiComplianceKit
 */

namespace AccessiComplianceKit\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AccessiComplianceKit\Fixes\FixManager;
use AccessiComplianceKit\Utils\Capabilities;
use AccessiComplianceKit\Utils\Options;

/**
 * Registers the Settings API sanitization for `accessi_compliance_kit_settings`
 * and `accessi_compliance_kit_active_fixes` (docs/admin.md §6), and handles the
 * `accessi_compliance_kit_save_settings` AJAX action used by the Settings tab.
 */
class SettingsPage {

	const OPTION_GROUP = 'accessi_compliance_kit';

	/**
	 * Hook settings registration and the save AJAX handler.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'wp_ajax_accessi_compliance_kit_save_settings', array( $this, 'handle_save_settings' ) );
	}

	/**
	 * Register the two option keys with the Settings API.
	 *
	 * @return void
	 */
	public function register_settings() {
		register_setting(
			self::OPTION_GROUP,
			Options::SETTINGS,
			array( 'sanitize_callback' => array( $this, 'sanitize_settings' ) )
		);

		register_setting(
			self::OPTION_GROUP,
			Options::ACTIVE_FIXES,
			array( 'sanitize_callback' => array( $this, 'sanitize_active_fixes' ) )
		);
	}

	/**
	 * Save the fix toggles and email opt-in posted from the Settings tab.
	 *
	 * @return void
	 */
	public function handle_save_settings() {
		check_ajax_referer( 'accessi_compliance_kit_save_settings', 'nonce' );

		if ( ! Capabilities::can_manage_settings() ) {
			wp_send_json_error(
				array( 'message' => __( 'You are not allowed to change these settings.', 'accessi-compliance-kit' ) ),
				403
			);
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- nonce verified above via check_ajax_referer(); value is whitelist-sanitized below in sanitize_active_fixes().
		$raw_fixes_json = isset( $_POST['active_fixes'] ) ? wp_unslash( $_POST['active_fixes'] ) : '';
		// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- nonce verified above via check_ajax_referer(); value is boolean-cast below in sanitize_settings().
		$email_opt_in = isset( $_POST['email_reminder_opt_in'] ) ? wp_unslash( $_POST['email_reminder_opt_in'] ) : '';

		$decoded   = json_decode( $raw_fixes_json, true );
		$raw_fixes = ( JSON_ERROR_NONE === json_last_error() && is_array( $decoded ) ) ? $decoded : array();

		$active_fixes = $this->sanitize_active_fixes( $raw_fixes );
		$settings     = $this->sanitize_settings(
			array_merge(
				Options::get_settings(),
				array( 'email_reminder_opt_in' => $email_opt_in )
			)
		);

		Options::update_active_fixes( $active_fixes );
		Options::update_settings( $settings );

		wp_send_json_success(
			array(
				'active_fixes' => $active_fixes,
				'settings'     => $settings,
			)
		);
	}

	/**
	 * Whitelist known fix IDs and cast every value to a strict boolean
	 * (docs/security.md §2: "whitelist known fix IDs; booleans cast strictly").
	 *
	 * @param mixed $value Raw fix ID => enabled map.
	 * @return array
	 */
	public function sanitize_active_fixes( $value ) {
		$value     = is_array( $value ) ? $value : array();
		$sanitized = array();

		foreach ( FixManager::fix_ids() as $fix_id ) {
			$sanitized[ $fix_id ] = isset( $value[ $fix_id ] ) && $this->to_bool( $value[ $fix_id ] );
		}

		return $sanitized;
	}

	/**
	 * Sanitize the plugin settings array, keeping only known keys.
	 *
	 * @param mixed $value Raw settings array.
	 * @return array
	 */
	public function sanitize_settings( $value ) {
		$value = is_array( $value ) ? $value : array();

		return array(
			'email_reminder_opt_in' => isset( $value['email_reminder_opt_in'] )
				&& $this->to_bool( $value['email_reminder_opt_in'] ),
			'statement_page_id'     => isset( $value['statement_page_id'] ) ? absint( $value['statement_page_id'] ) : 0,
		);
	}

	/**
	 * Strictly interpret a posted value as a boolean; anything not an
	 * explicit truthy form sanitizes to false.
	 *
	 * @param mixed $value Raw value.
	 * @return bool
	 */
	private function to_bool( $value ) {
		return true === $value || 1 === $value || '1' === $value || 'true' === $value;
	}
}
