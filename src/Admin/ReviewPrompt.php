<?php
/**
 * One dismissible "leave a review" prompt on the plugin's own admin page.
 *
 * @package AccessibilityComplianceKitForWooCommerce
 */

namespace AccessibilityComplianceKitForWooCommerce\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AccessibilityComplianceKitForWooCommerce\Scanner\ScanStorage;
use AccessibilityComplianceKitForWooCommerce\Utils\Capabilities;
use AccessibilityComplianceKitForWooCommerce\Utils\Options;

/**
 * Decides whether to show the review prompt (after three completed scans, once
 * per user unless snoozed) and stores the user's answer. The prompt is rendered
 * by the React app on the plugin page only; this class never outputs markup and
 * makes no external requests (the review link is a plain link the user clicks).
 */
class ReviewPrompt {

	const MIN_COMPLETED_SCANS = 3;

	const SNOOZE_SECONDS = 30 * DAY_IN_SECONDS;

	const STATE_DONE = 'done';

	const CHOICE_LATER = 'later';

	const CHOICE_DONE = 'done';

	const REVIEW_URL = 'https://wordpress.org/support/plugin/tazeen-store-accessibility-kit-for-woocommerce/reviews/#new-post';

	/**
	 * Scan storage service.
	 *
	 * @var ScanStorage
	 */
	private $scan_storage;

	/**
	 * Constructor.
	 *
	 * @param ScanStorage $scan_storage Scan storage service.
	 */
	public function __construct( ScanStorage $scan_storage ) {
		$this->scan_storage = $scan_storage;
	}

	/**
	 * Hook the answer-saving AJAX handler.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'wp_ajax_accessibility_compliance_kit_for_woocommerce_review_prompt', array( $this, 'handle_answer' ) );
	}

	/**
	 * Whether the prompt should be shown to a user right now.
	 *
	 * @param int $user_id User ID.
	 * @return bool
	 */
	public function should_show( $user_id ) {
		$state = Options::get_review_prompt_state( $user_id );

		if ( self::STATE_DONE === $state ) {
			return false;
		}

		if ( '' !== $state && (int) $state > time() ) {
			return false;
		}

		return $this->scan_storage->count_completed_scans() >= self::MIN_COMPLETED_SCANS;
	}

	/**
	 * Data localized to the admin app for the current user.
	 *
	 * @return array
	 */
	public function localized_data() {
		return array(
			'show'      => $this->should_show( get_current_user_id() ),
			'reviewUrl' => self::REVIEW_URL,
		);
	}

	/**
	 * Save the user's answer: snooze for 30 days, or never ask again.
	 *
	 * @return void
	 */
	public function handle_answer() {
		check_ajax_referer( 'accessibility_compliance_kit_for_woocommerce_review_prompt', 'nonce' );

		if ( ! Capabilities::can_scan() ) {
			wp_send_json_error(
				array( 'message' => __( 'You are not allowed to do that.', 'tazeen-store-accessibility-kit-for-woocommerce' ) ),
				403
			);
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- verified above via check_ajax_referer.
		$choice = isset( $_POST['choice'] ) ? sanitize_key( wp_unslash( $_POST['choice'] ) ) : '';

		if ( self::CHOICE_LATER === $choice ) {
			Options::update_review_prompt_state( get_current_user_id(), time() + self::SNOOZE_SECONDS );
		} elseif ( self::CHOICE_DONE === $choice ) {
			Options::update_review_prompt_state( get_current_user_id(), self::STATE_DONE );
		} else {
			wp_send_json_error( array( 'message' => __( 'Unknown choice.', 'tazeen-store-accessibility-kit-for-woocommerce' ) ), 400 );
		}

		wp_send_json_success( array( 'choice' => $choice ) );
	}
}
